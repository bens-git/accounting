<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class TransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // Get request parameters
        $page = $request->input('page', 1);
        $itemsPerPage = $request->input('itemsPerPage', 10);
        $sortBy = $request->input('sortBy.0.key', 'types.id'); // Default sort by id
        $order = $request->input('sortBy.0.order', 'asc'); // Default order ascending
        $search = $request->input('search', '');
        $startDate = $request->input('startDate');
        $endDate = $request->input('endDate');

        // Base query for data
        $query = Transaction::with(['user', 'recipient']);

        // Apply search filter if needed
        if (!empty($search)) {
            $query->where(function ($query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%')
                    ->orWhere('party', 'like', '%' . $search . '%');
            });
        }


        // Apply type filter if provided
        if ($request->filled('type')) {
            $query->where('type', '=', $request->input('type'));
        }



        // Apply sorting
        $query->orderBy($sortBy, $order);


        // Apply pagination
        $transactions = $query->paginate($itemsPerPage, ['*'], 'page', $page);
        $transactionsArray = $transactions->items();
        $total = $transactions->total();

        // Return response
        return response()->json([
            'count' => $total,
            'types' => $transactionsArray
        ]);
    }





    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|max:255',
            'party' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'date' => 'required|date',
            'payment_method' => 'nullable|string|max:255',
            'details' => 'nullable|string|max:255',
            'tag' => 'nullable|string|max:255',
            'recipient_id' => 'nullable|integer|exists:users,id',
            'recurrence_type' => 'nullable|string|max:255',
            'recurrence_start_date' => 'nullable|date',
            'recurrence_end_date' => 'nullable|date',
        ]);
        $user = auth()->user();

        $transaction = Transaction::create($validated);

        return response()->json($transaction);
    }



    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {


        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|max:255',
            'party' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'date' => 'required|date',
            'payment_method' => 'nullable|string|max:255',
            'details' => 'nullable|string|max:255',
            'tag' => 'nullable|string|max:255',
            'recipient_id' => 'nullable|integer|exists:users,id',
            'recurrence_type' => 'nullable|string|max:255',
            'recurrence_start_date' => 'nullable|date',
            'recurrence_end_date' => 'nullable|date',
        ]);

        $transaction = Transaction::findOrFail($id);
        $transaction->fill($validated);
        $transaction->save();


        return response()->json($transaction);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $user = Auth::user();
        $transaction = Transaction::where('id', $id)->where('user_id', $user->id)->first();
        if (!$transaction) {
            return response()->json(['message' => 'Transaction not found or you do not have permission to delete it'], 404);
        }


        // Delete the transaction
        $transaction->delete();

        return response()->json(['message' => 'Transaction deleted successfully']);
    }


    public function getTypesEnumOptions()
    {
        // Get the enum values for the 'status' column from the transactions table
        $enumValues = DB::select(DB::raw('SHOW COLUMNS FROM transactions WHERE Field = "type"'));

        // Extract the enum options from the result
        $enumOptions = [];
        if (isset($enumValues[0])) {
            preg_match('/^enum\((.*)\)$/', $enumValues[0]->Type, $matches);
            if (isset($matches[1])) {
                $enumOptions = explode(',', $matches[1]);
                $enumOptions = array_map(function ($value) {
                    return trim($value, "'");
                }, $enumOptions);
            }
        }

        return response()->json($enumOptions);
    }
}
