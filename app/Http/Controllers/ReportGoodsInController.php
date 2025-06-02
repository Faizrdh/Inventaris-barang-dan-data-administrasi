<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\GoodsIn;
use App\Models\Item;
use Carbon\Carbon;

class ReportGoodsInController extends Controller
{
    public function index(): View
    {
        // Cek role admin seperti pada EmployeeController
        if(Auth::user()->role->name != 'admin' && Auth::user()->role_id !== 1){
            abort(403, 'Akses tidak diizinkan untuk halaman ini.');
        }
        
        return view('admin.master.laporan.masuk');
    }

    public function list(Request $request): JsonResponse
    {
        try {
            // Cek role admin seperti pada EmployeeController  
            if(Auth::user()->role->name != 'admin' && Auth::user()->role_id !== 1){
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access'
                ], 403);
            }

            // Filter berdasarkan tanggal seperti code original
            if(empty($request->start_date) && empty($request->end_date)) {
                $goodsins = GoodsIn::with('item', 'user', 'supplier');
            } else {
                $goodsins = GoodsIn::with('item', 'user', 'supplier')
                    ->whereBetween('date_received', [$request->start_date, $request->end_date]);
            }

            $goodsins = $goodsins->latest()->get();

            if($request->ajax()){
                return DataTables::of($goodsins)
                    ->addColumn('quantity', function($data) {
                        try {
                            $item = Item::with("unit")->find($data->item->id);
                            if (!$item || !$item->unit) {
                                return $data->quantity."/tidak ditemukan";
                            }
                            return $data->quantity."/".$item->unit->name;
                        } catch (\Exception $e) {
                            return $data->quantity."/error";
                        }
                    })
                    ->addColumn("date_received", function($data) {
                        try {
                            return Carbon::parse($data->date_received)->format('d F Y');
                        } catch (\Exception $e) {
                            return 'Format tanggal tidak valid';
                        }
                    })
                    ->addColumn("kode_barang", function($data) {
                        try {
                            return $data->item ? $data->item->code : 'Kode tidak tersedia';
                        } catch (\Exception $e) {
                            return 'Error mendapatkan kode';
                        }
                    })
                    ->addColumn("supplier_name", function($data) {
                        try {
                            return $data->supplier ? $data->supplier->name : 'Supplier tidak tersedia';
                        } catch (\Exception $e) {
                            return 'Error mendapatkan supplier';
                        }
                    })
                    ->addColumn("item_name", function($data) {
                        try {
                            return $data->item ? $data->item->name : 'Item tidak tersedia';
                        } catch (\Exception $e) {
                            return 'Error mendapatkan nama item';
                        }
                    })
                    ->make(true);
            }

            return response()->json([
                'success' => true,
                'data' => $goodsins
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error saat memproses data goods: ' . $e->getMessage());
            return response()->json([
                'error' => true,
                'message' => 'Terjadi kesalahan saat memproses data',
                'details' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
}