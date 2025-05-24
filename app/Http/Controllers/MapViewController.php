<?php

namespace App\Http\Controllers;

use App\Models\Map;
use Illuminate\Http\Request;

class MapViewController extends Controller
{
    public function index()
    {
        $maps = Map::all();
        return view('maps.index', compact('maps'));
    }

    public function show($id)
    {
        $map = Map::findOrFail($id);
        return view('maps.show', compact('map'));
    }

    public function create()
    {
        $grid = [];
        for ($i = 0; $i < 20; $i++) {
            $grid[$i] = [];
            for ($j = 0; $j < 20; $j++) {
                $grid[$i][$j] = 0;
            }
        }
        
        $grid[3][4] = 1;
        $grid[3][5] = 1;
        $grid[4][5] = 1;
        $grid[5][5] = 1;
        $grid[10][10] = 2;
        $grid[10][11] = 2;
        $grid[11][10] = 2;
        
        return view('maps.create', compact('grid'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'grid' => 'required|array',
        ]);

        $map = new Map();
        $map->name = $validated['name'];
        $map->data = [
            'matrix' => $validated['grid']
        ];
        $map->save();

        return redirect()->route('maps.show', $map->id)->with('success', 'Mapa criado com sucesso!');
    }
}