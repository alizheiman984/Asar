<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BusinessInformation;
use Illuminate\Http\Request;

class BusinessInformationController extends Controller
{
    public function index()
    {
        $businessInfo = BusinessInformation::all();
        return response()->json($businessInfo);
    }

    public function store(Request $request)
    {
        $request->validate([
            'volunteer_team_id' => 'required|exists:volunteer_teams,id',
            'team_name' => 'required|string|max:255',
            'license_number' => 'required|string|unique:business_informations',
            'phone' => 'required|string',
            'bank_account_number' => 'required|string',
        ]);

        $businessInfo = BusinessInformation::create($request->all());
        return response()->json($businessInfo, 201);
    }

    public function show(BusinessInformation $businessInfo)
    {
        return response()->json($businessInfo);
    }

    public function update(Request $request, BusinessInformation $businessInfo)
    {
        $request->validate([
            'team_name' => 'sometimes|string|max:255',
            'license_number' => 'sometimes|string|unique:business_informations,license_number,' . $businessInfo->id,
            'phone' => 'sometimes|string',
            'bank_account_number' => 'sometimes|string',
        ]);

        $businessInfo->update($request->all());
        return response()->json($businessInfo);
    }

    public function destroy(BusinessInformation $businessInfo)
    {
        $businessInfo->delete();
        return response()->json(null, 204);
    }
} 