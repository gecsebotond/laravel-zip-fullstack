<?php

namespace App\Http\Controllers;

use App\Models\County;
use Illuminate\Http\Request;

class CountyController extends Controller
{
    public function index()
    {
        $counties = County::with('places')->get();

        return response()->json([
            'status' => 'success',
            'data' => $counties
        ]);
    }

    public function show($id)
    {
        $county = County::with('places')->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data' => $county
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:counties,name',
        ]);

        $county = County::create([
            'name' => $request->name,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'County created successfully',
            'data' => $county
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $county = County::findOrFail($id);

        $request->validate([
            'name' => 'required|string|unique:counties,name,' . $county->id,
        ]);

        $county->update([
            'name' => $request->name,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'County updated successfully',
            'data' => $county
        ]);
    }

    public function destroy($id)
    {
        $county = County::findOrFail($id);
        $county->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'County deleted successfully'
        ], 204);
    }
}