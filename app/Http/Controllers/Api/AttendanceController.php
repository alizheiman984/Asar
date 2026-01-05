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
            'volunteer_id'     => 'required|exists:volunteers,id',
            'campaign_id'      => 'required|exists:campaigns,id',
            'scanned'          => 'required|boolean',
            'reason_of_change' => 'required_if:scanned,false|in:عذر,عدم حضور',
            'image'            => 'nullable|image',
        ]);

        
        $isAttendance = $request->scanned ? 1 : 0;
        $reason       = $isAttendance ? 'حضور' : $request->reason_of_change;

        $alreadyExists = Attendance::where([
            'volunteer_id' => $request->volunteer_id,
            'campaign_id'  => $request->campaign_id,
        ])->exists();

        if ($alreadyExists) {
            return response()->json([
                'message' => 'تم تسجيل الحضور مسبقًا لهذا المتطوع في هذه الحملة.',
            ], 409);
        }

   
        $campaign  = Campaign::findOrFail($request->campaign_id);
        $volunteer = Volunteer::findOrFail($request->volunteer_id);

    
        $points = 0;
        $magnitudeChange = 'none';

        if ($isAttendance) {
        
            $points = $campaign->points;
            $volunteer->increment('total_points', $points);
            $magnitudeChange = '+' . $points;

        } elseif ($reason === 'عدم حضور') {
        
            $points = $campaign->points;
            $volunteer->decrement('total_points', $points);
            $magnitudeChange = '-' . $points;

        } elseif ($reason === 'عذر') {
         
            $points = intval($campaign->points / 2);
            $volunteer->decrement('total_points', $points);
            $magnitudeChange = '-' . $points;
        }

    
        $imagePath = null;
        if ($request->hasFile('image')) {
            $imageName = time() . '_' . uniqid() . '.' . $request->image->extension();
            $request->image->move(public_path('attendances'), $imageName);
            $imagePath = 'attendances/' . $imageName;
        }

     
        $attendance = Attendance::create([
            'volunteer_id'    => $request->volunteer_id,
            'campaign_id'     => $request->campaign_id,
            'employee_id'     => auth()->id(),
            'is_attendance'   => $isAttendance,
            'reason_of_change'=> $reason,
            'points_earned'   => $isAttendance ? $campaign->points : 0,
            'image'           => $imagePath,
        ]);


    
        if ($magnitudeChange !== 'none') {
            Point::create([
                'volunteer_id'     => $request->volunteer_id,
                'campaign_id'      => $request->campaign_id,
                'employee_id'      => auth()->id(),
                'points'           => $points,
                'magnitude_change' => $magnitudeChange,
                'reason_of_change' => $reason,
            ]);
        }

    
        if ($volunteer->total_points >= 10) {
            Certificate::firstOrCreate(
                [
                    'volunteer_id'     => $request->volunteer_id,
                    'points_threshold' => 10,
                ],
                [
                    'level'              => 'متطوع نشط',
                    'certificate_type'   => 'تشجيعية',
                    'certificate_path'   => 'certificates/volunteer_' . $request->volunteer_id . '.pdf',
                    'certificate_issued' => true,
                ]
            );
        }

    
        return response()->json([
            'message'    => 'تم تسجيل الحضور ومعالجة النقاط بنجاح',
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
            'scanned'          => 'required|boolean',
            'reason_of_change' => 'required_if:scanned,false|in:عذر,عدم حضور',
            'image'            => 'nullable|image',
        ]);

       
        $attendance = Attendance::findOrFail($id);
        $volunteer  = Volunteer::findOrFail($attendance->volunteer_id);
        $campaign   = Campaign::findOrFail($attendance->campaign_id);

   
        $newIsAttendance = $request->scanned ? 1 : 0;
        $newReason       = $newIsAttendance ? 'حضور' : $request->reason_of_change;

       
        if ($attendance->is_attendance) {
            $volunteer->decrement('total_points', $campaign->points);

        } elseif ($attendance->reason_of_change === 'عدم حضور') {
            $volunteer->increment('total_points', $campaign->points);

        } elseif ($attendance->reason_of_change === 'عذر') {
            $volunteer->increment('total_points', intval($campaign->points / 2));
        }

      
        $points = 0;
        $magnitudeChange = 'none';

        if ($newIsAttendance) {
          
            $points = $campaign->points;
            $volunteer->increment('total_points', $points);
            $magnitudeChange = '+' . $points;

        } elseif ($newReason === 'عدم حضور') {
         
            $points = $campaign->points;
            $volunteer->decrement('total_points', $points);
            $magnitudeChange = '-' . $points;

        } elseif ($newReason === 'عذر') {
        
            $points = intval($campaign->points / 2);
            $volunteer->decrement('total_points', $points);
            $magnitudeChange = '-' . $points;
        }

       
        $imagePath = $attendance->image;

        if ($request->hasFile('image')) {
            if ($attendance->image && file_exists(public_path($attendance->image))) {
                unlink(public_path($attendance->image));
            }

            $imageName = time() . '_' . uniqid() . '.' . $request->image->extension();
            $request->image->move(public_path('attendances'), $imageName);
            $imagePath = 'attendances/' . $imageName;
        }

      
        $attendance->update([
            'is_attendance'    => $newIsAttendance,
            'reason_of_change' => $newReason,
            'points_earned'    => $newIsAttendance ? $campaign->points : 0,
            'image'            => $imagePath,
        ]);

       
        Point::where('volunteer_id', $attendance->volunteer_id)
            ->where('campaign_id', $attendance->campaign_id)
            ->delete();

        if ($magnitudeChange !== 'none') {
            Point::create([
                'volunteer_id'     => $attendance->volunteer_id,
                'campaign_id'      => $attendance->campaign_id,
                'employee_id'      => auth()->id(),
                'points'           => $points,
                'magnitude_change' => $magnitudeChange,
                'reason_of_change' => $newReason,
            ]);
        }

  
        return response()->json([
            'message'    => 'تم تحديث الحضور ومعالجة النقاط بنجاح',
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
