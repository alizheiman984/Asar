<?php

namespace App\Http\Controllers\Api;

use App\Models\Team;
use App\Models\Campaign;
use App\Models\Employee;
use App\Models\Financial;
use App\Models\Volunteer;
use App\Models\DonorPayment;
use Illuminate\Http\Request;
use App\Models\VolunteerTeam;
use App\Mail\TeamApprovedMail;
use App\Mail\TeamRejectedMail;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Resources\TeamResource;
use Illuminate\Support\Facades\Mail;
use App\Http\Resources\VolunteerResource;


class GovernmentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    // Teams Tab
    public function getTeams()
    {
        $teams = VolunteerTeam::where('status', 'accepted')
                                ->where('type', 'volunteer teams')
                                ->get()
            ->map(function ($team) {
                 return [
                    'id' => $team->id,
                    'name' => $team->full_name,
                    'phone' => $team->phone,
                    'team_name' => $team->businessInformation->team_name,
                    'address' => $team->businessInformation->address,
                    'created_at' => $team->created_at,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $teams
        ]);
    }


      public function getCharities()
    {
        $teams = VolunteerTeam::where('status', 'accepted')
                                ->where('type', 'charities')
                                ->get()
            ->map(function ($team) {
                 return [
                    'id' => $team->id,
                    'name' => $team->full_name,
                    'phone' => $team->phone,
                    'team_name' => $team->businessInformation->team_name,
                    'address' => $team->businessInformation->address,
                    'created_at' => $team->created_at,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $teams
        ]);
    }

    // Manager Requests Tab
    public function getPendingTeams()
    {
        $teams = VolunteerTeam::where('status', 'pending')->get();

        return response()->json([
            'success' => true,
            'data' => $teams
        ]);
    }

    public function approveTeam(VolunteerTeam $team)
    {
        $team->update(['status' => 'accepted']);

         if ($team->email) {
            Mail::to($team->email)->send(new TeamApprovedMail($team));
        }

        return response()->json([
            'success' => true,
            'message' => 'Team approved successfully'
        ]);
    }

    public function rejectTeam(VolunteerTeam $team , Request $request)
    {
        $request->validate([
            'reason' => 'required|string|max:255',
        ]);
        $team->update(['status' => 'rejected']);

         if ($team->email) {
        Mail::to($team->email)->send(new TeamRejectedMail($team, $request->reason));
        }
        return response()->json([
            'success' => true,
            'message' => 'Team rejected successfully'
        ]);
    }

    // Volunteers Tab
    public function getVolunteers()
    {
        $volunteers = Volunteer::with(['team', 'campaigns'])
            ->get()
            ->map(function ($volunteer) {
                return [
                    'id' => $volunteer->id,
                    'name' => $volunteer->name,
                    'email' => $volunteer->email,
                    'team' => $volunteer->team ? $volunteer->team->team_name : null,
                    'campaign_count' => $volunteer->campaigns->count(),
                    'created_at' => $volunteer->created_at,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $volunteers
        ]);
    }

    public function getTeamDetails(VolunteerTeam $team)
    {
        return response()->json([
            'success' => true,
            'data' => [
                'id' => $team->id,
                'full_name' => $team->full_name,
                'team_name' => $team->businessInformation->team_name,

                'license_number' => $team->businessInformation->license_number,
                'phone' => $team->phone,
                'bank_account_number' => $team->businessInformation->bank_account_number,
                'email' => $team->email,
                'address' => $team->businessInformation->address,
                'status' => $team->status,
                'total_finance' => optional($team->financial)->total_amount ?? 0,

                'total_campaigns' => $team->campaigns->count(),
                'total_employees' => $team->employees->count(),

                'total_campaigns_rejected' => $team->campaigns->where('status','rejected')->count(),
                'total_campaigns_done' => $team->campaigns->whereIn('status',['done','pending'])->count(),
                'created_at' => $team->created_at,
            ]
        ]);
    }

     public function getListTeamFinance($id)
    {
        $payments = DonorPayment::where('team_id', $id)
            ->with(['benefactor', 'volunter'])  
            ->get()
            ->map(function ($payment) {
                return [
                    'id' => $payment->id,
                    'name' => optional($payment->benefactor)->name ?? optional($payment->volunter)->full_name ,
                    'details' => $payment->type,
                    'date' => $payment->payment_date,
                    'cost' => $payment->amount,
                ];
            });
    
        if ($payments->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No payments found'
            ]);
        }
    
        return response()->json([
            'success' => true,
            'data' => $payments
        ]);
    }



    public function getTotalTeamFinance(VolunteerTeam $team)
    {
        $finances = Financial::where('team_id', $team->id)
            ->get()
            ->map(function ($finance) {
                return [
                    'id' => $finance->id,
                    'total_amount' => $finance->total_amount,
                    'payment' => $finance->payment,
                    'date' => $finance->created_at,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $finances
        ]);
    }

    public function getTeamCampaigns(VolunteerTeam $team)
    {
        $ongoingCampaigns = Campaign::where('team_id', $team->id)
            ->where('status', 'pending')
            ->with('campaignType')
            ->get()
            ->map(function ($campaign) {
                return [
                    'id' => $campaign->id,
                    'name' => $campaign->campaign_name,
                    'location' => $campaign->address,
                    'status'=> $campaign->status,
                    'date' => $campaign->from,
                    'category' => $campaign->campaignType->name,
                    'cost' => $campaign->cost,
                    // 'supplies' => $campaign->description,
                ];
            });

        $completedCampaigns = Campaign::where('team_id', $team->id)
            ->whereIn('status',['done','rejected'])
            
            ->with('campaignType')
            ->get()
            ->map(function ($campaign) {
                return [
                    'id' => $campaign->id,
                    'name' => $campaign->campaign_name,
                    'location' => $campaign->address,
                    'status'=> $campaign->status,
                    'date' => $campaign->from,
                    'category' => $campaign->campaignType->name,
                    'cost' => $campaign->cost,
                    
                    // 'supplies' => $campaign->description,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => [
                'ongoing_campaigns' => $ongoingCampaigns,
                'completed_campaigns' => $completedCampaigns
            ]
        ]);
    }

    public function getTeamEmployees(VolunteerTeam $team)
    {
        $employees = Employee::where('team_id', $team->id)
            ->with('specialization')
            ->get()
            ->map(function ($employee) {
                return [
                    'id' => $employee->id,
                    'image' => $employee->image,
                    'name' => $employee->full_name,
                    'phone' => $employee->phone,
                    'email' => $employee->email,
                    'address' => $employee->address,
                    'position' => $employee->position,
                    'specialization' => $employee->specialization->name,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $employees
        ]);
    }

    public function getAllVolunteers()
    {
        $volunteers = Volunteer::with(['specialization'])
            ->get()
            ->map(function ($volunteer) {
                return [
                    'id' => $volunteer->id,
                    'image' => $volunteer->image,
                    'name' => $volunteer->full_name,
                    'phone' => $volunteer->phone,
                    'email' => $volunteer->email,
                    'nationality' => $volunteer->nationality,
                    'specialization' => $volunteer->specialization ? $volunteer->specialization->name : null,
                    'total_points' => $volunteer->total_points,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $volunteers
        ]);
    }
} 