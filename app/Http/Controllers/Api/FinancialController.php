<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use App\Models\Financial;
use App\Models\Benefactor;
use App\Models\DonorPayment;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\FinancialResource;

class FinancialController extends Controller
{
    public function index()
    {
          $employee = auth()->user();

        if (!$employee->team_id) {
            return response()->json(['message' => 'الموظف لا يتبع لأي فريق'], 422);
        }

        $financial = Financial::where('team_id', $employee->team_id)->first();

        if (!$financial) {
            return response()->json(['message' => 'لا توجد بيانات مالية لهذا الفريق'], 404);
        }

        $donations = DonorPayment::with('benefactor','volunter')
            ->where('team_id', $employee->team_id)
            ->orderBy('payment_date', 'desc') 
            ->get()
            ->map(function ($donation) {
                return [
                    'id' => $donation->id,
                    'amount' => $donation->amount,
                    'type' => $donation->type,
                    'status' => $donation->status,
                    'payment_date' => $donation->payment_date,
                    'transfer_number' => $donation->transfer_number,
                    'image' => $donation->image,
                    'benefactor_name' => $donation->benefactor->name ?? $donation->volunter->full_name ,
                    'benefactor_phone' => $donation->benefactor->phone ??$donation->volunter->phone ,
                    'created_at' => $donation->created_at,
                ];
            });

        return response()->json([
            'team_id' => $financial->team_id,
            'total_amount' => $financial->total_amount,
            'payment' => $financial->payment,
            'updated_at' => $financial->updated_at,
            'donations' => $donations,
        ]);
    }

    public function store(Request $request)
    {
          $employee = auth()->user();   
          $teamId = $employee->team_id;

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
         
            'amount' => 'required|numeric|min:0',
            'transfer_number' => 'nullable|string|max:255',
            'type' => 'required|in:حوالة,كاش',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $benefactor = Benefactor::firstOrCreate([
            'phone' => $validated['phone']
        ], [
            'name' => $validated['name']
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
        $image = $request->file('image');
        $imageName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
        $image->move(public_path('donations'), $imageName);
        $imagePath = 'donations/' . $imageName;
        }

      
        if (!$teamId) {
            return response()->json(['message' => 'الموظف لا يتبع لأي فريق'], 422);
        }


        $donation = DonorPayment::create([
            'benefactor_id' => $benefactor->id,
            'employee_id' => auth()->user()->id,
            'team_id' => $teamId,
            'amount' => $validated['amount'],
            'transfer_number' => $validated['transfer_number'],
            'type' => $validated['type'],
            'payment_date' => Carbon::now(),
            'image' => $imagePath,
        ]);

        $financial = Financial::firstOrCreate(
            ['team_id' => $teamId],
            ['total_amount' => 0, 'payment' => 0]
        );

        $financial->increment('total_amount', $validated['amount']);

        return response()->json([
            'message' => 'تم حفظ التبرع وتحديث المالية بنجاح',
            'donation' => $donation
        ]);
    }


    public function update(Request $request, $id)
    {
        $employee = auth()->user();
        $teamId = $employee->team_id;

        if (!$teamId) {
            return response()->json(['message' => 'الموظف لا يتبع لأي فريق'], 422);
        }

        $donation = DonorPayment::where('team_id', $teamId)->where('id', $id)->first();
        
        if (!$donation) {
            return response()->json(['message' => 'التبرع غير موجود أو لا يتبع لفريقك'], 404);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'amount' => 'required|numeric|min:0',
            'transfer_number' => 'nullable|string|max:255',
            'type' => 'required|in:حوالة,كاش',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $benefactor = Benefactor::firstOrCreate(
            ['phone' => $validated['phone']],
            ['name' => $validated['name']]
        );

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('donations'), $imageName);
            $donation->image = 'donations/' . $imageName;
        }

        $financial = Financial::firstOrCreate(
            ['team_id' => $teamId],
            ['total_amount' => 0, 'payment' => 0]
        );

        $financial->decrement('total_amount', $donation->amount);
        $financial->increment('total_amount', $validated['amount']);

        $donation->update([
            'benefactor_id' => $benefactor->id,
            'amount' => $validated['amount'],
            'transfer_number' => $validated['transfer_number'],
            'type' => $validated['type'],
        ]);

        return response()->json([
            'message' => 'تم تحديث التبرع وتحديث المالية بنجاح',
            'donation' => $donation
        ]);
    }




   



    public function destroy($id)
    {
        $employee = auth()->user();
        $teamId = $employee->team_id;

        if (!$teamId) {
            return response()->json(['message' => 'الموظف لا يتبع لأي فريق'], 422);
        }

        $donation = DonorPayment::where('team_id', $teamId)->where('id', $id)->first();

        if (!$donation) {
            return response()->json(['message' => 'التبرع غير موجود أو لا يتبع لفريقك'], 404);
        }

        $financial = Financial::where('team_id', $teamId)->first();
        if ($financial) {
            $financial->decrement('total_amount', $donation->amount);
        }

        if ($donation->image && file_exists(public_path($donation->image))) {
            unlink(public_path($donation->image));
        }

        $donation->delete();

        return response()->json(['message' => 'تم حذف التبرع وتحديث المالية بنجاح']);
    }


    public function getTeamFinancial($teamId)
    {
        $financial = Financial::where('team_id', $teamId)->firstOrFail();
        return new FinancialResource($financial);
    }

    public function getDonations($id)
    {
        $employee = auth()->user();

        if (!$employee->team_id) {
            return response()->json(['message' => 'الموظف لا يتبع لأي فريق'], 422);
        }

        $donation = DonorPayment::with('benefactor', 'volunter')
            ->where('team_id', $employee->team_id)
            ->where('id', $id)
            ->first();

        if (!$donation) {
            return response()->json(['message' => 'التبرع غير موجود أو لا يتبع لفريقك'], 404);
        }

        return response()->json([
            'id' => $donation->id,
            'amount' => $donation->amount,
            'type' => $donation->type,
            'status' => $donation->status,
            'payment_date' => $donation->payment_date,
            'transfer_number' => $donation->transfer_number,
            'image' => $donation->image,
            'benefactor_name' => $donation->benefactor->name ?? $donation->volunter->full_name,
            'benefactor_phone' => $donation->benefactor->phone ?? $donation->volunter->phone,
            'created_at' => $donation->created_at,
        ]);
            
        
        
    }

        public function updatestutas(Request $request, $id)
    {
        $employee = auth()->user();
        $teamId = $employee->team_id;

        if (!$teamId) {
            return response()->json(['message' => 'الموظف لا يتبع لأي فريق'], 422);
        }

        $donorpayment = DonorPayment::where('id', $id)->first();

        if (!$donorpayment) {
            return response()->json(['message' => 'التبرع غير موجود'], 404);
        }

        $donorpayment->update(['status' => $request->status]);

        return response()->json(['message' => 'تم تحديث حالة التبرع بنجاح']);
    }

}