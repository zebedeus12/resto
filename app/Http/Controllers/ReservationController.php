<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Reservation;
use Illuminate\Support\Facades\Auth;
use Yajra\Datatables\Datatables;


class ReservationController extends Controller
{
    public function index()
    {
        $user_id = auth()->user()->id; 
        $reservations = Reservation::where('user_id', $user_id)->get(); 

        return view('reservation.index', compact('reservations'));
    }

    public function store(Request $request)
    {
        // Validate the request data
        $validated = $request->validate([
            'cust_name' => 'required|string|max:255',
            'contact' => 'required|string|email|max:255',
            'reservation_date' => 'required|date',
            'number_guest' => 'required|integer|min:1',
            'special_request' => 'nullable|string|max:255',
            'user_id' => 'required|integer', // Ensure user_id is validated
        ]);

        // Extract user_id from validated data
        $user_id = $validated['user_id'];

        // Create reservation
        Reservation::create([
            'cust_name' => $validated['cust_name'],
            'contact' => $validated['contact'],
            'reservation_date' => $validated['reservation_date'],
            'number_guest' => $validated['number_guest'],
            'special_request' => $validated['special_request'],
            'user_id' => $user_id,
        ]);

        // Return a JSON response indicating success
        return response()->json(['message' => 'Reservation made successfully.']);
    }    

    public function reservationsData()
    {
        $user_id = auth()->user()->id; 
        $data = Reservation::where('user_id', $user_id)->get(); 
        return DataTables::of($data)
            ->addIndexColumn() // Add index column
            ->addColumn('action', function($row){
                // Add action column with buttons of the same size
                $editBtn = '<a href="javascript:void(0)" class="edit btn btn-primary btn-sm me-1">Edit</a>';
                $deleteBtn = '<a href="javascript:void(0)" class="delete btn btn-danger btn-sm">Delete</a>';
                return $editBtn . $deleteBtn;
            })
            ->addColumn('remark', function($row){
                // Add remark column with badges of the same size
                if ($row->approval_by_admin == 1) {
                    return '<span class="badge bg-success bg-opacity-20 p-2">Approved</span>';
                } else {
                    return '<span class="badge bg-warning bg-opacity-20 p-2">Waiting</span>';
                }
            })
            ->rawColumns(['action', 'remark'])
            ->make(true);
    }

    public function edit()
    {
    }

    public function update(Request $request)
    {
        
    }


    public function destroy($id)
    {

    }
}