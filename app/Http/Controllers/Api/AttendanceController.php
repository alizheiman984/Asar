<?php

namespace App\Http\Controllers\Api;

use App\Models\Point;
use App\Models\Campaign;
use App\Models\Employee;
use App\Models\Volunteer;
use App\Models\Attendance;
use App\Models\Certificate;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

class AttendanceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $attendances = Attendance::with(['volunteer', 'employee', 'campaign'])->get();
        return response()->json($attendances);
    }

    public function getAttendancesByCampaign($campaignId)
    {
        $attendances = Attendance::with(['volunteer', 'employee']) // إحضار العلاقات المطلوبة
            ->where('campaign_id', $campaignId)
            ->get();

        return response()->json([
            'message' => 'تم جلب الحضور والغياب بنجاح',
            'attendances' => $attendances,
        ]);
    }


    /**
     * Store a newly created resource in storage.
     */
  

    public function store(Request $request)
    {
        $request->validate([
            'volunteer_id' => 'required|exists:volunteers,id',
            'campaign_id' => 'required|exists:campaigns,id',
            'is_attendance' => 'required|boolean',
            'reason_of_change' => 'required|in:عذر,حضور,عدم حضور',
            'image' => 'nullable|image',
        ]);

        $existingAttendance = Attendance::where('volunteer_id', $request->volunteer_id)
            ->where('campaign_id', $request->campaign_id)
            ->first();

        if ($existingAttendance) {
            return response()->json([
                'message' => 'تم تسجيل الحضور مسبقًا لهذا المتطوع في هذه الحملة.',
            ], 409);
        }

        $campaign = Campaign::findOrFail($request->campaign_id);
        $volunteer = Volunteer::findOrFail($request->volunteer_id);
        $points = 0;
        $magnitudeChange = 'none';

        if ($request->is_attendance) {
            $points = $campaign->points;
            $volunteer->increment('total_points', $points);
            $magnitudeChange = '+' . $points;
        } elseif ($request->reason_of_change === 'عدم حضور') {
            $points = intval($campaign->points / 2);
            $volunteer->decrement('total_points', $points);
            $magnitudeChange = '-' . $points;
        } elseif ($request->reason_of_change === 'عذر') {
          
            $magnitudeChange = '0';
        }

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('attendances'), $imageName);
            $imagePath = 'attendances/' . $imageName;
        } else {
            $imagePath = null;
        }

        $attendance = Attendance::create([
            'volunteer_id' => $request->volunteer_id,
            'campaign_id' => $request->campaign_id,
            'employee_id' => auth()->user()->id,
            'is_attendance' => $request->is_attendance,
            'points_earned' => $request->is_attendance ? $campaign->points : 0,
            'image' => $imagePath,
        ]);

        if ($volunteer->total_points >= 10) {
        $existingCertificate = Certificate::where('volunteer_id', $request->volunteer_id)
            ->where('points_threshold', 100)
            ->first();

        $certificatePath = 'certificates/volunteer_' . $request->volunteer_id . '.pdf';

        if (!$existingCertificate) {
          Certificate::create([
                'volunteer_id' => $request->volunteer_id,
                'level' => 'متطوع نشط',
                'points_threshold' => 100,
                'certificate_type' => 'تشجيعية', 
                'issued_at' => now(),
                'certificate_path' => $certificatePath,
                'certificate_issued' => true,
            ]);

        }
    }
        if ($magnitudeChange !== '0' && $magnitudeChange !== 'none') {
            Point::create([
                'volunteer_id' => $request->volunteer_id,
                'campaign_id' => $request->campaign_id,
                'employee_id' => auth()->user()->id,
                'points' => $points,
                'magnitude_change' => $magnitudeChange,
                'reason_of_change' => $request->reason_of_change,
            ]);
        }

        return response()->json([
            'message' => 'تم تسجيل الحضور ومعالجة النقاط بنجاح',
            'attendance' => $attendance,
        ]);
    }


    /**
     * Display the specified resource.
     */
    public function show(Attendance $attendance)
    {
        $attendance->load(['volunteer', 'employee', 'campaign']);
        return response()->json($attendance);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {

        $request->validate([
            'is_attendance' => 'required|boolean',
            'reason_of_change' => 'required|in:عذر,حضور,عدم حضور',
            'image' => 'nullable|image',

        ]);



        $attendance = Attendance::findOrFail($id);
        $volunteer = Volunteer::findOrFail($attendance->volunteer_id);
        $campaign = Campaign::findOrFail($attendance->campaign_id);

        $oldPoints = $attendance->points_earned;

        if ($attendance->is_attendance) {
            $volunteer->decrement('total_points', $oldPoints);
        } elseif ($attendance->reason_of_change === 'عدم حضور') {
            $volunteer->increment('total_points', intval($campaign->points / 2));
        }

        $newPoints = 0;
        $magnitudeChange = 'none';

        if ($request->is_attendance) {
            $newPoints = $campaign->points;
            $volunteer->increment('total_points', $newPoints);
            $magnitudeChange = '+' . $newPoints;
        } elseif ($request->reason_of_change === 'عدم حضور') {
            $newPoints = intval($campaign->points / 2);
            $volunteer->decrement('total_points', $newPoints);
            $magnitudeChange = '-' . $newPoints;
        } elseif ($request->reason_of_change === 'عذر') {
            $magnitudeChange = '0';
        }

      if ($request->hasFile('image')) {
            if ($attendance->image && file_exists(public_path($attendance->image))) {
                unlink(public_path($attendance->image));
            }

            $image = $request->file('image');
            $imageName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('attendances'), $imageName);
            $imagePath = 'attendances/' . $imageName;
        } else {
            $imagePath = $attendance->image;
        }


        $attendance->update([

            'is_attendance' => $request->is_attendance,
            'points_earned' => $request->is_attendance ? $campaign->points : 0,
            'image' => $imagePath,
            
        ]);

        Point::where('volunteer_id', $attendance->volunteer_id)
            ->where('campaign_id', $attendance->campaign_id)
            ->delete();

        if ($magnitudeChange !== '0' && $magnitudeChange !== 'none') {
            Point::create([
                'volunteer_id' => $attendance->volunteer_id,
                'campaign_id' => $attendance->campaign_id,
                'employee_id' => auth()->user()->id,
                'points' => $newPoints,
                'magnitude_change' => $magnitudeChange,
                'reason_of_change' => $request->reason_of_change,
            ]);
        }

        return response()->json([
            'message' => 'تم تحديث الحضور ومعالجة النقاط بنجاح',
            'attendance' => $attendance,
        ]);
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Attendance $attendance)
    {
        // Delete associated image if exists
        if ($attendance->image) {
            Storage::disk('public')->delete($attendance->image);
        }

        // Update volunteer's points if attendance was not marked
        if (!$attendance->is_attended) {
            $volunteer = Volunteer::findOrFail($attendance->volunteer_id);
            $volunteer->total_points += $attendance->banned_point;
            $volunteer->save();
        }

        $attendance->delete();
        return response()->json(null, 204);
    }

    public function getVolunteerAttendance(Volunteer $volunteer)
    {
        $attendances = Attendance::where('volunteer_id', $volunteer->id)
            ->with('campaign')
            ->orderBy('date_of_attendance', 'desc')
            ->get();

        return response()->json($attendances);
    }

    public function getEmployeeAttendance(Employee $employee)
    {
        $attendances = Attendance::where('employee_id', $employee->id)
            ->with(['volunteer', 'campaign'])
            ->orderBy('date_of_attendance', 'desc')
            ->get();

        return response()->json($attendances);
    }

    public function getCampaignAttendance(Campaign $campaign)
    {
        $attendances = Attendance::where('campaign_id', $campaign->id)
            ->with(['volunteer', 'employee'])
            ->orderBy('date_of_attendance', 'desc')
            ->get();

        return response()->json($attendances);
    }

    public function getVolunteerCampaignAttendance(Volunteer $volunteer, Campaign $campaign)
    {
        $attendance = Attendance::where('volunteer_id', $volunteer->id)
            ->where('campaign_id', $campaign->id)
            ->with('campaign')
            ->first();

        return response()->json($attendance);
    }
}
