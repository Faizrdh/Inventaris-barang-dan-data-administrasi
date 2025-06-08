<?php

namespace App\Http\Controllers;

use App\Models\SenderLetter;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Http\Exceptions\HttpResponseException;

class SenderLetterController extends Controller
{
    public function index()
    {
        return view('admin.master.sender-letter');
    }

    public function list(Request $request)
    {
        $senderLetters = SenderLetter::query()->get();
        
        if($request->ajax()){
            return DataTables::of($senderLetters)
                ->addColumn('tindakan', function($data){
                 $button = '<a href="javascript:void(0)" class="btn btn-success btn-sm ubah" id="'.$data->id.'"><i class="fas fa-edit"></i> '.__("edit").'</a>';
                $button .= ' <a href="javascript:void(0)" class="btn btn-danger btn-sm hapus" id="'.$data->id.'"><i class="fas fa-trash"></i> '.__("delete").'</a>';
                return $button;
                })
                ->rawColumns(['tindakan'])
                ->make(true);
        }
        
        return response()->json([
            'senderLetters' => $senderLetters
        ]);
    }

    public function save(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'from_department' => 'required|string|max:255',
            'destination' => 'required|string|max:255'
        ]);

        $senderLetter = new SenderLetter();
        $senderLetter->name = $validated['name'];
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
    }

    public function detail(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'id' => 'required|integer'
        ]);
        
        $senderLetter = SenderLetter::find($validated['id']);
        
        if(!$senderLetter) {
            throw new HttpResponseException(response([
                "message" => "not found."
            ], 404));
        }
        
        return response()->json([
            "data" => $senderLetter
        ])->setStatusCode(200);
    }

    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'id' => 'required|integer',
            'name' => 'required|string|max:255',
            'from_department' => 'required|string|max:255',
            'destination' => 'required|string|max:255'
        ]);
        
        $senderLetter = SenderLetter::find($validated['id']);
        
        if(!$senderLetter) {
            throw new HttpResponseException(response([
                "message" => "not found."
            ], 404));
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
    }

    public function delete(Request $request): JsonResponse
    {
        $id = $request->id;
        $senderLetter = SenderLetter::find($id);
        
        if(!$senderLetter) {
            throw new HttpResponseException(response([
                "message" => "not found."
            ], 404));
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
    }
}