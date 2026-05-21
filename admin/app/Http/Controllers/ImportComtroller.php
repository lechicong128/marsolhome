<?php

namespace App\Http\Controllers;

use App\Models\Language;
use App\Models\Products;
use App\Services\TransactionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\DataTables;

class ImportComtroller extends Controller
{
    public $currentLanguage;
    protected $transactionService;
    
    public function __construct(Request $request, TransactionService $transactionService)
    {
        parent::__construct($request);
        $this->currentLanguage = app()->getLocale();
        $this->transactionService = $transactionService;
    }

    public function get_list()
    {
        if (!has_permission('warehouse_import', 'view')) {
            access_denied();
        }
        return view('admin.imports.list', [
            'title' => 'Danh sách nhập kho',
        ]);
    }

    public function detail($id = '')
    {
        if (!has_permission('warehouse_import', 'view')) {
            access_denied();
        }

        $import = null;
        if (!empty($id)) {
            $import = DB::table('tbl_warehouse_imports')->where('id', $id)->first();
            if ($import) {
                $import->details = DB::table('tbl_warehouse_import_details')
                    ->join('tbl_products', 'tbl_warehouse_import_details.id_product', '=', 'tbl_products.id')
                    ->select('tbl_warehouse_import_details.*', 'tbl_products.name as product_name', 'tbl_products.code as product_code')
                    ->where('id_import', $id)
                    ->get();
            }
        }

        $products = Products::where('active', 1)->get();

        return view('admin.imports.detail', [
            'id' => $id,
            'title' => empty($id) ? 'Thêm phiếu nhập kho' : 'Chi tiết phiếu nhập kho',
            'import' => $import,
            'products' => $products,
        ]);
    }

    public function getTable()
    {
        $imports = DB::table('tbl_warehouse_imports')
            ->select('tbl_warehouse_imports.*')
            ->orderBy('id', 'desc');

        return Datatables::of($imports)
            ->addColumn('items', function ($item) {
                $details = DB::table('tbl_warehouse_import_details')
                    ->leftJoin('tbl_products', 'tbl_warehouse_import_details.id_product', '=', 'tbl_products.id')
                    ->where('id_import', $item->id)
                    ->select('tbl_products.name', 'tbl_warehouse_import_details.product_name', 'tbl_warehouse_import_details.quantity')
                    ->get();
                
                $count = count($details);
                if ($count == 0) return '';

                $html = '<table class="table-bordered" style="width: 100%; margin-bottom: 0; min-width: 200px; font-size: 13px; background-color: #fff; border: 1px solid #eaebf0;">';
                foreach ($details as $index => $d) {
                    $name = $d->name ?? $d->product_name;
                    $qty = (float)$d->quantity;
                    
                    $display = ($index >= 3) ? 'display: none;' : '';
                    $class = ($index >= 3) ? 'hidden-row-' . $item->id : '';
                    
                    $html .= '<tr class="'.$class.'" style="'.$display.'">';
                    $html .= '<td style="padding: 5px 10px; text-align: left; vertical-align: middle; color: #333; border: 1px solid #eaebf0; background-color: #fafafa;">' . htmlspecialchars(trim($name)) . '</td>';
                    $html .= '<td style="padding: 5px 10px; text-align: right; width: 60px; vertical-align: middle; color: #1e88e5; font-weight: bold; border: 1px solid #eaebf0;">' . formatMoney($qty) . '</td>';
                    $html .= '</tr>';
                }
                
                if ($count > 3) {
                    $html .= '<tr><td colspan="2" style="padding: 4px; text-align: center; border: 1px solid #eaebf0;">';
                    $html .= '<a href="javascript:void(0);" onclick="toggleItems(this, '.$item->id.')" style="font-size: 12px; color: #007bff; text-decoration: none;">Xem thêm ('.($count - 3).') <i class="fa fa-angle-down"></i></a>';
                    $html .= '</td></tr>';
                }
                
                $html .= '</table>';
                
                return $html;
            })
            ->addColumn('options', function ($item) {
                $btnName = ($item->status == 1) ? 'Xem' : 'Sửa/Xem';
                $edit = "<a href='admin/imports/detail/$item->id' class='btn btn-xs btn-primary'><i class='fa fa-pencil'></i> $btnName</a>";
                $delete = " <button onclick='deleteImport($item->id)' class='btn btn-xs btn-danger'><i class='fa fa-trash'></i> Xóa</button>";
                return $edit . $delete;
            })
            ->editColumn('status', function ($item) {
                if ($item->status == 0) {
                    return '<button onclick="approveImport('.$item->id.')" class="btn btn-xs btn-warning" style="width: 100px; display: block; margin: 0 auto;" title="Bấm để Duyệt"><i class="fa fa-clock-o"></i> Chờ duyệt</button>';
                }
                if ($item->status == 1) {
                    return '<button onclick="unapproveImport('.$item->id.')" class="btn btn-xs btn-success" style="width: 100px; display: block; margin: 0 auto;" title="Bấm để Bỏ duyệt"><i class="fa fa-check"></i> Đã duyệt</button>';
                }
                return '<span class="label label-danger">Đã hủy</span>';
            })
            ->editColumn('import_date', function ($item) {
                return date('d/m/Y', strtotime($item->import_date));
            })
            ->addIndexColumn()
            ->rawColumns(['options', 'status', 'items'])
            ->make(true);
    }

