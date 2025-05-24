<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Map;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class MapController extends Controller
{

    public function index()
    {
        $maps = Map::all();
        return response()->json($maps);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'data' => 'required|array',
        ]);

        $map = Map::create($validated);

        return response()->json($map, Response::HTTP_CREATED);
    }

    public function show(string $id)
    {
        $map = Map::findOrFail($id);
        return response()->json($map);
    }

    public function destroy(string $id)
    {
        $map = Map::findOrFail($id);
        $map->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}