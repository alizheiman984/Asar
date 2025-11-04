<?php

namespace App\Http\Controllers\Api;

use App\Models\OTP;
use App\Mail\SendOtpMail;
use Illuminate\Http\Request;
use App\Models\VolunteerTeam;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;

class OTPController extends Controller
{
    public function sendOTP(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email',
            ]);
    
            $models = [
                \App\Models\Volunteer::class,
                \App\Models\VolunteerTeam::class,
            ];
    
            $exists = false;
    
            foreach ($models as $model) {
                if ($model::where('email', $request->email)->exists()) {
                    $exists = true;
                    break;
                }
            }
    
            if (!$exists) {
                return response()->json([
                    'success' => false,
                    'message' => 'No account found with this email'
                ], 404);
            }
    
            $otp = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
            $expiresAt = now()->addMinutes(5);
    
            OTP::create([
                'otp' => $otp,
                'email' => $request->email,
                'expires_at' => $expiresAt,
            ]);
    
            $to = $request->email;
            // $subject = 'Your OTP Code';
            // $message = "Your OTP code is: $otp\n\nIt will expire in 5 minutes.";
            // $headers = "From: info@Asar.com\r\n";
            // $headers .= "Reply-To: info@Asar.com\r\n";
            // $headers .= "X-Mailer: PHP/" . phpversion();
            
            
            // $sendResult = mail($to, $subject, $message, $headers);

             Mail::raw("Your OTP code is: $otp\n\nIt will expire in 5 minutes.", function ($message) use ($to) {
                $message->to($to)
                        ->subject('Your OTP Code');
            });
            
            // \Log::info('Trying to send mail', [
            //     'to' => $to,
            //     'subject' => $subject,
            //     'result' => $sendResult ? 'Success' : 'Fail'
            // ]);
            
            // if (!$sendResult) {
            //     throw new \Exception("Failed to send email.");
            // }

            return response()->json([
                'success' => true,
                'message' => 'OTP sent successfully',
                'expires_at' => $expiresAt,
                'otp' => $otp,
            ]);
    
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send OTP',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    


    

    public function verifyOTP(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email',
                'otp' => 'required|string',
            ]);

            $otp = OTP::where('email', $request->email)
                      ->where('otp', $request->otp)
                      ->where('expires_at', '>', now())
                      ->first();

            if (!$otp) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid or expired OTP'
                ], 422);
            }

            $otp->delete();

            return response()->json([
                'success' => true,
                'message' => 'OTP verified successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to verify OTP',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function updatePasswordForVolunteerEntities(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email',
                'password' => 'required|string|min:8|confirmed',
            ]);
    
            $models = [
                \App\Models\Volunteer::class,
                \App\Models\VolunteerTeam::class,
            ];
    
            $found = false;
    
            foreach ($models as $model) {
                $entity = $model::where('email', $request->email)->first();
    
                if ($entity) {
                    $entity->password = bcrypt($request->password);
                    $entity->save();
    
                    $found = true;
                    break;
                }
            }
    
            if (!$found) {
                return response()->json([
                    'success' => false,
                    'message' => 'No account found with this email'
                ], 404);
            }
    
            return response()->json([
                'success' => true,
                'message' => 'Password updated successfully'
            ]);
    
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update password',
                'error' => $e->getMessage()
            ], 500);
        }
    }

} 