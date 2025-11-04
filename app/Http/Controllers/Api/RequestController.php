<?php

namespace App\Http\Controllers\Api;

use App\Models\Volunteer;
use Illuminate\Http\Request;
use App\Models\VolunteerTeam;
use App\Http\Controllers\Controller;
use App\Http\Resources\RequestResource;
use App\Models\Request as VolunteerRequest;

class RequestController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $auth = auth()->user();
        $Request = VolunteerRequest::with(['volunteer', 'team'])->where('volunteer_id', $auth->id)
        ->get();
        return RequestResource::collection($Request);

        
    }
    

    /**
     * Store a newly created resource in storage.
     */
   public function store(Request $request)
    {
        $validated = $request->validate([
            'content' => 'required|string',
            'type' => 'required|in:complaints,suggestion',
            'team_id' => 'nullable|exists:volunteer_teams,id',
        ]);
    
        // إذا النوع suggestion، يتم تجاهل team_id وتعيينها إلى null
        if ($validated['type'] === 'suggestion') {
            $validated['team_id'] = null;
        }
    
        // تعيين المستخدم الحالي تلقائيًا
        $validated['volunteer_id'] = auth()->id();
    
        $requestModel = VolunteerRequest::create($validated);
    
        return response()->json([
            'message' => 'Request created successfully',
            'data' => $requestModel
        ]);
    }

    

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $volunteerRequest = VolunteerRequest::with(['team', 'volunteer'])->find($id);
    
        if (!$volunteerRequest) {
            return response()->json([
                'message' => 'Request not found'
            ], 404);
        }
    
        return response()->json($volunteerRequest);
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $volunteerRequest = VolunteerRequest::find($id);
    
        if (!$volunteerRequest) {
            return response()->json([
                'message' => 'Request not found'
            ], 404);
        }
    
        $validated = $request->validate([
            'content' => 'required|string',
            'type' => 'required|in:complaints,suggestion',
            'team_id' => 'nullable|exists:volunteer_teams,id',
        ]);
    
        if ($validated['type'] === 'suggestion') {
            $validated['team_id'] = null;
        }
    
        $volunteerRequest->update($validated);
    
        return response()->json([
            'message' => 'Request updated successfully',
            'data' => $volunteerRequest
        ]);
    }

    
    

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $requestModel = VolunteerRequest::findOrFail($id);
        $requestModel->delete();

        return response()->json(['message' => 'Request deleted successfully']);
    }





    public function indexForEmployee()
    {
        $employee = auth()->user();

        if (!$employee || !$employee->team_id) {
            return response()->json([
                'message' => 'No team assigned to this employee'
            ], 403);
        }

        $teamId = $employee->team_id;

        $requests = VolunteerRequest::with('volunteer')->where(function ($query) use ($teamId) {
            $query->where('type', 'suggestion') 
                ->orWhere(function ($q) use ($teamId) {
                    $q->where('type', 'complaints')
                        ->where('team_id', $teamId);
                });
        })->latest()->get();

        return response()->json([
            'data' => $requests
        ]);
    }


    public function getTeamRequests(VolunteerTeam $team)
    {
        $requests = VolunteerRequest::where('team_id', $team->id)
            ->with('volunteer')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($requests);
    }

    public function getVolunteerRequests(Volunteer $volunteer)
    {
        $requests = VolunteerRequest::where('volunteer_id', $volunteer->id)
            ->with('team')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($requests);
    }

    public function getRequestsByStatus($status)
    {
        $validStatuses = ['pending', 'App\\roved', 'rejected'];
        
        if (!in_array($status, $validStatuses)) {
            return response()->json(['message' => 'Invalid status'], 422);
        }

        $requests = VolunteerRequest::where('status', $status)
            ->with(['team', 'volunteer'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($requests);
    }

    public function getRequestsByType($type)
    {
        $validTypes = ['join_team', 'leave_team', 'change_team', 'other'];
        
        if (!in_array($type, $validTypes)) {
            return response()->json(['message' => 'Invalid request type'], 422);
        }

        $requests = VolunteerRequest::where('type', $type)
            ->with(['team', 'volunteer'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($requests);
    }

    private function handleApprovedRequest(VolunteerRequest $request)
    {
        $team = VolunteerTeam::findOrFail($request->team_id);
        $volunteer = Volunteer::findOrFail($request->volunteer_id);

        switch ($request->type) {
            case 'join_team':
                if (!$team->volunteers()->where('volunteer_id', $volunteer->id)->exists()) {
                    $team->volunteers()->attach($volunteer->id);
                }
                break;

            case 'leave_team':
                if ($team->volunteers()->where('volunteer_id', $volunteer->id)->exists()) {
                    $team->volunteers()->detach($volunteer->id);
                }
                break;

            case 'change_team':
                // Handle team change logic here
                // This would typically involve removing from old team and adding to new team
                break;
        }
    }
}
