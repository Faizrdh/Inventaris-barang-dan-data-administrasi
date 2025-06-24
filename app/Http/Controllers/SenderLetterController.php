<?php

namespace App\Http\Controllers;

use App\Models\SenderLetter;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class SenderLetterController extends Controller
{
    public function index(): View
    {
        return view('admin.master.sender-letter');
    }

    public function list(Request $request): JsonResponse
    {
        try {
            $senderLetters = SenderLetter::query()->get();
            
            if($request->ajax()){
                return DataTables::of($senderLetters)
                    ->addColumn('tindakan', function($data){
                        // Hanya tampilkan action buttons untuk employee
                        if(Auth::user()->role->name == 'employee') {
                            $button = '<a href="javascript:void(0)" class="btn btn-success btn-sm ubah" id="'.$data->id.'"><i class="fas fa-edit"></i> '.__("edit").'</a>';
                            $button .= ' <a href="javascript:void(0)" class="btn btn-danger btn-sm hapus" id="'.$data->id.'"><i class="fas fa-trash"></i> '.__("delete").'</a>';
                            return $button;
                        }
                        // Jika bukan employee, return empty string
                        return '';
                    })
                    ->rawColumns(['tindakan'])
                    ->make(true);
            }
            
            return response()->json([
                'status' => 'error',
                'message' => 'Request harus menggunakan AJAX'
            ], 400);
            
        } catch (\Exception $e) {
            Log::error('Error in sender letter list: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function save(Request $request): JsonResponse
    {
        try {
            // Cek apakah user adalah employee
            if(Auth::user()->role->name != 'employee') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized. Only employees can add data.'
                ], 403);
            }

            $validated = $request->validate([
                'from_department' => 'required|string|max:255',
                'destination' => 'required|string|max:255'
            ]);

            $senderLetter = new SenderLetter();
            $senderLetter->from_department = $validated['from_department'];
            $senderLetter->destination = $validated['destination'];
            
            $status = $senderLetter->save();
            
            if(!$status){
                return response()->json([
                    "message" => __("failed to save")
                ])->setStatusCode(400);
            }
            
            return response()->json([
                "message" => __("saved successfully")
            ])->setStatusCode(200);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
            
        } catch (\Exception $e) {
            Log::error('Error saving sender letter: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function detail(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'id' => 'required|integer|exists:sender_letters,id'
            ]);
            
            $senderLetter = SenderLetter::find($validated['id']);
            
            if(!$senderLetter) {
                return response()->json([
                    "success" => false,
                    "message" => "Sender letter not found."
                ], 404);
            }
            
            return response()->json([
                "data" => $senderLetter
            ])->setStatusCode(200);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
            
        } catch (\Exception $e) {
            Log::error('Error getting sender letter detail: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request): JsonResponse
    {
        try {
            // Cek apakah user adalah employee
            if(Auth::user()->role->name != 'employee') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized. Only employees can edit data.'
                ], 403);
            }

            $validated = $request->validate([
                'id' => 'required|integer|exists:sender_letters,id',
                'from_department' => 'required|string|max:255',
                'destination' => 'required|string|max:255'
            ]);
            
            $senderLetter = SenderLetter::find($validated['id']);
            
            if(!$senderLetter) {
                return response()->json([
                    "success" => false,
                    "message" => "Sender letter not found."
                ], 404);
            }
            
            $senderLetter->fill($validated);
            $status = $senderLetter->save();
            
            if(!$status){
                return response()->json([
                    "message" => __("data failed to change")
                ])->setStatusCode(400);
            }
            
            return response()->json([
                "message" => __("data changed successfully")
            ])->setStatusCode(200);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
            
        } catch (\Exception $e) {
            Log::error('Error updating sender letter: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function delete(Request $request): JsonResponse
    {
        try {
            // Cek apakah user adalah employee
            if(Auth::user()->role->name != 'employee') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized. Only employees can delete data.'
                ], 403);
            }

            $request->validate([
                'id' => 'required|integer|exists:sender_letters,id'
            ]);
            
            $senderLetter = SenderLetter::find($request->id);
            
            if(!$senderLetter) {
                return response()->json([
                    "success" => false,
                    "message" => "Sender letter not found."
                ], 404);
            }
            
            $status = $senderLetter->delete();
            
            if(!$status){
                return response()->json([
                    "message" => __("data failed to delete")
                ])->setStatusCode(400);
            }
            
            return response()->json([
                "message" => __("data deleted successfully")
            ])->setStatusCode(200);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
            
        } catch (\Exception $e) {
            Log::error('Error deleting sender letter: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }
}