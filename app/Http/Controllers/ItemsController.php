<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ItemsController extends Controller
{

    public function update(Request $request, string $id, string $item_id)
    {
        $name = $request->input('name');
        $description = $request->input('description');
        $quantity = $request->input('quantity');
        $category_id = $request->input('category_id');

        // Required parameters check
        if (!$name || !$quantity || !$category_id) {
            return response()->json([
                'message' => 'Required parameters missing',
            ], 400);
        }

        $user = User::findOrFail($id);
        $item = $user->items()->find($item_id);

        if (!$item) {
            return response()->json(['message' => 'Item not found'], 404);
        }

        $item->update([
            'name' => $name,
            'description' => $description,
            'quantity' => $quantity,
            'category_id' => $category_id
        ]);

        // Retrieve the existing items of the user
        $items = $user->items()->get()->toArray();

        $user->items = json_encode($items);
        $user->save();

        return response()->json([
            'message' => 'Item updated successfully',
            'item' => [
                'id' => $item->id,
                'name' => $item->name,
                'description' => $item->description,
                'quantity' => $item->quantity,
                'category_id' => $item->category_id,
            ]
        ]);
    }

    /**
     * Delete an item by ID.
     */


    public function destroy($id, $item_id)
    {
        $user = User::findOrFail($id);
        $item = $user->items()->find($item_id);
        if (!$item) {
            return response()->json(['message' => 'Item not found'], 404);
        }
        $item->delete();
        $user->items = $user->items()->get();
        $user->save();
        return response()->json(['message' => 'Item deleted successfully'], 200);
    }



    public function item_post(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'quantity' => 'required|integer|min:1',
            'category_id' => 'required|integer|exists:categories,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // Add image validation rules
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 400);
        }

        $user = User::findOrFail($id);

        $itemData = [
            "name" => $request->input('name'),
            "description" => $request->input('description'),
            "quantity" => $request->input('quantity'),
            "category_id" => $request->input('category_id'),
        ];

        // Handle image upload
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time() . '_' . $image->getClientOriginalName();
            $image->storeAs('images', $imageName, 'public');
            $itemData['image'] = $imageName;
        }

        $item = $user->items()->create($itemData);

        // Retrieve the existing items of the user
        $items = $user->items()->get()->toArray();

        // Append the new item to the items array
        $items[] = $item;

        // Update the items field of the user with the updated array
        $user->items = json_encode($items);
        $user->save();

        return response()->json([
            'message' => 'New item added successfully',
            'item' => [
                'id' => $item->id,
                'name' => $item->name,
                'description' => $item->description,
                'quantity' => $item->quantity,
                'category_id' => $item->category_id,
                'image' => $item->image,
            ],
        ]);
    }
}
