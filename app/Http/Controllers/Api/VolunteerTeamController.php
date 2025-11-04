<?php

namespace App\Http\Controllers\Api;

use App\Models\Employee;
use App\Models\Financial;
use App\Models\Volunteer;
use App\Models\Campaign;
use Illuminate\Support\Str;
use App\Http\Controllers\Controller;
use App\Models\VolunteerTeam;
use Illuminate\Http\Request;
use App\Models\BusinessInformation;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class VolunteerTeamController extends Controller
{
    public function index()
    {
        $teams = VolunteerTeam::with(['businessInformation', 'employees', 'financial'])->get();
        return response()->json($teams);
    }

    public function store(Request $request)
    {
        $request->validate([
            'full_name' => 'required|string|max:255',
            'national_id' => 'required|string|unique:volunteer_teams',
            'gender' => 'required|in:male,female',
            'nationality' => 'required|string',
            'address' => 'required|string',
            'date_of_birth' => 'required|date',
            'email' => 'required|email|unique:volunteer_teams',
            'password' => 'required|string|min:8',
            'status' => 'required|in:active,inactive',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        // Handle image upload if present
        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('team_images', 'public');
        }

        $team = VolunteerTeam::create([
            'full_name' => $request->full_name,
            'national_id' => $request->national_id,
            'gender' => $request->gender,
            'nationality' => $request->nationality,
            'address' => $request->address,
            'date_of_birth' => $request->date_of_birth,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'status' => $request->status,
            'image' => $imagePath,
        ]);

        // Create initial financial record
        $team->financial()->create(['total_amount' => 0]);

        return response()->json($team, 201);
    }

    public function show($id)
    {
        $team = VolunteerTeam::find($id);
        
        if (!$team) {
            return response()->json([
                'success' => false,
                'message' => 'Team not found'
            ], 404);
        }

        $team->load([
            'businessInformation',
            'employees',
            'financial',
            'campaigns',
            'requests',
            'donorPayments',
            'contracts'
        ]);

        return response()->json([
            'success' => true,
            'data' => $team
        ]);
    }

    public function update(Request $request, VolunteerTeam $team)
    {
        $request->validate([
            'full_name' => 'sometimes|string|max:255',
            'national_id' => 'sometimes|string|unique:volunteer_teams,national_id,' . $team->id,
            'gender' => 'sometimes|in:male,female',
            'nationality' => 'sometimes|string',
            'address' => 'sometimes|string',
            'date_of_birth' => 'sometimes|date',
            'email' => 'sometimes|email|unique:volunteer_teams,email,' . $team->id,
            'password' => 'sometimes|string|min:8',
            'status' => 'sometimes|in:active,inactive',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        // Handle image update
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($team->image) {
                Storage::disk('public')->delete($team->image);
            }
            $imagePath = $request->file('image')->store('team_images', 'public');
            $team->image = $imagePath;
        }

        $data = $request->except('image');
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        $team->update($data);
        return response()->json($team);
    }

    public function destroy(VolunteerTeam $team)
    {
        // Delete associated image if exists
        if ($team->image) {
            Storage::disk('public')->delete($team->image);
        }

        $team->delete();
        return response()->json(null, 204);
    }

    public function getMyEmployees()
    {
        $team = VolunteerTeam::find(Auth::id());

        if (!$team) {
            return response()->json([
                'success' => false,
                'message' => 'Team not found'
            ], 404);
        }

        $employees = $team->employees()->with('specialization')->get();

        return response()->json([
            'success' => true,
            'data' => $employees
        ]);
    }

  public function getTeamStatistics()
    {
        $team = VolunteerTeam::find(Auth::id());
        
        if (!$team) {
            return response()->json([
                'success' => false,
                'message' => 'Team not found'
            ], 404);
        }

        $team->load(['financial', 'employees', 'contracts', 'campaigns']);

        $totalCampaigns = $team->campaigns->count();
        $completedCampaigns = $team->campaigns->where('status', 'done')->count();
        $uncompletedCampaigns = $team->campaigns->where('status', '!=', 'done')->count();

        $total = $completedCampaigns + $uncompletedCampaigns;

        $completedPercentage = $total > 0 ? ($completedCampaigns / $total) * 100 : 0;
        $uncompletedPercentage = $total > 0 ? ($uncompletedCampaigns / $total) * 100 : 0;

        return response()->json([
            'success' => true,
            'data' => [
                'finance' => [
                    'total_price' => $team->financial ? $team->financial->total_amount : 0,
                    'payment' => $team->financial ? $team->financial->payment : 0
                ],
                'employees' => [
                    'total_count' => $team->employees->count()
                ],
                'contracts' => [
                    'total_count' => $team->contracts->count()
                ],
                'campaigns' => [
                    'total_count' => $totalCampaigns,
                    'completed_percentage' => round($completedPercentage, 2),
                    'uncompleted_percentage' => round($uncompletedPercentage, 2),
                    'completed_count' => $completedCampaigns,
                    'uncompleted_count' => $uncompletedCampaigns
                ]
            ]
        ]);
    }


    public function getTeamCampaigns()
    {
        $team = Auth::user(); 
        if (!$team) {
            return response()->json([
                'success' => false,
                'message' => 'Team not found'
            ], 404);
        }

        $completedCampaigns = Campaign::where('team_id', $team->id)
            ->whereIn('status', ['done', 'rejected'])
            ->with('campaignType')
            ->get()
            ->map(function ($campaign) {
                return [
                    'id' => $campaign->id,
                    'name' => $campaign->campaign_name,
                    'status'=> $campaign->status,
                    'location' => $campaign->address,
                    'date' => $campaign->from,
                    'category' => optional($campaign->campaignType)->name,
                    'cost' => $campaign->cost,
                ];
            });

        $uncompletedCampaigns = Campaign::where('team_id', $team->id)
            ->whereNotIn('status', ['done', 'rejected'])
            ->with('campaignType')
            ->get()
            ->map(function ($campaign) {
                return [
                    'id' => $campaign->id,
                    'name' => $campaign->campaign_name,
                    'status'=> $campaign->status,
                    'location' => $campaign->address,
                    'date' => $campaign->from,
                    'category' => optional($campaign->campaignType)->name,
                    'cost' => $campaign->cost,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => [
                'completed_campaigns' => $completedCampaigns,
                'uncompleted_campaigns' => $uncompletedCampaigns
            ]
        ]);
    }

    public function getTeamContracts()
    {
        $team = VolunteerTeam::find(Auth::id());
        
        if (!$team) {
            return response()->json([
                'success' => false,
                'message' => 'Team not found'
            ], 404);
        }

        $contracts = $team->contracts()->get()->map(function($contract) {
            return [
                'id' => $contract->id,
                'company_name' => $contract->company_name,
                'content' => $contract->content,
                'contract_date' => $contract->contract_date,
                'image' => $contract->image,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $contracts
        ]);
    }

    public function storeContract(Request $request)
    {
        $team = VolunteerTeam::find(Auth::id());
        
        if (!$team) {
            return response()->json([
                'success' => false,
                'message' => 'Team not found'
            ], 404);
        }

        $request->validate([
            'company_name' => 'required|string|max:255',
            'content' => 'required|string',
            'contract_date' => 'required|date',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $contractData = $request->except('image');

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = uniqid() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('uploads/contracts'), $imageName);
            $contractData['image'] = 'uploads/contracts/' . $imageName;
        }

        $contract = $team->contracts()->create($contractData);

        return response()->json([
            'success' => true,
            'message' => 'Contract created successfully',
            'data' => $contract
        ], 201);
    }

    public function updateContract(Request $request, $contractId)
    {
        $team = VolunteerTeam::find(Auth::id());
        
        if (!$team) {
            return response()->json([
                'success' => false,
                'message' => 'Team not found'
            ], 404);
        }

        $contract = $team->contracts()->find($contractId);
        if (!$contract) {
            return response()->json([
                'success' => false,
                'message' => 'Contract not found'
            ], 404);
        }
        
        $request->validate([
            'company_name' => 'sometimes|string|max:255',
            'content' => 'sometimes|string',
            'contract_date' => 'sometimes|date',
            // 'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $contractData = $request->except('image');

        if ($request->hasFile('image')) {
            if ($contract->image && file_exists(public_path($contract->image))) {
                unlink(public_path($contract->image));
            }
            
            $image = $request->file('image');
            $imageName = uniqid() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('uploads/contracts'), $imageName);
            $contractData['image'] = 'uploads/contracts/' . $imageName;
        }

        $contract->update($contractData);

        return response()->json([
            'success' => true,
            'message' => 'Contract updated successfully',
            'data' => $contract
        ]);
    }

    public function deleteContract($contractId)
    {
        $team = VolunteerTeam::find(Auth::id());
        
        if (!$team) {
            return response()->json([
                'success' => false,
                'message' => 'Team not found'
            ], 404);
        }

        $contract = $team->contracts()->find($contractId);
        
        if (!$contract) {
            return response()->json([
                'success' => false,
                'message' => 'Contract not found'
            ], 404);
        }

        // Delete image if exists
        if ($contract->image) {
            Storage::disk('public')->delete($contract->image);
        }

        $contract->delete();

        return response()->json([
            'success' => true,
            'message' => 'Contract deleted successfully'
        ]);
    }

    public function showContract($contractId)
    {
        $team = VolunteerTeam::find(Auth::id());
        
        if (!$team) {
            return response()->json([
                'success' => false,
                'message' => 'Team not found'
            ], 404);
        }

        $contract = $team->contracts()->find($contractId);
        
        if (!$contract) {
            return response()->json([
                'success' => false,
                'message' => 'Contract not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $contract
        ]);
    }

    public function storeEmployee(Request $request)
    {
        $request->validate([
            'full_name' => 'required|string',
            'email' => 'required|email|unique:employees',
            'password' => 'required|min:6',
            'national_number' => 'nullable|unique:employees',
            'position' => 'required|in:مشرف,موظف مالي',
            'phone' => 'required|string',
            'address' => 'nullable|string',
            'date_accession' => 'required|date_format:Y-m-d',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048', 
            'team_id' => 'exists:volunteer_teams,id',
            'specialization_id' => 'nullable|exists:specializations,id',
        ]);
    
        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->move(public_path('uploads/employee'), uniqid() . '.' . $request->file('image')->getClientOriginalExtension());
            $imageRelativePath = 'uploads/employee/' . basename($imagePath);

         
        }
    
        $employee = Employee::create([
            'full_name' => $request->full_name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'national_number' => $request->national_number,
            'position' => $request->position,
            'phone' => $request->phone,
            'address' => $request->address,
            'date_accession' => $request->date_accession,
            'image' => $imageRelativePath, 
            'team_id' => auth()->user()->id,
            'specialization_id' => $request->specialization_id,
        ]);
    
        return response()->json(['employee' => $employee], 201);
    }

    public function LoginEmployee(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);
    
        $employee = Employee::where('email', $request->email)->first();
    
        if (!$employee || !Hash::check($request->password, $employee->password)) {
            return response()->json([
                'message' => 'The provided credentials are incorrect.',
            ], 403);
        }
    


        $token = $employee->createToken('auth_token')->plainTextToken;
    
        return response()->json([
            'message' => 'employee logged in successfully',
            'Employee' => $employee,
            'token' => $token,
        ]);
    }


} 