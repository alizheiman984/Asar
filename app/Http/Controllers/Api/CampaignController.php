<?php

namespace App\Http\Controllers\Api;

use App\Models\Campaign;
use App\Models\ChatRoom;
use App\Models\Employee;
use App\Models\Financial;
use App\Models\Volunteer;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\CampaignResource;
use App\Http\Resources\VolunteerResource;
use Illuminate\Support\Facades\Notification;
use App\Notifications\CampaignFullNotification;
use Illuminate\Support\Facades\DB;

class CampaignController extends Controller
{
    public function index()
    {
        $campaigns = Campaign::with(['specialization', 'campaignType', 'team', 'employee'])
            ->where('status','pending')->get();

        return CampaignResource::collection($campaigns);
    }

        public function getcampaignsBySpecialty()
    {
        $user = auth()->user(); 

        $campaigns = Campaign::where('specialization_id', $user->specialization_id)
            ->with(['specialization', 'campaignType', 'team', 'employee'])
            ->get();

        return CampaignResource::collection($campaigns);
    }


    public function storeCampaign(Request $request)
    {
        $request->validate([
            'campaign_name' => 'required|string|max:255',
            'number_of_volunteer' => 'required|integer',
            'cost' => 'required|numeric',
            'address' => 'required|string',
            'from' => 'required|date_format:Y-m-d H:i:s',
            'to' => 'required|date_format:Y-m-d H:i:s',
            'points' => 'required|integer',
            'specialization_id' => 'nullable|exists:specializations,id',
            'campaign_type_id' => 'required|exists:campaign_types,id',
        ]);

        $employee = Auth::user();
        $team_id = $employee->team_id;

        DB::beginTransaction();
        try {
                $financial = Financial::where('team_id', $team_id)->first();

                if (!$financial) {
                    return response()->json(['message' => 'لا توجد بيانات مالية للفريق'], 404);
                }

                if ($request->cost > $financial->total_amount) {
                    return response()->json(['message' => 'الرصيد المتوفر لا يكفي لتغطية تكلفة الحملة'], 422);
                }

            $campaign = Campaign::create([
                'campaign_name' => $request->campaign_name,
                'number_of_volunteer' => $request->number_of_volunteer,
                'cost' => $request->cost,
                'address' => $request->address,
                'from' => $request->from,
                'to' => $request->to,
                'points' => $request->points,
                'status' => 'pending',
                'specialization_id' => $request->specialization_id,
                'campaign_type_id' => $request->campaign_type_id,
                'team_id' => $team_id,
                'employee_id' => $employee->id,
            ]);

            $financial = Financial::where('team_id', $team_id)->first();

              $financial = Financial::where('team_id', $team_id)->first();

        if ($financial) {
            $financial->total_amount -= $request->cost;
            $financial->payment = $request->cost;
            $financial->save();
        }

            DB::commit();

            return response()->json(['campaign' => $campaign], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);

        }
    }

    public function show($id)
    {
        try {
            $campaign = Campaign::find($id);
    
            if (!$campaign) {
                return response()->json(['message' => 'Campaign not found'], 404);
            }
    
        $campaign->load(['specialization', 'campaignType', 'team', 'employee', 'volunteers']);
        
        return new CampaignResource($campaign);
        } catch (\Exception $e) {
            return response()->json(['message' => 'An error occurred while deleting the campaign', 'error' => $e->getMessage()], 500);
        }
    }

