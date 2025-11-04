<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Point;
use App\Models\Volunteer;
use App\Models\Employee;
use App\Models\Campaign;
use Illuminate\Http\Request;

class PointController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $points = Point::with(['volunteer', 'employee', 'campaign'])->get();
        return response()->json($points);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'volunteer_id' => 'required_without:employee_id|exists:volunteers,id',
            'employee_id' => 'required_without:volunteer_id|exists:employees,id',
            'campaign_id' => 'required|exists:campaigns,id',
            'magnitude_change' => 'required|integer',
            'reason_of_change' => 'required|string|max:255',
            'date_of_change' => 'required|date',
        ]);

        // Ensure only one of volunteer_id or employee_id is provided
        if ($request->has('volunteer_id') && $request->has('employee_id')) {
            return response()->json(['message' => 'Only one of volunteer_id or employee_id can be provided'], 422);
        }

        $point = Point::create($request->all());

        // Update the total points for the volunteer or employee
        if ($request->has('volunteer_id')) {
            $volunteer = Volunteer::findOrFail($request->volunteer_id);
            $volunteer->total_points += $request->magnitude_change;
            $volunteer->save();
        } else {
            $employee = Employee::findOrFail($request->employee_id);
            $employee->total_points += $request->magnitude_change;
            $employee->save();
        }

        return response()->json($point, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Point $point)
    {
        $point->load(['volunteer', 'employee', 'campaign']);
        return response()->json($point);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Point $point)
    {
        $request->validate([
            'volunteer_id' => 'sometimes|exists:volunteers,id',
            'employee_id' => 'sometimes|exists:employees,id',
            'campaign_id' => 'sometimes|exists:campaigns,id',
            'magnitude_change' => 'sometimes|integer',
            'reason_of_change' => 'sometimes|string|max:255',
            'date_of_change' => 'sometimes|date',
        ]);

        // Store old values for point adjustment
        $oldMagnitude = $point->magnitude_change;
        $oldVolunteerId = $point->volunteer_id;
        $oldEmployeeId = $point->employee_id;

        $point->update($request->all());

        // If magnitude changed, update the total points
        if ($request->has('magnitude_change')) {
            $difference = $request->magnitude_change - $oldMagnitude;

            if ($oldVolunteerId) {
                $volunteer = Volunteer::findOrFail($oldVolunteerId);
                $volunteer->total_points += $difference;
                $volunteer->save();
            } else {
                $employee = Employee::findOrFail($oldEmployeeId);
                $employee->total_points += $difference;
                $employee->save();
            }
        }

        return response()->json($point);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Point $point)
    {
        // Update the total points before deleting
        if ($point->volunteer_id) {
            $volunteer = Volunteer::findOrFail($point->volunteer_id);
            $volunteer->total_points -= $point->magnitude_change;
            $volunteer->save();
        } else {
            $employee = Employee::findOrFail($point->employee_id);
            $employee->total_points -= $point->magnitude_change;
            $employee->save();
        }

        $point->delete();
        return response()->json(null, 204);
    }

    public function getVolunteerPoints(Volunteer $volunteer)
    {
        $points = Point::where('volunteer_id', $volunteer->id)
            ->with('campaign')
            ->orderBy('date_of_change', 'desc')
            ->get();

        return response()->json([
            'total_points' => $volunteer->total_points,
            'points_history' => $points
        ]);
    }

    public function getEmployeePoints(Employee $employee)
    {
        $points = Point::where('employee_id', $employee->id)
            ->with('campaign')
            ->orderBy('date_of_change', 'desc')
            ->get();

        return response()->json([
            'total_points' => $employee->total_points,
            'points_history' => $points
        ]);
    }

    public function getCampaignPoints(Campaign $campaign)
    {
        $points = Point::where('campaign_id', $campaign->id)
            ->with(['volunteer', 'employee'])
            ->orderBy('date_of_change', 'desc')
            ->get();

        return response()->json($points);
    }
}
