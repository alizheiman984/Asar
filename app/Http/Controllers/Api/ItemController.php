<?php

namespace App\Http\Controllers\Api;

use App\Models\Item;
use App\Models\Inventory;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ItemController extends Controller
{

     public function index()
    {
        $items = Item::all();

        return response()->json($items);
    }


    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'unit' => 'nullable|string|max:50',
        ]);

        $item = Item::create([
            'name' => $request->name,
            'unit' => $request->unit
        ]);

        return response()->json([
            'message' => 'تم إنشاء المادة بنجاح',
            'item' => $item
        ], 201);
    }


    public function show($id)
    {
        $item = Item::find($id);

        if (!$item) {
            return response()->json(['message' => 'المادة غير موجودة'], 404);
        }

        return response()->json($item);
    }

  
    public function update(Request $request, $id)
    {
        $item = Item::find($id);

        if (!$item) {
            return response()->json(['message' => 'المادة غير موجودة'], 404);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'unit' => 'required|string|max:50',
        ]);

        $item->update([
            'name' => $request->name,
            'unit' => $request->unit, 
        ]);

        return response()->json([
            'message' => 'تم تحديث المادة بنجاح',
            'item' => $item
        ]);
    }


    public function destroy($id)
    {
        $item = Item::find($id);

        if (!$item) {
            return response()->json(['message' => 'المادة غير موجودة'], 404);
        }

        $item->delete();

        return response()->json(['message' => 'تم حذف المادة بنجاح']);
    }
}