        public function update(Request $request, $id)
    {
        $request->validate([
            'campaign_name' => 'nullable|string|max:255',
            'number_of_volunteer' => 'nullable|integer',
            'cost' => 'nullable|numeric',
            'address' => 'nullable|string',
            'from' => 'nullable|date_format:Y-m-d H:i:s',
            'to' => 'nullable|date_format:Y-m-d H:i:s',
            'points' => 'nullable|integer',
            'specialization_id' => 'nullable|exists:specializations,id',
            'campaign_type_id' => 'nullable|exists:campaign_types,id',
        ]);

        $campaign = Campaign::find($id);

        if (!$campaign) {
            return response()->json(['message' => 'الحملة غير موجودة'], 404);
        }

        $employee = auth()->user();
        $teamId = $employee->team_id;

        $financial = Financial::where('team_id', $teamId)->first();

        // التأكد من وجود financial
        if (!$financial) {
            return response()->json(['message' => 'لا توجد بيانات مالية لهذا الفريق'], 404);
        }

        DB::beginTransaction();

        try {
            // معالجة التغيير في التكلفة
            if ($request->has('cost')) {
                $newCost = floatval($request->cost);
                $currentCost = floatval($campaign->cost);

                if ($newCost > $currentCost) {
                    $diff = $newCost - $currentCost;

                    if ($diff > $financial->total_amount) {
                        return response()->json(['message' => 'الرصيد المتوفر لا يكفي لتغطية الفرق في التكلفة'], 422);
                    }

                    $financial->total_amount -= $diff;
                    $financial->payment += $diff;
                } elseif ($newCost < $currentCost) {
                    $refund = $currentCost - $newCost;

                    $financial->total_amount += $refund;
                    $financial->payment -= $refund;
                }

                $financial->save();
            }

            // تحديث الحقول غير الفارغة فقط
            $campaign->update(array_filter([
                'campaign_name' => $request->campaign_name,
                'number_of_volunteer' => $request->number_of_volunteer,
                'cost' => $request->cost,
                'address' => $request->address,
                'from' => $request->from,
                'to' => $request->to,
                'points' => $request->points,
                'specialization_id' => $request->specialization_id,
                'campaign_type_id' => $request->campaign_type_id,
            ], fn($value) => $value !== null && $value !== ''));

            DB::commit();

            return response()->json(['campaign' => $campaign], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'حدث خطأ', 'error' => $e->getMessage()], 500);
        }
    }

    
    

    public function destroy($id)
    {
        try {
            $campaign = Campaign::find($id);
    
            if (!$campaign) {
                return response()->json(['message' => 'Campaign not found'], 404);
            }
    
            $campaign->delete();
    
            return response()->json(['message' => 'Campaign deleted successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'An error occurred while deleting the campaign', 'error' => $e->getMessage()], 500);
        }
    }
    
    

    public function volunteers(Campaign $campaign)
    {
        return VolunteerResource::collection($campaign->volunteers()->paginate(10));
    }




    public function addVolunteer($id)
    {
        $volunteerId = auth()->id();

        $campaign = Campaign::find($id);

        if (!$campaign) {
            return response()->json(['message' => 'Campaign not found'], 404);
        }

        if ($campaign->volunteers()->where('volunteer_id', $volunteerId)->exists()) {
            return response()->json(['message' => 'You have already joined this campaign'], 422);
        }

        if ($campaign->volunteers()->count() >= $campaign->number_of_volunteer) {
            return response()->json(['message' => 'Campaign is already full'], 422);
        }

        $campaign->volunteers()->attach($volunteerId);

        if ($campaign->volunteers()->count() >= $campaign->number_of_volunteer) {
            $employee = $campaign->employee;

            if ($employee) {
                Notification::send($employee, new CampaignFullNotification($campaign));

                $chatRoom = ChatRoom::create([
                    'campaign_id' => $campaign->id,
                    'employee_id' => $campaign->employee_id,
                ]);

                foreach ($campaign->volunteers as $v) {
                    $chatRoom->volunteers()->attach($v->id, ['user_type' => 'App\\Models\\Volunteer']);
                }

                $chatRoom->volunteers()->attach($employee->id, ['user_type' => 'App\\Models\\Employee']);
            }
        }

        return response()->json(['message' => 'You have joined the campaign successfully']);
    }




    public function removeVolunteer($id)
    {
        $volunteerId = auth()->id();

        $campaign = Campaign::find($id);

        if (!$campaign) {
            return response()->json(['message' => 'Campaign not found'], 404);
        }

        // التحقق ما إذا كان المتطوع مسجلًا في الحملة
        if (! $campaign->volunteers()->where('volunteer_id', $volunteerId)->exists()) {
            return response()->json(['message' => 'You are not registered in this campaign'], 422);
        }

        $campaign->volunteers()->detach($volunteerId);

        return response()->json(['message' => 'You have been removed from the campaign successfully']);
    }

} 