<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    public function index()
    {
        return view('admin.settings.profile'); 
    }

    public function update(Request $request)
    {
        try {
            $id = $request->id;
            $user = User::find($id);
            
            if (!$user) {
                return response()->json([
                    "message" => __("User not found")
                ])->setStatusCode(404);
            }

            // Handle image upload
            if ($request->hasFile('image')) {
                // Delete old image if exists
                if (!empty($user->image) && Storage::disk('public')->exists('profile/' . $user->image)) {
                    Storage::disk('public')->delete('profile/' . $user->image);
                }
                
                // Store new image
                $image = $request->file('image');
                $imageName = time() . '_' . $image->getClientOriginalName();
                $image->storeAs('profile', $imageName, 'public');
                $user->image = $imageName;
            }

            // Handle password update
            if (!empty($request->password)) {
                $user->password = Hash::make($request->password);
            }

            // Handle name update
            if ($request->has('name') && !empty($request->name)) {
                $user->name = $request->name;
            }

            // Handle username update
            if ($request->has('username') && !empty($request->username)) {
                $user->username = $request->username;
            }

            // Save user data
            $status = $user->save();
            
            if (!$status) {
                return response()->json([
                    "message" => __("data failed to change")
                ])->setStatusCode(400);
            }

            return response()->json([
                "message" => __("data changed successfully"),
                "user" => [
                    'name' => $user->name,
                    'username' => $user->username,
                    'image' => $user->image
                ]
            ])->setStatusCode(200);
            
        } catch (\Exception $e) {
            return response()->json([
                "message" => "An error occurred: " . $e->getMessage()
            ])->setStatusCode(500);
        }
    }
}