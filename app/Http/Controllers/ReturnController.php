<?php

namespace App\Http\Controllers;

use App\Models\ItemReturn;
use App\Models\Item;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ReturnController extends Controller
{
    public function index()
    {
        $customers = Customer::all();
        $items = Item::all();
        $returns = ItemReturn::with('customer')->orderBy('created_at', 'desc')->get();
        
        return view('admin.master.returns', compact('customers', 'items', 'returns'));
    }

    public function save(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'borrower_id' => 'required|exists:customers,id',
            'item_code' => 'required|exists:items,code',
            'return_date' => 'required|date',
            'status' => 'required|in:Baik,Rusak',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('error', 'Validation failed: ' . $validator->errors()->first());
        }

        try {
            // Check if item exists
            $item = Item::where('code', $request->item_code)->first();
            if (!$item) {
                return redirect()->back()
                    ->with('error', 'Item not found')
                    ->withInput();
            }

            // Create return record
            ItemReturn::create([
                'borrower_id' => $request->borrower_id,
                'item_code' => $request->item_code,
                'return_date' => $request->return_date,
                'status' => $request->status,
            ]);

            // Since Item model doesn't have status column, we'll skip updating item status
            // The item status is tracked in the ItemReturn record instead
            // If you need to track item status, you'll need to add a migration first

            return redirect()->route('return.index')
                ->with('success', 'Data pengembalian berhasil ditambahkan');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error saving data: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function edit($id)
    {
        try {
            $return = ItemReturn::findOrFail($id);
            $customers = Customer::all();
            $items = Item::all();
            $returns = ItemReturn::with('customer')->orderBy('created_at', 'desc')->get();
            
            return view('admin.master.returns', compact('customers', 'items', 'returns', 'return'));
        } catch (\Exception $e) {
            return redirect()->route('return.index')
                ->with('error', 'Return record not found');
        }
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'borrower_id' => 'required|exists:customers,id',
            'item_code' => 'required|exists:items,code',
            'return_date' => 'required|date',
            'status' => 'required|in:Baik,Rusak',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('error', 'Validation failed: ' . $validator->errors()->first());
        }

        try {
            $return = ItemReturn::findOrFail($id);
            
            $return->update([
                'borrower_id' => $request->borrower_id,
                'item_code' => $request->item_code,
                'return_date' => $request->return_date,
                'status' => $request->status,
            ]);

            // Since Item model doesn't have status column, we'll skip updating item status
            // The item status is tracked in the ItemReturn record instead

            return redirect()->route('return.index')
                ->with('success', 'Return data updated successfully');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error updating data: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function delete($id)
    {
        try {
            $return = ItemReturn::findOrFail($id);
            $return->delete();

            return redirect()->route('return.index')
                ->with('success', 'Return data deleted successfully');

        } catch (\Exception $e) {
            return redirect()->route('return.index')
                ->with('error', 'Error deleting data: ' . $e->getMessage());
        }
    }
}