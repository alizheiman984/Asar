<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BenefitRequests;
use App\Models\BenefitRequestImage;
use Illuminate\Support\Str;

class BenefitRequestController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'description' => 'required|string',
            'images' => 'required|array|min:1',
            'images.*' => 'image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $benefit = BenefitRequests::create([
            'volunteer_id' => auth()->id(),
            'description' => $request->description,
            'status' => 'pending',
        ]);

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {

                $fileName = Str::uuid() . '.' . $image->getClientOriginalExtension();
                $image->move(public_path('BenefitRequests'), $fileName);

                BenefitRequestImage::create([
                    'benefit_request_id' => $benefit->id,
                    'image_path' => 'BenefitRequests/' . $fileName, // نخزن path نسبي
                ]);
            }
        }

        return response()->json([
            'message' => 'Benefit request created successfully',
            'data' => $benefit->load('images'),
        ], 201);
    }

 
    public function myRequests()
    {
        $requests = BenefitRequests::with('images')
            ->where('volunteer_id', auth()->id())
            ->latest()
            ->get();

        return response()->json($requests);
    }

    public function destroy($id)
    {
        $benefit = BenefitRequests::with('images')->find($id);

        if (!$benefit) {
            return response()->json([
                'message' => 'Benefit request not found'
            ], 404);
        }

        foreach ($benefit->images as $image) {
            $imagePath = public_path($image->image_path);

            if (\File::exists($imagePath)) {
                \File::delete($imagePath);
            }
        }

        $benefit->delete();

        return response()->json([
            'message' => 'Benefit request and images deleted successfully'
        ], 200);
    }




    // Field Supervisor

    public function fieldRequestsPending()
    {
        $requests = BenefitRequests::with(['images', 'volunteer'])
            ->where('status', 'pending')
            ->latest()
            ->get();

        return response()->json($requests);
    }

        public function fieldRequestsCompleted()
    {
        $requests = BenefitRequests::with(['images', 'volunteer'])
            ->where('status', 'completed')
            ->latest()
            ->get();

        return response()->json($requests);
    }

    public function fieldRequestsRejected()
    {
        $requests = BenefitRequests::with(['images', 'volunteer'])
            ->where('status', 'rejected')
            ->latest()
            ->get();

        return response()->json($requests);
    }

    public function fieldRequestsAccepted()
    {
        $requests = BenefitRequests::with(['images', 'volunteer'])
            ->where('status', 'accepted')
            ->latest()
            ->get();

        return response()->json($requests);
    }

    public function fieldRequestsAll()
    {
        $requests = BenefitRequests::with(['images', 'volunteer'])
            ->latest()
            ->get();

        return response()->json($requests);
    }


      public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:accepted,rejected,completed',
            'supervisor_note' => 'nullable|string',
        ]);

        $benefit = BenefitRequests::find($id);

        if (!$benefit) {
            return response()->json([
                'message' => 'Benefit request not found'
            ], 404);
        }

        $benefit->update([
            'status' => $request->status,
            'supervisor_note' => $request->supervisor_note,
            'employee_id' => auth()->id(),
        ]);

        return response()->json([
            'message' => 'Status updated successfully',
            'data' => $benefit
        ]);
    }

}
