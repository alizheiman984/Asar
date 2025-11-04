<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use App\Models\Employee;
use App\Models\Benefactor;
use App\Models\DonorPayment;
use Illuminate\Http\Request;
use App\Models\VolunteerTeam;
use App\Http\Controllers\Controller;
use App\Http\Resources\TeamResource;
use Illuminate\Support\Facades\Storage;
use App\Http\Resources\VolunteerResource;
use App\Http\Resources\DonorPaymentResource;

class DonorPaymentController extends Controller
{
    public function getallteamaccepted(){
        $volunter_team = VolunteerTeam::where('status', 'accepted')->get();
        return TeamResource::collection($volunter_team);
        
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $volunteer = auth()->user(); // أو auth()->guard('benefactor')->user()

        $payments = DonorPayment::with('team')->where('volunteer_id', $volunteer->id)->get();

        return DonorPaymentResource::collection($payments);

        // return response()->json($payments);
    }



    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'employee_id' => 'nullable|exists:employees,id',
            'team_id' => 'required|exists:volunteer_teams,id',
            'amount' => 'required|numeric',
            'transfer_number' => 'required|string',
            'type' => '|in:حوالة,كاش',
            'status' => 'in:pending,accepted,rejected',
            'payment_date' => 'nullable|date',
            'image' => 'nullable',
        ]);

        $benefactor = auth()->user();

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = uniqid() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('uploads/DonorPayment'), $imageName);
            $imageRelativePath = 'uploads/DonorPayment/' . $imageName;
        } else {
            return response()->json(['message' => 'No image uploaded'], 400);
        }
        
        // return $request->all();
        $payment = DonorPayment::create([
            'team_id' => $request->team_id,
            // 'benefactor_id' => $benefactor->id,
            'volunteer_id' =>auth()->user()->id,
            // 'employee_id' => $request->employee_id,
        
            'amount' => $request->amount,
            'transfer_number' => $request->transfer_number,
            'type' => 'حوالة',
            'status' => 'pending',
            'payment_date' => Carbon::now(),
            'image' => $imageRelativePath,
        ]);

        return response()->json([
            'message' => 'Payment created successfully',
            'payment' => $payment,
        ]);
    }

   
    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $payment = DonorPayment::with('team')->find($id);
        if(!$payment)
        {
            return response()->json(['message' => 'Payment not found'], 404);
        }
    
        
    return new DonorPaymentResource($payment);
        
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $payment = DonorPayment::find($id);
    
        if (!$payment) {
            return response()->json(['message' => 'Payment not found'], 404);
        }
    
        $request->validate([
            'employee_id' => 'nullable|exists:employees,id',
            'team_id' => 'required|exists:volunteer_teams,id',
            'amount' => 'required|numeric',
            'transfer_number' => 'required|string',
            'type' => 'in:حوالة,كاش',
            'status' => 'in:pending,accepted,rejected',
            'payment_date' => 'nullable|date',
            'image' => 'nullable|file|image|max:2048',
        ]);
    
        // إذا تم رفع صورة جديدة
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = uniqid() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('uploads/DonorPayment'), $imageName);
            $imageRelativePath = 'uploads/DonorPayment/' . $imageName;
    
            // حذف الصورة القديمة إن وجدت (اختياري)
            if ($payment->image && file_exists(public_path($payment->image))) {
                unlink(public_path($payment->image));
            }
    
            $payment->image = $imageRelativePath;
        }
    
        // تحديث الحقول
        $payment->update([
            'employee_id' => $request->employee_id,
            'team_id' => $request->team_id,
            'amount' => $request->amount,
            'transfer_number' => $request->transfer_number,
            'type' => $request->type ?? $payment->type,
            'status' => $request->status ?? $payment->status,
            'payment_date' => $request->payment_date ?? $payment->payment_date,
        ]);
    
        return response()->json([
            'message' => 'Payment updated successfully',
            'payment' => $payment,
        ]);
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $donorPayment = DonorPayment::find($id);

        if ($donorPayment) {
            $donorPayment->delete();
        
            return response()->json([
                'success' => true,
                'message' => 'Donor payment deleted successfully.'
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Donor payment not found.'
            ], 404);
        }
        

    }

    public function getBenefactorPayments(Benefactor $benefactor)
    {
        $payments = DonorPayment::where('benefactor_id', $benefactor->id)
            ->with(['team', 'employee'])
            ->orderBy('date_of_payment', 'desc')
            ->get();

        return response()->json($payments);
    }

    public function getTeamPayments(VolunteerTeam $team)
    {
        $payments = DonorPayment::where('team_id', $team->id)
            ->with(['benefactor', 'employee'])
            ->orderBy('date_of_payment', 'desc')
            ->get();

        return response()->json($payments);
    }

    public function getEmployeePayments(Employee $employee)
    {
        $payments = DonorPayment::where('employee_id', $employee->id)
            ->with(['benefactor', 'team'])
            ->orderBy('date_of_payment', 'desc')
            ->get();

        return response()->json($payments);
    }

    public function getPaymentsByStatus($status)
    {
        $validStatuses = ['pending', 'completed', 'failed'];
        
        if (!in_array($status, $validStatuses)) {
            return response()->json(['message' => 'Invalid status'], 422);
        }

        $payments = DonorPayment::where('status', $status)
            ->with(['benefactor', 'team', 'employee'])
            ->orderBy('date_of_payment', 'desc')
            ->get();

        return response()->json($payments);
    }
}
