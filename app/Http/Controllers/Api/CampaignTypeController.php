<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CampaignTypeResource;
use App\Models\CampaignType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CampaignTypeController extends Controller
{
 

    public function index()
    {
        $campaignTypes = CampaignType::all();
        return response()->json([
            'success' => true,
            'data' => $campaignTypes
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:campaign_types'
        ]);

        $campaignType = CampaignType::create([
            'name' => $request->input('name')
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Campaign type created successfully',
            'data' => $campaignType
        ], 201);
    }

    public function show(CampaignType $campaignType)
    {
        return response()->json([
            'success' => true,
            'data' => $campaignType
        ]);
    }

    public function update(Request $request, CampaignType $campaignType)
    {
        try {
            // Validate the request
            $validated = $request->validate([
                'name' => 'required|string|max:255|unique:campaign_types,name,' . $campaignType->id
            ]);

            // Update the campaign type
            $campaignType->update($validated);

            return new CampaignTypeResource($campaignType);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        }
    }

    public function destroy(CampaignType $campaignType)
    {
        $campaignType->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Campaign type deleted successfully'
        ]);
    }
} 