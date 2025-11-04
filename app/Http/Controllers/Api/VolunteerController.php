<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Volunteer;
use App\Http\Resources\VolunteerResource;
use App\Http\Resources\CampaignResource;
use App\Http\Resources\PointResource;
use App\Http\Resources\AttendanceResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class VolunteerController extends Controller
{
    public function index()
    {
        $volunteers = Volunteer::all();
        return response()->json($volunteers);
    }

    public function store(Request $request)
    {
        $request->validate([
            'full_name' => 'required|string|max:255',
            'national_id' => 'required|string|unique:volunteers',
            'nationality' => 'required|string',
            'phone' => 'required|string',
            'email' => 'required|email|unique:volunteers',
            'password' => 'required|string|min:8',
            'specialization_id' => 'required|exists:specializations,id',
        ]);

        $volunteer = Volunteer::create([
            'full_name' => $request->full_name,
            'national_id' => $request->national_id,
            'nationality' => $request->nationality,
            'phone' => $request->phone,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'specialization_id' => $request->specialization_id,
        ]);

        return response()->json($volunteer, 201);
    }

    public function show(Volunteer $volunteer)
    {
        return response()->json($volunteer);
    }

    public function update(Request $request, Volunteer $volunteer)
    {
        $request->validate([
            'full_name' => 'sometimes|string|max:255',
            'national_id' => 'sometimes|string|unique:volunteers,national_id,' . $volunteer->id,
            'nationality' => 'sometimes|string',
            'phone' => 'sometimes|string',
            'email' => 'sometimes|email|unique:volunteers,email,' . $volunteer->id,
            'password' => 'sometimes|string|min:8',
            'specialization_id' => 'sometimes|exists:specializations,id',
        ]);

        $data = $request->all();
        if (isset($data['password'])) {
            $data['password'] = bcrypt($data['password']);
        }

        $volunteer->update($data);
        return response()->json($volunteer);
    }

    public function destroy(Volunteer $volunteer)
    {
        $volunteer->delete();
        return response()->json(null, 204);
    }

    public function campaigns(Volunteer $volunteer)
    {
        return CampaignResource::collection($volunteer->campaigns()->paginate(10));
    }

    public function points(Volunteer $volunteer)
    {
        return PointResource::collection($volunteer->points()->paginate(10));
    }

    public function attendances(Volunteer $volunteer)
    {
        return AttendanceResource::collection($volunteer->attendances()->paginate(10));
    }
} 