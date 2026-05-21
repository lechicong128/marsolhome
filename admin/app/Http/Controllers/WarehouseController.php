<?php

namespace App\Http\Controllers;

use App\Models\Products;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;

class WarehouseController extends Controller
{
    public $currentLanguage;
    public function __construct(Request $request)
    {
        parent::__construct($request);
        $this->currentLanguage = app()->getLocale();
    }

    public function get_list()
    {
        if (!has_permission('warehouse_stock', 'view')) {
            access_denied();
        }
        return view('admin.warehouse.list', [
            'title' => 'Quản lý tồn kho',
        ]);
    }

    public function getTable()
    {
        $stock = DB::table('tbl_warehouse_stock')
            ->join('tbl_products', 'tbl_warehouse_stock.id_product', '=', 'tbl_products.id')
            ->select(
                'tbl_warehouse_stock.*',
                'tbl_products.name as product_name',
                'tbl_products.code as product_code'
            )
            ->orderBy('tbl_warehouse_stock.quantity', 'desc');

        return Datatables::of($stock)
            ->addColumn('options', function ($item) {
                return "<a href='javascript:void(0)' onclick='showImportHistory({$item->id_product}, \"{$item->product_name}\")' class='btn btn-xs btn-primary'><i class='fa fa-list'></i> Chi tiết lô nhập</a>";
            })
            ->editColumn('quantity', function ($item) {
                $low = $item->quantity < 10 ? 'color:red;font-weight:700' : 'color:#28a745;font-weight:700';
                return "<span style='font-size:15px;{$low}'>{$item->quantity}</span>";
            })
            ->editColumn('last_import', function ($item) {
                return $item->last_import ? date('d/m/Y', strtotime($item->last_import)) : '-';
            })
            ->addIndexColumn()
            ->rawColumns(['options', 'quantity'])
            ->make(true);
    }

    /**
     * Trả về danh sách các lô nhập kho đã duyệt của 1 sản phẩm,
     * kèm số lượng còn lại (remaining_qty) của từng lô.
     */
    public function getImportHistory($id_product)
    {
        $rows = DB::table('tbl_warehouse_import_details as d')
            ->join('tbl_warehouse_imports as i', 'd.id_import', '=', 'i.id')
            ->where('d.id_product', $id_product)
            ->where('i.status', 1)               // chỉ lô đã duyệt
            ->select(
                'i.id as import_id',
                'i.import_code',
                'i.import_date',
                'i.supplier_name',
                'd.quantity as import_qty',
                DB::raw('COALESCE(d.remaining_qty, d.quantity) as remaining_qty')
            )
            ->orderBy('i.import_date', 'desc')
            ->get();

        return response()->json([
            'result' => true,
            'data'   => $rows,
        ]);
    }
}
