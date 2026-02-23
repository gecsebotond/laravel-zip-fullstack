<?php

namespace App\Http\Controllers;

use App\Models\Place;
use Illuminate\Http\Request;

class PlaceController extends Controller
{
    public function index()
    {
        return Place::with('county')->get();
    }

    public function show($id)
    {
        return Place::with('county')->findOrFail($id);
    }
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function store(Request $request)
    {
        $request->validate([
            'postal_code' => 'required|string',
            'name'        => 'required|string',
            'county_id'   => 'required|exists:counties,id',
        ]);

        $place = Place::create($request->all());

        return response()->json($place, 201);
    }

    public function update(Request $request, $id)
    {
        $place = Place::findOrFail($id);

        $request->validate([
            'postal_code' => 'sometimes|required|string',
            'name'        => 'sometimes|required|string',
            'county_id'   => 'sometimes|required|exists:counties,id',
        ]);

        $place->update($request->all());

        return response()->json($place);
    }

    public function destroy($id)
    {
        $place = Place::findOrFail($id);
        $place->delete();

        return response()->json(null, 204);
    }
}