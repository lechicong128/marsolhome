<?php

namespace App\Traits;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use App\Models\Invoice as InvoiceModel;
use App\Models\InvoiceItem as InvoiceItemModel;
use App\Models\Clients;
use App\Libraries\Invoice;

trait InvoiceTrait
{
    public function createInvoice(array $data)
    {
        $this->invoice = new Invoice();
        $invoice_id = $data['invoice_id'] ?? 0;
        $invoice = InvoiceModel::find($invoice_id);
        if(empty($invoice)) {
            $data['result'] = false;
            $data['message'] = lang('Không tồn tại hóa đơn!');
            return $data;
        }

        $dataView = [
            "ma_hoadon" => $invoice->reference_no,
        ];
        $dtCustomer = Clients::find($invoice->customer_id);
        if(empty($dtCustomer)) {
            $data['result'] = false;
            $data['message'] = lang('Không tồn tại khách hàng!');
            return $data;
        }
        $ma_hoadon = $invoice->reference_no;    
        $dnmua_mst = $dtCustomer->client_information_vat->vat ?? '';
        $dnmua_ten = $dtCustomer->client_information_vat->company ?? '';
        $dnmua_dia_chi = $dtCustomer->client_information_vat->address ?? '';
        $dnmua_tennguoimua = $dtCustomer->client_information_vat->name ?? '';
        $dnmua_email = $dtCustomer->client_information_vat->email ?? '';
        $thanhtoan_phuongthuc = 3;
        $thanhtoan_phuongthuc_ten = $dtCustomer->client_information_vat->payment_mode ?? '';
        $thanhtoan_taikhoan = $dtCustomer->client_information_vat->account_no ?? '';
        $thanhtoan_nganhang = $dtCustomer->client_information_vat->account_name ?? '';
        $ngaylap = date('Y-m-d H:i:s', strtotime($invoice->created_at));
        $tongtien_chietkhau = $invoice->total_discount;
        $tongtien_chuavat = $invoice->total_net;
        $tienthue = $invoice->total_tax;
        $tongtien_covat = $invoice->grand_total;
        $query = InvoiceItemModel::where('invoice_id', $invoice_id);
        $dataInvoiceItem = $query->get();
        if(empty($dataInvoiceItem)) {
            $data['result'] = false;
            $data['message'] = lang('Không tồn tại sản phẩm hóa đơn!');
            return $data;
        }
        $dschitiet = [];
        foreach($dataInvoiceItem as $key => $item) {
            $dschitiet[] = [
                "stt"=> $key + 1,
                "hanghoa_loai"=> 0,
                "ma"=> $item->code_item,
                "ten"=> $item->name_item,
                "donvitinh" => $item->unit_name,
                "soluong"=> $item->quantity,
                "dongia"=> $item->price,
                'tongtien_chietkhau' => $item->total_discount_item,
                "tongtien_chuathue"=> $item->total_net_item,
                "mathue"=> $item->vat,
                "tyletinhthue"=> 1,
                "tongtien_cothue"=> $item->grand_total_item
            ];
        }
        $dsthuesuat[] = [
          "mathue" => $invoice->tax_rate,
          "tongtien_chiuthue" => $invoice->total_net,
          "tongtien_thue" => $invoice->total_tax
        ];
        $dataInvoice = [
            "ma_hoadon" => $ma_hoadon,
            "dnmua_mst" => $dnmua_mst,
            "dnmua_ten" => $dnmua_ten,
            "dnmua_dia_chi" => $dnmua_dia_chi,
            "dnmua_tennguoimua" => $dnmua_tennguoimua,
            "dnmua_email" => $dnmua_email,
            "thanhtoan_phuongthuc" => $thanhtoan_phuongthuc,
            "thanhtoan_phuongthuc_ten" => $thanhtoan_phuongthuc_ten,
            "thanhtoan_taikhoan" => $thanhtoan_taikhoan,
            "thanhtoan_nganhang" => $thanhtoan_nganhang,
            "ngaylap" => $ngaylap,
            "tongtien_chietkhau" => 0,
            "tongtien_chuavat" => $tongtien_chuavat,
            "tienthue" => $tienthue,
            "tongtien_covat" => $tongtien_covat,
            "dschitiet" => $dschitiet,
            "dsthuesuat" => $dsthuesuat
        ];

        $result = $this->invoice->guiHoadonGoc($dataInvoice);
        if($result->result && $result->result->maketqua == '01') {
            DB::table('tbl_invoice')->where('id', $invoice_id)
            ->update([
                'response_import' => json_encode($result),
                'status_invoice'=> 1,
                'date_status_invoice' => date('Y-m-d H:i:s'),
                'magiaodich' => $result->result->magiaodich ?? null,
            ]);
            $data['result'] = true;
            $data['message'] = lang('Tạo hóa đơn thành công!');
            return $data;
        } else {
            DB::table('tbl_log_invoice')
            ->insertGetId([
                'invoice_id' => $invoice_id,
                'date' => date('Y-m-d H:i:s'),
                'response' => json_encode($result),
            ]);
            $message = $result->error->message
                ?? $result->result->motaketqua
                ?? 'Lỗi không xác định';
            $data['result'] = false;
            $data['message'] = lang('Tạo hóa đơn thất bại! Lỗi: '.$message);
            return $data;
        }
    }

}