    public function submit(Request $request, $id = 0)
    {
        $validator = Validator::make($request->all(), [
            'import_date' => 'required|date',
            'supplier_name' => 'required',
            'products' => 'required|array|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['result' => false, 'message' => $validator->errors()->all()]);
        }

        DB::beginTransaction();
        try {
            $dataHeader = [
                'import_date' => $request->input('import_date'),
                'supplier_name' => $request->input('supplier_name'),
                'note' => $request->input('note'),
                'updated_at' => now(),
            ];

            if (empty($id)) {
                $dataHeader['import_code'] = 'NK-' . date('Ymd') . '-' . strtoupper(uniqid());
                $dataHeader['status'] = 0;
                $dataHeader['created_by'] = auth()->id();
                $dataHeader['created_at'] = now();
                $id = DB::table('tbl_warehouse_imports')->insertGetId($dataHeader);
            } else {
                DB::table('tbl_warehouse_imports')->where('id', $id)->update($dataHeader);
                // Clear old details to re-insert
                DB::table('tbl_warehouse_import_details')->where('id_import', $id)->delete();
            }

            $products = DB::table('tbl_products')
                ->whereIn('id', array_keys($request->input('products')))
                ->pluck('name', 'id');
            $productCodes = DB::table('tbl_products')
                ->whereIn('id', array_keys($request->input('products')))
                ->pluck('code', 'id');

            $details = [];
            foreach ($request->input('products') as $productId => $qty) {
                if ($qty > 0) {
                    $details[] = [
                        'id_import'    => $id,
                        'id_product'   => $productId,
                        'product_code' => $productCodes[$productId] ?? null,
                        'product_name' => $products[$productId] ?? null,
                        'quantity'     => $qty,
                        'remaining_qty'=> $qty,   // sẽ bị ghi đè về 0 cho đến khi duyệt
                        'created_at'   => now(),
                        'updated_at'   => now(),
                    ];
                }
            }
            DB::table('tbl_warehouse_import_details')->insert($details);

            DB::commit();
            return response()->json(['result' => true, 'message' => 'Lưu thành công']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['result' => false, 'message' => $e->getMessage()]);
        }
    }

    public function approve($id)
    {
        if (!has_permission('warehouse_import', 'approve')) {
             return response()->json(['result' => false, 'message' => 'Bạn không có quyền duyệt']);
        }

        $import = DB::table('tbl_warehouse_imports')->where('id', $id)->first();
        if (!$import || $import->status != 0) {
            return response()->json(['result' => false, 'message' => 'Phiếu không hợp lệ hoặc đã duyệt']);
        }

        DB::beginTransaction();
        try {
            $details = DB::table('tbl_warehouse_import_details')->where('id_import', $id)->get();
            foreach ($details as $detail) {
                // Set remaining_qty = quantity khi duyệt lần đầu
                DB::table('tbl_warehouse_import_details')->where('id', $detail->id)->update([
                    'remaining_qty' => $detail->quantity,
                    'updated_at'    => now(),
                ]);

                // Update tổng tồn kho
                $stock = DB::table('tbl_warehouse_stock')->where('id_product', $detail->id_product)->first();
                if ($stock) {
                    DB::table('tbl_warehouse_stock')->where('id_product', $detail->id_product)->update([
                        'quantity'    => $stock->quantity + $detail->quantity,
                        'last_import' => $import->import_date,
                        'updated_at'  => now(),
                    ]);
                } else {
                    DB::table('tbl_warehouse_stock')->insert([
                        'id_product'  => $detail->id_product,
                        'quantity'    => $detail->quantity,
                        'last_import' => $import->import_date,
                        'created_at'  => now(),
                        'updated_at'  => now(),
                    ]);
                }
            }

            DB::table('tbl_warehouse_imports')->where('id', $id)->update([
                'status' => 1,
                'approved_by' => auth()->id(),
                'updated_at' => now(),
            ]);

            DB::commit();
            return response()->json(['result' => true, 'message' => 'Duyệt thành công, kho đã được cập nhật']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['result' => false, 'message' => $e->getMessage()]);
        }
    }
    public function unapprove($id)
    {
        if (!has_permission('warehouse_import', 'approve')) {
             return response()->json(['result' => false, 'message' => 'Bạn không có quyền thực hiện']);
        }

        $import = DB::table('tbl_warehouse_imports')->where('id', $id)->first();
        if (!$import || $import->status != 1) {
            return response()->json(['result' => false, 'message' => 'Phiếu không ở trạng thái có thể bỏ duyệt']);
        }

        DB::beginTransaction();
        try {
            $details = DB::table('tbl_warehouse_import_details')->where('id_import', $id)->get();
            
            // KIỂM TRA TỒN KHO: Nếu đã xuất hàng từ lô này thì không cho bỏ duyệt
            foreach ($details as $detail) {
                if ($detail->remaining_qty < $detail->quantity) {
                    $consumedLots = DB::table('tbl_transaction_warehouse_details')
                        ->where('detail_id', $detail->id)
                        ->select('transaction_id', 'qty_take', 'created_at')
                        ->get();

                    $resolvedLogs = [];
                    foreach ($consumedLots as $log) {
                        $logArr = (array) $log;
                        // Gọi service để lấy mã đơn hàng (reference_no)
                        $this->request->merge(['id' => $log->transaction_id]);
                        $resDetail = $this->transactionService->getListDetailTransaction($this->request);
                        $detailData = $resDetail->getData(true);
                        
                        $logArr['order_code'] = $detailData['data']['data']['reference_no'] ?? ("ID: " . $log->transaction_id);
                        $resolvedLogs[] = $logArr;
                    }

                    return response()->json([
                        'result' => false, 
                        'type' => 'consumed_error',
                        'product_name' => $detail->product_name,
                        'consumed_by' => $resolvedLogs,
                        'message' => "Sản phẩm [{$detail->product_name}] đã được xuất kho bởi các đơn hàng sau. Không thể bỏ duyệt!"
                    ]);
                }
            }

            // Thực hiện hoàn tồn
            foreach ($details as $detail) {
                // Trừ tổng tồn kho
                $stock = DB::table('tbl_warehouse_stock')->where('id_product', $detail->id_product)->first();
                if ($stock) {
                    DB::table('tbl_warehouse_stock')->where('id_product', $detail->id_product)->update([
                        'quantity'   => max(0, $stock->quantity - $detail->quantity),
                        'updated_at' => now(),
                    ]);
                }

                // Reset remaining_qty về 0 (vì status sẽ về 0)
                DB::table('tbl_warehouse_import_details')->where('id', $detail->id)->update([
                    'remaining_qty' => 0,
                    'updated_at'    => now(),
                ]);
            }

            // Cập nhật trạng thái phiếu nhập
            DB::table('tbl_warehouse_imports')->where('id', $id)->update([
                'status' => 0,
                'approved_by' => null,
                'updated_at' => now(),
            ]);

            DB::commit();
            return response()->json(['result' => true, 'message' => 'Đã bỏ duyệt và trừ tồn kho thành công']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['result' => false, 'message' => $e->getMessage()]);
        }
    }

    public function delete($id)
    {
        if (!has_permission('warehouse_import', 'delete')) {
             return response()->json(['result' => false, 'message' => 'Bạn không có quyền xóa']);
        }

        $import = DB::table('tbl_warehouse_imports')->where('id', $id)->first();
        if (!$import) {
            return response()->json(['result' => false, 'message' => 'Không tìm thấy phiếu nhập']);
        }

        if ($import->status == 1) {
            return response()->json(['result' => false, 'message' => 'Phiếu đã duyệt không thể xóa. Vui lòng bỏ duyệt trước!']);
        }

        DB::beginTransaction();
        try {
            DB::table('tbl_warehouse_import_details')->where('id_import', $id)->delete();
            DB::table('tbl_warehouse_imports')->where('id', $id)->delete();

            DB::commit();
            return response()->json(['result' => true, 'message' => 'Xóa phiếu nhập thành công']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['result' => false, 'message' => $e->getMessage()]);
        }
    }
}
