<?php

namespace App\Http\Controllers;

use App\Models\Draft;
use App\Models\User;
use App\Models\Party;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use DateTime;
use Illuminate\Support\Facades\Log;

class DraftController extends Controller
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
        $sortBy = $request->input('sortBy'); // Default sort by id
        $search = $request->input('search', '');
        $inputMonth = $request->input('month');


        if ($inputMonth) {
            // Convert full month name to numeric month
            $month = DateTime::createFromFormat('F', ucfirst(strtolower($inputMonth)))->format('n');
        }

        // Base query for data
        $query = Draft::with(['user', 'recipient', 'party']);


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


        if (isset($month)) {
            $query->where(function ($query) use ($month) {
                // Exclude BI-MONTHLY based on month parity
                if ($month % 2 == 0) {
                    // Even month: exclude BI-MONTHLY ODD
                    $query->where('recurrence_type', '!=', 'BI-MONTHLY ODD');
                } else {
                    // Odd month: exclude BI-MONTHLY EVEN
                    $query->where('recurrence_type', '!=', 'BI-MONTHLY EVEN');
                }

                // Include YEARLY only if recurrence_start_month matches the input month
                $query->where(function ($subQuery) use ($month) {
                    $subQuery->where('recurrence_type', '!=', 'YEARLY') // Exclude other YEARLY drafts
                        ->orWhere('recurrence_type', 'YEARLY')
                        ->where('recurrence_start_month', $month); // Only include YEARLY if recurrence_start_month matches
                });
            });
        }




        // Apply sorting
        if ($sortBy) {
            foreach ($sortBy as $sort) {
                $key = $sort['key'];
                $order = $sort['order'];


                $query->orderBy($key, $order);
            }
        }


        // Apply pagination
        if ($itemsPerPage == -1) {
            $draftsArray = $query->get()->toArray(); // Get all items without pagination
            $total = count($draftsArray);
        } else {
            $drafts = $query->paginate($itemsPerPage, ['*'], 'page', $page);
            $draftsArray = $drafts->items();
            $total = $drafts->total();
        }

        // Return response
        return response()->json([
            'count' => $total,
            'drafts' => $draftsArray
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
        $users = User::orderBy('name', 'ASC')->select('name', 'id')->get();

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
            'payment_method' => 'nullable|string|max:255',
            'details' => 'nullable|string|max:255',
            'tag' => 'nullable|string|max:255',
            'user_id' => 'required|integer|exists:users,id',
            'recipient_id' => 'nullable|integer|exists:users,id',
            'recurrence_type' => 'required|string|max:255',
            'recurrence_start_date' => 'nullable|date',
            'recurrence_end_date' => 'nullable|date',
        ]);

        $draft = Draft::create($validated);

        return response()->json($draft);
    }


    public function populateMonth(Request $request)
    {
        $validated = $request->validate([
            'month' => 'required|string|max:255',
            'year' => 'required|integer',
        ]);

        // Convert full month name to numeric month
        $firstDayOfMonth = date('Y-m-d', strtotime("first day of " . $validated['month'] . " " . $validated['year']));


        $newRequest = new Request([
            'itemsPerPage' => -1,
            'month' => $validated['month'],
        ]);


        // Call methodTwo and pass the modified request
        $response = $this->index($newRequest);

        if ($response instanceof JsonResponse) {
            // Decode the JSON response to a PHP array
            $data = $response->getData(true); // Pass `true` for associative array
            $drafts = $data['drafts'];
        }

        foreach ($drafts as $draft) {
            $transactionData = [
                'name' => $draft['name'],
                'type' => $draft['type'],
                'party_id' => $draft['party_id'],
                'amount' => $draft['amount'],
                'date' => $firstDayOfMonth,
                'payment_method' => $draft['payment_method'],
                'details' => $draft['details'],
                'tag' => $draft['tag'],
                'user_id' => $draft['user_id'],
                'recipient_id' => $draft['recipient_id'],
            ];
            $transaction = Transaction::create($transactionData);
        }



        return response()->json(['message' => 'Drafts populated']);
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
            'payment_method' => 'nullable|string|max:255',
            'details' => 'nullable|string|max:255',
            'tag' => 'nullable|string|max:255',
            'user_id' => 'nullable|integer|exists:users,id',
            'recipient_id' => 'nullable|integer|exists:users,id',
            'recurrence_type' => 'nullable|string|max:255',
            'recurrence_start_date' => 'nullable|date',
            'recurrence_end_date' => 'nullable|date',
        ]);

        $draft = Draft::findOrFail($id);
        $draft->fill($validated);
        $draft->save();


        return response()->json($draft);
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
        $draft = Draft::where('id', $id)->where('user_id', $user->id)->first();
        if (!$draft) {
            return response()->json(['message' => 'Draft not found or you do not have permission to delete it'], 404);
        }


        // Delete the draft
        $draft->delete();

        return response()->json(['message' => 'Draft deleted successfully']);
    }


    public function getTypesEnumOptions()
    {
        // Get the enum values for the 'status' column from the drafts table
        $enumValues = DB::select(DB::raw('SHOW COLUMNS FROM drafts WHERE Field = "type"'));

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
        $enumValues = DB::select(DB::raw('SHOW COLUMNS FROM drafts WHERE Field = "payment_method"'));

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
        $enumValues = DB::select(DB::raw('SHOW COLUMNS FROM drafts WHERE Field = "tag"'));

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
