<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ListOfMemberModel;
use Illuminate\Support\Facades\Validator;

class EditMemberController extends Controller
{
    
  
    public function edit(Request $request, string $id)
    {
        $listOfMember = ListOfMemberModel::find($id);
    
        if (!$listOfMember) {
            return response()->json(['message' => 'Member not found'], 404);
        }
    
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'role' => 'required|string|max:255',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'church_status' => 'required|integer',
            'attendance_status' => 'required|integer',
        ]);
    
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
    
        // Handle file upload for photo
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('photos', 'public');
            $listOfMember->photo = $photoPath;
        }
    
        // Update the specified fields
        $listOfMember->name = $request->name;
        $listOfMember->role = $request->role;
        $listOfMember->church_status = $request->church_status;
        $listOfMember->attendance_status = $request->attendance_status;
        $listOfMember->save();
    
        return response()->json([
            'listOfMember' => $listOfMember->load('attendances'),
            'message' => 'Member updated successfully'
        ], 200);
    }
}