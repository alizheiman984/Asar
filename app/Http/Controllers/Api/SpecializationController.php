<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Specialization;
use Illuminate\Http\Request;

class SpecializationController extends Controller
{
    public function index()
    {
        $specializations = Specialization::get();
        return response()->json($specializations);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:specializations',
        ]);

        $specialization = Specialization::create($request->all());
        return response()->json($specialization, 201);
    }

    public function show(Specialization $specialization)
    {
        $specialization->load(['volunteers', 'employees', 'campaigns']);
        return response()->json($specialization);
    }

    public function update(Request $request, Specialization $specialization)
    {
        $request->validate([
            'name' => 'sometimes|string|max:255|unique:specializations,name,' . $specialization->id,
        ]);

        $specialization->update($request->all());
        return response()->json($specialization);
    }

    public function destroy(Specialization $specialization)
    {
        $specialization->delete();
        return response()->json(null, 204);
    }
} 