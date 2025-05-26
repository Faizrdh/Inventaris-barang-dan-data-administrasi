<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use App\Models\Item;
use App\Models\GoodsIn;
use App\Models\GoodsOut;
use Yajra\DataTables\DataTables;

class ReportStockController extends Controller
{
    public function index():View
    {
        return view('admin.master.laporan.stok');
    }

   public function list(Request $request): JsonResponse
{
    try {
        if($request->ajax()) {
            try {
                if(empty($request->start_date) && empty($request->end_date)) {
                    $data = Item::with('goodsOuts', 'goodsIns');
                } else {
                    $data = Item::with(['goodsOuts' => function($query) use ($request) {
                        $query->whereBetween('date_out', [$request->start_date, $request->end_date]);
                    }, 'goodsIns']);
                }
                
                $data = $data->latest()->get();
                
                return DataTables::of($data)
                    ->addColumn('jumlah_masuk', function ($item) {
                        try {
                            return $item->goodsIns ? $item->goodsIns->sum('quantity') : 0;
                        } catch (\Exception $e) {
                            \Illuminate\Support\Facades\Log::error('Error pada kolom jumlah_masuk: ' . $e->getMessage());
                            return 0;
                        }
                    })
                    ->addColumn("jumlah_keluar", function ($item) {
                        try {
                            return $item->goodsOuts ? $item->goodsOuts->sum('quantity') : 0;
                        } catch (\Exception $e) {
                            \Illuminate\Support\Facades\Log::error('Error pada kolom jumlah_keluar: ' . $e->getMessage());
                            return 0;
                        }
                    })
                    ->addColumn("kode_barang", function ($item) {
                        try {
                            return $item->code ?? 'Kode tidak tersedia';
                        } catch (\Exception $e) {
                            \Illuminate\Support\Facades\Log::error('Error pada kolom kode_barang: ' . $e->getMessage());
                            return 'Error mendapatkan kode';
                        }
                    })
                    ->addColumn("stok_awal", function ($item) {
                        try {
                            return $item->quantity ?? 0;
                        } catch (\Exception $e) {
                            \Illuminate\Support\Facades\Log::error('Error pada kolom stok_awal: ' . $e->getMessage());
                            return 0;
                        }
                    })
                    ->addColumn("nama_barang", function ($item) {
                        try {
                            return $item->name ?? 'Nama tidak tersedia';
                        } catch (\Exception $e) {
                            \Illuminate\Support\Facades\Log::error('Error pada kolom nama_barang: ' . $e->getMessage());
                            return 'Error mendapatkan nama';
                        }
                    })
                    ->addColumn("total", function ($item) {
                        try {
                            $totalQuantityIn = $item->goodsIns ? $item->goodsIns->sum('quantity') : 0;
                            $totalQuantityOut = $item->goodsOuts ? $item->goodsOuts->sum('quantity') : 0;
                            $result = ($item->quantity ?? 0) + $totalQuantityIn - $totalQuantityOut;
                            $result = max(0, $result);
                            
                            if($result == 0){
                                return "<span class='text-red font-weight-bold'>".$result."</span>";
                            }
                            return "<span class='text-success font-weight-bold'>".$result."</span>";
                        } catch (\Exception $e) {
                            \Illuminate\Support\Facades\Log::error('Error pada kolom total: ' . $e->getMessage());
                            return "<span class='text-warning font-weight-bold'>Error</span>";
                        }
                    })
                    ->rawColumns(['total'])
                    ->make(true);
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Error saat memproses data item: ' . $e->getMessage());
                return response()->json([
                    'error' => true,
                    'message' => 'Terjadi kesalahan saat memproses data',
                    'details' => config('app.debug') ? $e->getMessage() : null
                ], 500);
            }
        }
        
        return response()->json(['message' => 'Request harus melalui AJAX'], 400);
    } catch (\Exception $e) {
        \Illuminate\Support\Facades\Log::error('Error pada controller list item: ' . $e->getMessage());
        return response()->json([
            'error' => true,
            'message' => 'Terjadi kesalahan pada server',
            'details' => config('app.debug') ? $e->getMessage() : null
        ], 500);
    }
}
}
