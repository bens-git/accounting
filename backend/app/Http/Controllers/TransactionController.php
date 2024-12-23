<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\User;
use App\Models\Party;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

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
        $sortBy = $request->input('sortBy.0.key', 'date'); // Default sort by id
        $order = $request->input('sortBy.0.order', 'desc'); // Default order ascending
        $search = $request->input('search', '');
        $month = $request->input('month');
        $year = $request->input('year');
        $monthNumber = date('m', strtotime($month));

        // Base query for data
        $query = Transaction::with(['user', 'recipient', 'party']);


        // Apply search filter if needed
        if (!empty($search)) {
            $query->where(function ($query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%')
                    ->orWhereHas('party', function ($query) use ($search) {
                        $query->where('name', 'like', "%{$search}%"); // Search in the related party's name
                    });
            });
        }


        // Apply type filter if provided
        if ($request->filled('type')) {
            $query->where('type', '=', $request->input('type'));
        }

        // Apply tag filter if provided
        if ($request->filled('tag')) {
            $query->where('tag', '=', $request->input('tag'));
        }

        // Year filter
        if ($request->filled('year')) {
            $query->whereYear('date', $year);
        }

        // Month filter
        if ($request->filled('month')) {
            $query->whereMonth('date', $monthNumber);
        }



        // Apply sorting
        $query->orderBy($sortBy, $order);


        // Apply pagination
        if ($itemsPerPage == -1) {
            $transactionsArray = $query->get()->toArray(); // Get all items without pagination
            $total = count($transactionsArray);
        } else {
            $transactions = $query->paginate($itemsPerPage, ['*'], 'page', $page);
            $transactionsArray = $transactions->items();
            $total = $transactions->total();
        }


        // Return response
        return response()->json([
            'count' => $total,
            'transactions' => $transactionsArray
        ]);
    }



    public function getParties(Request $request)
    {


        // Base query for data
        $parties = Party::orderBy('name', 'ASC')->get();

        $total = $parties->count();

        // Return response
        return response()->json([
            'count' => $total,
            'parties' => $parties
        ]);
    }


    public function getUsers(Request $request)
    {


        // Base query for data
        $users = User::orderBy('id', 'ASC')->select('name', 'id', 'maintenance_percentage')->get();

        $total = $users->count();

        // Return response
        return response()->json([
            'count' => $total,
            'users' => $users
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
            'party_id' => 'required|integer|exists:parties,id',
            'amount' => 'required|numeric|min:0.01',
            'date' => 'required|date',
            'payment_method' => 'nullable|string|max:255',
            'details' => 'nullable|string|max:255',
            'tag' => 'nullable|string|max:255',
            'user_id' => 'required|integer|exists:users,id',
            'recipient_id' => 'nullable|integer|exists:users,id',
            'recurrence_type' => 'nullable|string|max:255',
            'recurrence_start_date' => 'nullable|date',
            'recurrence_end_date' => 'nullable|date',
        ]);

        $transaction = Transaction::create($validated);

        return response()->json($transaction);
    }


    public function postParty(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:parties,name',
        ]);

        if ($validator->fails()) {
            // Rename the error key before returning the response
            $errors = $validator->errors()->toArray();

            $customErrors = [];
            foreach ($errors as $key => $messages) {
                $customKey = $key === 'name' ? 'party_name' : $key;
                $customErrors[$customKey] = $messages;
            }

            return response()->json([
                'message' => 'Party creation failed.',
                'errors' => $customErrors,
            ], 422);
        }



        $party = Party::create($validator->validated());

        return response()->json($party);
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

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|max:255',
            'party_id' => 'required|string|exists:parties,id',
            'amount' => 'required|numeric|min:0.01',
            'date' => 'required|date',
            'payment_method' => 'nullable|string|max:255',
            'details' => 'nullable|string|max:255',
            'tag' => 'nullable|string|max:255',
            'user_id' => 'nullable|integer|exists:users,id',
            'recipient_id' => 'nullable|integer|exists:users,id',
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

    public function getPaymentMethodsEnumOptions()
    {
        $enumValues = DB::select(DB::raw('SHOW COLUMNS FROM transactions WHERE Field = "payment_method"'));

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

    public function getTagsEnumOptions()
    {
        $enumValues = DB::select(DB::raw('SHOW COLUMNS FROM transactions WHERE Field = "tag"'));

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

    public function getRecurrenceTypesEnumOptions()
    {
        $enumValues = DB::select(DB::raw('SHOW COLUMNS FROM drafts WHERE Field = "recurrence_type"'));

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
