<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ListOfMemberModel;
use App\Models\Expense;
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

        $today = Carbon::today()->toDateString();

        $tithes = Tithes::with('member')
        ->orderBy('created_at', 'desc')
        ->whereDate('date_created', $today)
        ->get();

        
        $lastSunday = Carbon::now('Asia/Manila')->previous(Carbon::SUNDAY);

        $totalAmount = Tithes::sum('amount');
        $totalAmountToday = Tithes::
        whereDate('created_at', $today)
        ->sum('amount');
        $totalAmountLastSunday = Tithes::whereDate('created_at', $lastSunday)
        ->sum('amount');

        $expenses = Expense::orderBy('created_at', 'desc')
        ->get();
        

        return response()->json([
            'listOfMembers' => $listOfMembers,
            'tithes' => $tithes,
            'totalAmount' => $totalAmount,
            'totalAmountToday' => $totalAmountToday,
            'totalAmountLastSunday' => $totalAmountLastSunday,
            'expenses' => $expenses,
        ], 200);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        // Validate the request data for expenses
        $validatedData = $request->validate([
            'expenses' => 'required|array',
            'expenses.*.title' => 'required|string',
            'expenses.*.amount' => 'required|numeric',
            'expenses.*.date_created' => 'required|date',
        ]);

        $createdExpenses = [];
        foreach ($validatedData['expenses'] as $expenseData) {
            $expense = new Expense();
            $expense->title = $expenseData['title'];
            $expense->amount = $expenseData['amount'];
            $expense->date_created = $expenseData['date_created'];
            $expense->save();
            $createdExpenses[] = $expense;
        }

        return response()->json([
            'expenses' => $createdExpenses,
            'message' => 'Expenses processed successfully.'
        ], 200);
    }
    

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validate the request data
        $validatedData = $request->validate([
            'tithes' => 'required|array',
            'tithes.*.member_id' => 'nullable|integer', // Make member_id nullable
            'tithes.*.type' => 'required|string',
            'tithes.*.amount' => 'required|numeric',
            'tithes.*.payment_method' => 'required|string',
            'tithes.*.date_created' => 'nullable|date', 
            'tithes.*.notes' => 'nullable|string',
        ]);

        $createdTithes = [];
        $existingMembers = [];
        $today = Carbon::today();

        // Iterate over each set of tithes data
        foreach ($validatedData['tithes'] as $titheData) {
            // Skip the check for existing records if member_id is null
            if ($titheData['member_id'] !== null) {
                $existingTithe = Tithes::where('member_id', $titheData['member_id'])
                    ->whereDate('date_created', $today)
                    ->first();
            } else {
                $existingTithe = null;
            }

            if (!$existingTithe) {
                $tithes = new Tithes();
                $tithes->member_id = $titheData['member_id'] ?? null; // Set member_id to null if not provided
                $tithes->type = $titheData['type'];
                $tithes->amount = $titheData['amount'];
                $tithes->payment_method = $titheData['payment_method'];
                $tithes->date_created = $titheData['date_created'] ?? $today->toDateString(); // Use today's date if not provided
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

        // Construct the response message
        $responseMessage = 'Tithes added successfully.';
        if (!empty($existingMembers)) {
            $responseMessage .= ' Note: Some members already had tithes recorded today.';
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
            'date_created' => 'required|date', 
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
        $tithes->date_created = $validatedData['date_created']; 
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
        $tithes = Tithes::find($id);
        if (!$tithes) {
            return response()->json(['message' => 'Tithes record not found'], 404);
        }
        $tithes->delete();
        return response()->json(['message' => 'Tithes deleted successfully'], 200);
    }
}
