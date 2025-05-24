<?php

namespace App\Http\Controllers;

use App\Models\Map;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MapViewController extends Controller
{
    public function index()
    {
        $maps = Map::all();
        return view('maps.index', compact('maps'));
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
        
        return view('maps.create', compact('grid'));
    }

    public function show($id)
    {
        $map = Map::findOrFail($id);
        return view('maps.show', compact('map'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'grid' => 'required',
            'origin' => 'nullable',
            'destination' => 'nullable',
        ]);

        Log::info('Dados recebidos:', $request->all());

        $gridData = $validated['grid'];
        if (is_string($gridData)) {
            $gridData = json_decode($gridData, true);
        }
        
        $origin = $validated['origin'] ?? null;
        if (is_string($origin) && !empty($origin)) {
            $origin = json_decode($origin, true);
        }
        
        $destination = $validated['destination'] ?? null;
        if (is_string($destination) && !empty($destination)) {
            $destination = json_decode($destination, true);
        }

        $map = new Map();
        $map->name = $validated['name'];
        $map->data = [
            'matrix' => $gridData,
            'origin' => $origin,
            'destination' => $destination
        ];
        $map->save();

        Log::info('Mapa salvo com ID: ' . $map->id);

        return redirect()->route('maps.show', $map->id)->with('success', 'Mapa criado com sucesso!');
    }
    
    public function edit($id)
    {
        $map = Map::findOrFail($id);
        $grid = $map->data['matrix'] ?? [];
        $origin = $map->data['origin'] ?? null;
        $destination = $map->data['destination'] ?? null;
        
        return view('maps.edit', compact('map', 'grid', 'origin', 'destination'));
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'origin' => 'nullable',
            'destination' => 'nullable',
            'path' => 'nullable',
            'grid' => 'nullable',
        ]);
        
        $map = Map::findOrFail($id);
        
        $origin = $validated['origin'] ?? null;
        if (is_string($origin) && !empty($origin)) {
            $origin = json_decode($origin, true);
        }
        
        $destination = $validated['destination'] ?? null;
        if (is_string($destination) && !empty($destination)) {
            $destination = json_decode($destination, true);
        }
        
        $path = $validated['path'] ?? null;
        if (is_string($path) && !empty($path)) {
            $path = json_decode($path, true);
        }
        
        $grid = $validated['grid'] ?? null;
        if (is_string($grid) && !empty($grid)) {
            $grid = json_decode($grid, true);
        }
        
        $data = $map->data;
        $data['origin'] = $origin;
        $data['destination'] = $destination;
        $data['path'] = $path;
        
        if ($grid) {
            $data['matrix'] = $grid;
        }
        
        $map->data = $data;
        
        $map->save();
        
        return redirect()->route('maps.show', $map->id)
                         ->with('success', 'Mapa atualizado com sucesso!');
    }
}