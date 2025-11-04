<?php

namespace app\Http\Controllers\Api;

use app\Http\Controllers\Controller;
use app\Models\VolunteerTeam;
use app\Http\Resources\TeamResource;
use app\Http\Resources\CampaignResource;
use app\Http\Resources\EmployeeResource;
use app\Http\Resources\RequestResource;
use app\Http\Resources\DonorPaymentResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class TeamController extends Controller
{
    public function index()
    {
        $teams = VolunteerTeam::paginate(10);
        return TeamResource::collection($teams);
    }

    public function store(Request $request)
    {
        $request->validate([
            'full_name' => 'required|string|max:255',
            'team_name' => 'required|string|max:255',
            'license_number' => 'required|string|unique:volunteer_teams',
            'reg_image' => 'nullable|string',
            'phone' => 'required|string|max:255',
            'bank_account_number' => 'required|string|unique:volunteer_teams',
            'email' => 'required|string|email|max:255|unique:volunteer_teams',
            'password' => 'required|string|min:8',
        ]);

        $team = VolunteerTeam::create([
            'full_name' => $request->full_name,
            'team_name' => $request->team_name,
            'license_number' => $request->license_number,
            'reg_image' => $request->reg_image,
            'phone' => $request->phone,
            'bank_account_number' => $request->bank_account_number,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        return new TeamResource($team);
    }

    public function show(VolunteerTeam $team)
    {
        $team->load(['campaigns', 'employees', 'requests', 'donorPayments']);
        return new TeamResource($team);
    }

    public function update(Request $request, VolunteerTeam $team)
    {
        $request->validate([
            'full_name' => 'sometimes|string|max:255',
            'team_name' => 'sometimes|string|max:255',
            'license_number' => 'sometimes|string|unique:volunteer_teams,license_number,' . $team->id,
            'reg_image' => 'nullable|string',
            'phone' => 'sometimes|string|max:255',
            'bank_account_number' => 'sometimes|string|unique:volunteer_teams,bank_account_number,' . $team->id,
            'email' => 'sometimes|string|email|max:255|unique:volunteer_teams,email,' . $team->id,
            'password' => 'sometimes|string|min:8',
        ]);

        if ($request->has('password')) {
            $request->merge([
                'password' => Hash::make($request->password)
            ]);
        }

        $team->update($request->all());

        return new TeamResource($team);
    }

    public function destroy(VolunteerTeam $team)
    {
        $team->delete();
        return response()->json(['message' => 'Team deleted successfully']);
    }

    public function campaigns(VolunteerTeam $team)
    {
        return CampaignResource::collection($team->campaigns()->paginate(10));
    }

    public function employees(VolunteerTeam $team)
    {
        return EmployeeResource::collection($team->employees()->paginate(10));
    }

    public function requests(VolunteerTeam $team)
    {
        return RequestResource::collection($team->requests()->paginate(10));
    }

    public function donorPayments(VolunteerTeam $team)
    {
        return DonorPaymentResource::collection($team->donorPayments()->paginate(10));
    }
} 