<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use App\Models\GoodsOut;
use App\Models\Item;
use Yajra\DataTables\DataTables;
use Carbon\Carbon;

class ReportGoodsOutController extends Controller
{
    public function index(): View 
    {
        return view('admin.master.laporan.keluar');
    }

    public function list(Request $request): JsonResponse
    {
        try {

            if($request->ajax()) {
                try {
                    if(empty($request->start_date) && empty($request->end_date)) {
                        $goodsouts = GoodsOut::with('item', 'user', 'customer');
                    } else {
                        $goodsouts = GoodsOut::with('item', 'user', 'customer')
                                    ->whereBetween('date_out', [$request->start_date, $request->end_date]);
                    }
                    
                    $goodsouts = $goodsouts->latest()->get();
                    
                    return DataTables::of($goodsouts)
                        ->addColumn('quantity', function($data) {
                            try {
                                $item = Item::with("unit")->find($data->item->id);
                                if (!$item || !$item->unit) {
                                    return $data->quantity."/tidak ditemukan";
                                }
                                return $data->quantity."/".$item->unit->name;
                            } catch (\Exception $e) {
                                \Illuminate\Support\Facades\Log::error('Error pada kolom quantity: ' . $e->getMessage());
                                return $data->quantity."/error";
                            }
                        })
                        ->addColumn("date_out", function($data) {
                            try {
                                return Carbon::parse($data->date_out)->format('d F Y');
                            } catch (\Exception $e) {
                                \Illuminate\Support\Facades\Log::error('Error pada kolom date_out: ' . $e->getMessage());
                                return 'Format tanggal tidak valid';
                            }
                        })
                        ->addColumn("kode_barang", function($data) {
                            try {
                                return $data->item ? $data->item->code : 'Kode tidak tersedia';
                            } catch (\Exception $e) {
                                \Illuminate\Support\Facades\Log::error('Error pada kolom kode_barang: ' . $e->getMessage());
                                return 'Error mendapatkan kode';
                            }
                        })
                        ->addColumn("customer_name", function($data) {
                            try {
                                return $data->customer ? $data->customer->name : 'Customer tidak tersedia';
                            } catch (\Exception $e) {
                                \Illuminate\Support\Facades\Log::error('Error pada kolom customer_name: ' . $e->getMessage());
                                return 'Error mendapatkan customer';
                            }
                        })
                        ->addColumn("item_name", function($data) {
                            try {
                                return $data->item ? $data->item->name : 'Item tidak tersedia';
                            } catch (\Exception $e) {
                                \Illuminate\Support\Facades\Log::error('Error pada kolom item_name: ' . $e->getMessage());
                                return 'Error mendapatkan nama item';
                            }
                        })
                        ->make(true);
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error('Error saat memproses data goods out: ' . $e->getMessage());
                    return response()->json([
                        'error' => true,
                        'message' => 'Terjadi kesalahan saat memproses data',
                        'details' => config('app.debug') ? $e->getMessage() : null
                    ], 500);
                }
            }
            
            return response()->json(['message' => 'Request harus melalui AJAX'], 400);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error pada controller list goods out: ' . $e->getMessage());
            return response()->json([
                'error' => true,
                'message' => 'Terjadi kesalahan pada server',
                'details' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
}