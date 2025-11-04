<?php

namespace App\Http\Controllers\Api;

use App\Models\Volunteer;

use Illuminate\Http\Request;
use App\Models\VolunteerTeam;
use App\Models\BusinessInformation;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    //Volunteer
    public function volunteerRegister(Request $request)
    {
        try {
            $request->validate([
                'full_name' => 'required|string|max:255',
              
                'email' => 'required|string|email|max:255|unique:volunteers',
                'password' => 'required|string|min:8',
                'specialization_id' => 'required|exists:specializations,id',
              
            ]);

            $volunteer = Volunteer::create([
                'full_name' => $request->full_name,
                // 'national_number' => $request->national_number,
                // 'nationality' => $request->nationality,
                // 'phone' => $request->phone,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                // 'birth_date' => $request->birth_date,
                'specialization_id' => $request->specialization_id,
               
                'total_points' => 0,
            ]);

            $token = $volunteer->createToken('auth_token')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Volunteer registered successfully',
                'data' => [
                    'volunteer' => $volunteer,
                    'token' => $token,
                ]
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'errors' => $e instanceof ValidationException ? $e->errors() : []
            ], 422);
        }
    }

    public function volunteerLogin(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);
    
        $volunteer = Volunteer::where('email', $request->email)->first();
    
        if (!$volunteer || !Hash::check($request->password, $volunteer->password)) {
            return response()->json([
                'message' => 'The provided credentials are incorrect.',
            ], 403);
        }
    
        $token = $volunteer->createToken('auth_token')->plainTextToken;
    
        return response()->json([
            'message' => 'Volunteer logged in successfully',
            'volunteer' => $volunteer,
            'token' => $token,
        ]);
    }
    

    public function profileVolunteer(Request $request)
    {
        $volunteer = $request->user(); 
        $volunteer->load('specialization');
    
        return response()->json([
            'success' => true,
            'message' => 'Volunteer profile retrieved successfully',
            'data' => $volunteer,
        ]);
    }
    

    public function updateProfilevolunteer(Request $request)
    {
        $volunteer = $request->user();

        $request->validate([
            'full_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:volunteers,email,' . $volunteer->id,
            'password' => 'sometimes|string|min:8',
            'phone' => 'sometimes|string|max:255',
            'nationality' => 'required|string|max:255',
            'birth_date' => 'required|date_format:Y-m-d',
            'national_number' => 'required|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'specialization_id' => 'required|exists:specializations,id',
        ]);
        
    
        // رفع الصورة إذا كانت موجودة
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = uniqid() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('uploads/volunteers'), $imageName);
    
            $volunteer->image = 'uploads/volunteers/' . $imageName;
        }
    
        if ($request->has('password')) {
            $volunteer->password = Hash::make($request->password);
        }
    
        $volunteer->fill($request->except(['password', 'email', 'image']));
        
        if ($request->has('email')) {
            $volunteer->email = $request->email;
        }
    
        $volunteer->save();
    
        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully',
            'data' => $volunteer
        ]);
    }

    /// teams
    public function teamRegister(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'full_name' => 'required|string|max:255',
            'national_number' => 'required|string|unique:volunteer_teams',
            'phone' => 'required|string|max:255|unique:volunteer_teams',
            'gender' => 'required|in:ذكر,أنثى',
            'nationality' => 'required|string',
            'birth_date' => 'required|date_format:Y-m-d',
            'image' => 'required|image',
            'email' => 'required|email|unique:volunteer_teams',
            'password' => 'required|string|min:8',
    
            'type'=>'required|in:volunteer teams,charities',
            'long'=>'required',
            'lat'=>'required',

            'team_name' => 'required|string|max:255',
            'bank_account_number' => 'required|string|unique:business_informations',
            'log_image' => 'required|image',
            'logo' => 'required|image',
            'license_number' => 'required|string',
            'address' => 'nullable|string',
        ]);
    
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }
    
        DB::beginTransaction();
    
        try {
            $imagePath = $request->file('image')->move(public_path('uploads/volunteers'), uniqid() . '.' . $request->file('image')->getClientOriginalExtension());
            $logoPath = $request->file('logo')->move(public_path('uploads/logos'), uniqid() . '.' . $request->file('logo')->getClientOriginalExtension());
            $logImagePath = $request->file('log_image')->move(public_path('uploads/log'), uniqid() . '.' . $request->file('log_image')->getClientOriginalExtension());
    
            $imageRelativePath = 'uploads/volunteers/' . basename($imagePath);
            $logoRelativePath = 'uploads/logos/' . basename($logoPath);
            $logImageRelativePath = 'uploads/log/' . basename($logImagePath);
    
            $volunteerTeam = VolunteerTeam::create([
                'full_name' => $request->full_name,
                'national_number' => $request->national_number,
                'phone' => $request->phone,
                'gender' => $request->gender,
                'nationality' => $request->nationality,
                'birth_date' => $request->birth_date,
                'image' => $imageRelativePath,
                'email' => $request->email,
                'type'=>$request->type,


                'password' => Hash::make($request->password),
                'status' =>'pending',
            ]);
    
            $businessInfo = BusinessInformation::create([
                'team_name' => $request->team_name,
                'bank_account_number' => $request->bank_account_number,
                'log_image' => $logImageRelativePath,
                'logo' => $logoRelativePath,
                'license_number' => $request->license_number,
                'address' => $request->address,
                'long'=>$request->long,
                'lat'=>$request->lat,
                'team_id' => $volunteerTeam->id,
            ]);
    
            DB::commit();
    
            return response()->json([
                'success'=> true,
                'message' => 'Team registered successfully',
                'data' => $volunteerTeam,
                'business_info' => $businessInfo,
            ], 201);
    
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Registration failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    

    public function teamLogin(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);
    
        $team = VolunteerTeam::where('email', $request->email)->first();
    
        
        if (!$team) {
            return response()->json([
                'message' => 'Team with this email not found'
            ], 404);
        }
        
        if ($team->status == "rejected" || $team->status ==  "pending") {
            return response()->json([
                'message' => 'عذرًا، حسابك غير مفعل.',
            ], 403); 
        }
    
        if (!Hash::check($request->password, $team->password)) {
            return response()->json([
                'message' => 'The provided credentials are incorrect.',
            ], 403);
        }
    
        $token = $team->createToken('auth_token')->plainTextToken;
    
        return response()->json([
            'success' => true,
            'message' => 'Team  logged in successfully',
            'data' => [
                  'team' => [
                    ...$team->toArray(),
                    'role' => 'Admin'
                ],
                'token' => $token
            ]
        ]);
   
    }
    
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Successfully logged out',
        ]);
    }

    // تسجيل الدخول
    public function loginemployee(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (!Auth::guard('employee')->attempt($credentials)) {
            return response()->json(['message' => 'بيانات الدخول غير صحيحة'], 401);
        }

        $employee = Auth::guard('employee')->user();
        return response()->json(['employee' => $employee], 200);
    }
} 