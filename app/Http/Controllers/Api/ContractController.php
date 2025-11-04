<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Contract;
use App\Http\Resources\ContractResource;
use Illuminate\Http\Request;

class ContractController extends Controller
{
    public function index()
    {
        $contracts = Contract::with(['team', 'benefactor'])->paginate(10);
        return ContractResource::collection($contracts);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'amount' => 'required|numeric|min:0',
            'status' => 'required|in:pending,active,completed,cancelled',
            'team_id' => 'required|exists:volunteer_teams,id',
            'benefactor_id' => 'required|exists:benefactors,id',
        ]);

        $contract = Contract::create($request->all());
        return new ContractResource($contract);
    }

    public function show(Contract $contract)
    {
        return new ContractResource($contract->load(['team', 'benefactor']));
    }

    public function update(Request $request, Contract $contract)
    {
        $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'start_date' => 'sometimes|date',
            'end_date' => 'sometimes|date|after:start_date',
            'amount' => 'sometimes|numeric|min:0',
            'status' => 'sometimes|in:pending,active,completed,cancelled',
            'team_id' => 'sometimes|exists:volunteer_teams,id',
            'benefactor_id' => 'sometimes|exists:benefactors,id',
        ]);

        $contract->update($request->all());
        return new ContractResource($contract);
    }

    public function destroy(Contract $contract)
    {
        $contract->delete();
        return response()->json(['message' => 'Contract deleted successfully']);
    }

    public function getTeamContracts($teamId)
    {
        $contracts = Contract::where('team_id', $teamId)
            ->with(['benefactor'])
            ->orderBy('created_at', 'desc')
            ->get();

        return ContractResource::collection($contracts);
    }

    public function getBenefactorContracts($benefactorId)
    {
        $contracts = Contract::where('benefactor_id', $benefactorId)
            ->with(['team'])
            ->orderBy('created_at', 'desc')
            ->get();

        return ContractResource::collection($contracts);
    }

    public function updateStatus(Request $request, Contract $contract)
    {
        $request->validate([
            'status' => 'required|in:pending,active,completed,cancelled'
        ]);

        $contract->update(['status' => $request->status]);
        return new ContractResource($contract);
    }
} 