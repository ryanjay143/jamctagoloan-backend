<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ListOfMemberModel;
use App\Models\Attendance;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Response;
use Carbon\Carbon;


class ListOfMemberController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Retrieve members with church_status of 0
        $listOfMembers = ListOfMemberModel::orderBy('church_status', 'asc')
        ->orderBy('created_at', 'asc')
        ->get();

        // Retrieve members with attendance_status of 1
        $listOfMembersAlreadyAtt = ListOfMemberModel::where('attendance_status', 0)
        ->where('church_status', 0)
        ->get();

        // Retrieve attendance records with status 0 for today only
        $today = Carbon::today()->toDateString();
        $attendanceTodayCount = Attendance::where('status', 0)
            ->whereDate('updated_at', $today)
            ->count();
           
        $attendanceToday = Attendance::with('member')->where('status', 0)
            ->whereDate('updated_at', $today)
            ->get();

        $kidsAttendedTodayCount = Attendance::with('member')->where('status', 0)
        ->whereDate('updated_at', $today)
        ->whereHas('member', function($query) {
            $query->where('role', 'Kids Ministry');
        })->count();

        $kidsAttendedToday = Attendance::with('member')->where('status', 0)
        ->whereDate('updated_at', $today)
        ->whereHas('member', function($query) {
            $query->where('role', 'Kids Ministry');
        })->get();

        

        $adultAttendedTodayCount = Attendance::with('member')->where('status', 0)
        ->whereDate('updated_at', $today)
        ->whereHas('member', function($query) {
            $query->where('role', '!=', 'Kids Ministry');
        })->count();

        $absentTodayCount = Attendance::where('status', 1)
        ->whereDate('updated_at', $today)
        ->count();

        $absentToday = Attendance::with('member')->where('status', 1)
        ->whereDate('updated_at', $today)
        ->get();

        $firstTimerCount = ListOfMemberModel::where('role', 'First timer')
        ->whereDate('updated_at', $today)
        ->count();

        $firstTimer = ListOfMemberModel::where('role', 'First timer')
        ->whereDate('updated_at', $today)
        ->get();

        $lastSunday = Carbon::now()->previous(Carbon::SUNDAY)->toDateString();
        $attendedLastSundayCount = Attendance::with('member')->
        where('status', 0)
        ->whereDate('updated_at', $lastSunday)->count();

        $attendedLastSunday = Attendance::with('member')->
        where('status', 0)
        ->whereDate('updated_at', $lastSunday)->get();


        $overAllAttendance = Attendance::with('member')
        ->whereHas('member', function($query) {
            $query->where('church_status', 0);
        })
        ->get();

        // Return a JSON response with separate keys for each list
        return response()->json([
            'listOfMembers' => $listOfMembers,
            'listOfMembersAlreadyAtt' => $listOfMembersAlreadyAtt,
            'attendanceTodayCount' => $attendanceTodayCount,
            'absentTodayCount' => $absentTodayCount,
            'firstTimerCount' => $firstTimerCount,
            'attendedLastSundayCount' => $attendedLastSundayCount,
            'attendanceToday' => $attendanceToday,
            'firstTimer' => $firstTimer,
            'absentToday' => $absentToday,
            'attendedLastSunday' => $attendedLastSunday,
            'overAllAttendance' => $overAllAttendance,
            'kidsAttendedTodayCount' => $kidsAttendedTodayCount,
            'adultAttendedTodayCount' => $adultAttendedTodayCount,
            'kidsAttendedToday' => $kidsAttendedToday
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'role' => 'required|string|max:255',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Check if a photo is uploaded and store it
        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('photos', 'public');
        }

        $listOfMember = ListOfMemberModel::create([
            'name' => $request->name,
            'role' => $request->role,
            'photo' => $photoPath,
        ]);

        return response()->json($listOfMember, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, string $id)
    {
        // Find the member by ID
        $listOfMember = ListOfMemberModel::find($id);

        // Check if the member exists
        if (!$listOfMember) {
            return response()->json(['message' => 'Member not found'], 404);
        }

        // Update the member's details with new values from the request
        $listOfMember->name = $request->input('name', $listOfMember->name);
        $listOfMember->role = $request->input('role', $listOfMember->role);
        $listOfMember->photo = $request->input('photo', $listOfMember->photo);

        // Save the updated member details
        $listOfMember->save();

        // Return a success response with the updated member details
        return response()->json([
            'listOfMember' => $listOfMember,
            'message' => 'Member details updated successfully'
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $listOfMember = ListOfMemberModel::find($id);
    
        if (!$listOfMember) {
            return response()->json(['message' => 'Member not found'], 404);
        }
    
        $validator = Validator::make($request->all(), [
            'attendance_status' => 'required|integer',
            'status' => 'required|integer',
        ]);
    
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
    
        // Prevent duplicate attendance on the same day
        $today = Carbon::today()->toDateString();
        $alreadyAttended = Attendance::where('member_id', $listOfMember->id)
            ->whereDate('created_at', $today)
            ->exists();
    
        if ($alreadyAttended) {
            $todayDate = Carbon::today()->toFormattedDateString();
            return response()->json([
                'message' => "{$listOfMember->name} has already been recorded."
            ], 409); // 409 Conflict status code
        }
    
        // Update the member's attendance status only if no duplicate is found
        $listOfMember->attendance_status = $request->attendance_status;
        $listOfMember->save();
    
        // Create a new attendance record
        Attendance::create([
            'member_id' => $listOfMember->id,
            'status' => $request->status,
        ]);
    
        return response()->json([
            'listOfMember' => $listOfMember->load('attendances'),
            'message' => 'Attendance status updated successfully'
        ], 200);
    }
        /**
     * Remove the specified resource from storage.
     */

    public function destroy(string $id)
    {
        // Get today's date
        $today = Carbon::today();

        // Find the attendance record with its related member for today
        $attendance = Attendance::with('member')
            ->where('id', $id)
            ->whereDate('created_at', $today) // Assuming 'created_at' is the date field
            ->first();

        if ($attendance) {
            // Update the attendance_status of the related member
            $member = $attendance->member;
            if ($member) {
                $member->attendance_status = 0;
                $member->save();
            }

            // Delete the attendance record
            $attendance->delete();
        }

        // Optionally, return a response or redirect
        return response()->json(['message' => 'Attendance deleted and member status updated successfully.']);
    }
}
