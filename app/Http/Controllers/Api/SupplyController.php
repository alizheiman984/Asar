<?php

namespace App\Http\Controllers\Api;

use App\Models\Supplie;
use App\Models\Inventory;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class SupplyController extends Controller
{
    
    public function addSupply(Request $request)
    {
        $request->validate([
            'item_id' => 'required|exists:items,id',
            'quantity' => 'required|integer|min:1',
        ]);

        Supplie::create([
            'employee_id' =>auth()->user()->id,
            'item_id' => $request->item_id,
            'quantity' => $request->quantity,
            'type' => 'اضافة',
            'notes' => $request->notes,
            'campaign_id' => $request->campaign_id
        ]);

        $inventory = Inventory::firstOrCreate(
        ['item_id' => $request->item_id],
        ['quantity' => 0]
    );

    $inventory->increment('quantity', $request->quantity);
        return response()->json(['message' => 'تمت الإضافة بنجاح']);
    }

    public function consume(Request $request)
    {
        $request->validate([
            'item_id' => 'required|exists:items,id',
            'quantity' => 'required|integer|min:1',
        ]);

      $inventory = Inventory::where('item_id', $request->item_id)->first();

        if (!$inventory || $inventory->quantity < $request->quantity) {
            return response()->json(['message' => 'الكمية غير كافية'], 400);
        }

        Supplie::create([
            'employee_id' => auth()->user()->id,
            'item_id' => $request->item_id,
            'quantity' => $request->quantity,
            'type' => 'صرف',
            'notes' => $request->notes,
            'campaign_id' => $request->campaign_id
        ]);

    $inventory->decrement('quantity', $request->quantity);
    
        return response()->json(['message' => 'تم الصرف بنجاح']);
    }



    public function teamSupplies()
    {
        $team_id = auth()->user()->team_id;

        $supplies = Supplie::with(['item', 'employee', 'campaign'])
            ->whereHas('employee', function($q) use ($team_id) {
                $q->where('team_id', $team_id);
            })
            ->latest()
            ->get();

        return response()->json($supplies);
    }



}
