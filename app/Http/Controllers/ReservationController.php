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

    public function index_admin()
    {
        // Retrieve all reservations
        $reservations = Reservation::all();

        // Calculate counts based on approval status
        $waitingCount = Reservation::where('approval_by_admin', 0)->count();
        $approvedCount = Reservation::where('approval_by_admin', 1)->count();
        $rejectedCount = Reservation::where('approval_by_admin', 2)->count();
        $canceledCount = Reservation::where('approval_by_admin', 3)->count();

        // Pass the reservations and counts to the view
        return view('reservation.index_admin', compact('reservations', 'waitingCount', 'approvedCount', 'rejectedCount', 'canceledCount'));
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
                $deleteBtn = '<a href="javascript:void(0)" class="delete btn btn-danger btn-sm">Cancel</a>';
                return $editBtn . $deleteBtn;
            })
            ->addColumn('remark', function($row) {
                if ($row->approval_by_admin == 1) {
                    return '<span class="badge bg-success bg-opacity-20 p-2">Approved</span>';
                } elseif ($row->approval_by_admin == 0) {
                    return '<span class="badge bg-warning bg-opacity-20 p-2">Waiting</span>';
                } elseif ($row->approval_by_admin == 2) {
                    return '<span class="badge bg-danger bg-opacity-20 p-2">Rejected</span>';
                } elseif ($row->approval_by_admin == 3) {
                    return '<span class="badge bg-secondary bg-opacity-20 p-2">Canceled</span>';
                } else {
                    return ''; // Handle other cases if necessary
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

    // dashboard admin
    public function approve($id)
    {
        $reservation = Reservation::find($id);
        if ($reservation) {
            $reservation->approval_by_admin = 1; // Set status to approved
            $reservation->approval_date_by_admin = now();
            $reservation->save();
        }

        return redirect()->route('reservation.admin')->with('success', 'Reservation approved successfully.');
    }

    public function reject($id)
    {
        $reservation = Reservation::find($id);
        if ($reservation) {
            $reservation->approval_by_admin = 2; // Set status to rejected
            $reservation->approval_date_by_admin = now();
            $reservation->save();
        }

        return redirect()->route('reservation.admin')->with('success', 'Reservation rejected successfully.');
    }
}