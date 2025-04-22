<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ListOfMemberModel;
use App\Models\Tithes;
use Carbon\Carbon;

class TithesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $listOfMembers = ListOfMemberModel::orderBy('church_status', 'asc')
        ->orderBy('created_at', 'asc')
        ->get();

        $today = Carbon::today();

        $tithes = Tithes::with('member')
        ->whereDate('created_at', $today)
        ->orderBy('created_at', 'desc')
        ->get();

        return response()->json([
            'listOfMembers' => $listOfMembers,
            'tithes' => $tithes
        ], 200);
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
        // Validate the request data
        $validatedData = $request->validate([
            'tithes' => 'required|array',
            'tithes.*.member_id' => 'required|integer',
            'tithes.*.type' => 'required|string',
            'tithes.*.amount' => 'required|numeric',
            'tithes.*.payment_method' => 'required|string',
            'tithes.*.notes' => 'nullable|string',
        ]);

        $createdTithes = [];
        $existingMembers = [];
        $today = Carbon::today();

        // Iterate over each set of tithes data
        foreach ($validatedData['tithes'] as $titheData) {
            // Check if a tithes record for this member already exists today
            $existingTithe = Tithes::where('member_id', $titheData['member_id'])
                ->whereDate('created_at', $today)
                ->first();

            if (!$existingTithe) {
                $tithes = new Tithes();
                $tithes->member_id = $titheData['member_id'];
                $tithes->type = $titheData['type'];
                $tithes->amount = $titheData['amount'];
                $tithes->payment_method = $titheData['payment_method'];
                $tithes->notes = $titheData['notes'] ?? null;

                // Save the tithes record to the database
                $tithes->save();

                // Add the saved tithes to the array
                $createdTithes[] = $tithes;
            } else {
                // Add the member_id to the existing members array
                $existingMembers[] = $titheData['member_id'];
            }
        }

        $responseMessage = 'Tithes Added successfully';
        if (!empty($existingMembers)) {
            $responseMessage .= '. Tithes Added successfully';
        }

        return response()->json([
            'tithes' => $createdTithes,
            'message' => $responseMessage
        ], 200);
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
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        // Validate the request data
        $validatedData = $request->validate([
            'member_id' => 'required|integer',
            'amount' => 'required|numeric',
            'type' => 'required|string',
            'payment_method' => 'required|string',
            'notes' => 'nullable|string',
        ]);

        // Find the tithes record by ID
        $tithes = Tithes::find($id);

        if (!$tithes) {
            return response()->json(['message' => 'Tithes record not found'], 404);
        }

        // Update the tithes record with validated data
        $tithes->member_id = $validatedData['member_id'];
        $tithes->amount = $validatedData['amount'];
        $tithes->type = $validatedData['type'];
        $tithes->payment_method = $validatedData['payment_method'];
        $tithes->notes = $validatedData['notes'] ?? null;

        // Save the updated tithes record to the database
        $tithes->save();

        return response()->json([
            'tithes' => $tithes,
            'message' => 'Tithes updated successfully'
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
