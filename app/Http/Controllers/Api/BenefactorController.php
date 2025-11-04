<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Benefactor;
use App\Http\Resources\BenefactorResource;
use Illuminate\Http\Request;

class BenefactorController extends Controller
{
    public function index()
    {
        $benefactors = Benefactor::paginate(10);
        return BenefactorResource::collection($benefactors);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'national_id' => 'required|string|unique:benefactors',
            'status' => 'required|in:active,inactive',
            'team_id' => 'required|exists:volunteer_teams,id',
        ]);

        $benefactor = Benefactor::create($request->all());
        return new BenefactorResource($benefactor);
    }

    public function show(Benefactor $benefactor)
    {
        return new BenefactorResource($benefactor);
    }

    public function update(Request $request, Benefactor $benefactor)
    {
        $request->validate([
            'name' => 'sometimes|string|max:255',
            'phone' => 'sometimes|string|max:255',
            'address' => 'sometimes|string|max:255',
            'national_id' => 'sometimes|string|unique:benefactors,national_id,' . $benefactor->id,
            'status' => 'sometimes|in:active,inactive',
            'team_id' => 'sometimes|exists:volunteer_teams,id',
        ]);

        $benefactor->update($request->all());
        return new BenefactorResource($benefactor);
    }

    public function destroy(Benefactor $benefactor)
    {
        $benefactor->delete();
        return response()->json(['message' => 'Benefactor deleted successfully']);
    }
} 