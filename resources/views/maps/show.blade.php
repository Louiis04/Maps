@extends('layouts.app')

@section('content')
    <h1>{{ $map->name }}</h1>
    
    <div class="map-legend">
        <div class="legend-item"><div class="color-box cell-0"></div> Rua (0)</div>
        <div class="legend-item"><div class="color-box cell-1"></div> Casa (1)</div>
        <div class="legend-item"><div class="color-box cell-2"></div> Prédio (2)</div>
        <div class="legend-item"><div class="color-box cell-3"></div> Praça (3)</div>
        <div class="legend-item"><div class="color-box origin-point"></div> Origem</div>
        <div class="legend-item"><div class="color-box destination-point"></div> Destino</div>
    </div>
    
    <div class="grid-container">
        @php
            $matrix = $map->data['matrix'] ?? [];
            $origin = $map->data['origin'] ?? null;
            $destination = $map->data['destination'] ?? null;
            $path = $map->data['path'] ?? [];
            $rows = count($matrix);
            $cols = $rows > 0 ? count($matrix[0]) : 0;
        @endphp
        
        @for($i = 0; $i < $rows; $i++)
            @for($j = 0; $j < $cols; $j++)
                @php
                    $cellClasses = ['grid-cell', 'cell-' . $matrix[$i][$j]];
                    
                    // Verificar se a célula está no caminho
                    $isInPath = false;
                    foreach ($path as $point) {
                        if ($point[0] == $i && $point[1] == $j) {
                            $isInPath = true;
                            break;
                        }
                    }
                    
                    if ($isInPath) {
                        $cellClasses[] = 'path-cell';
                    }
                    
                    // Adicionar classes para origem e destino
                    if ($origin && $origin[0] == $i && $origin[1] == $j) {
                        $cellClasses[] = 'origin-point';
                    }
                    if ($destination && $destination[0] == $i && $destination[1] == $j) {
                        $cellClasses[] = 'destination-point';
                    }
                @endphp
                
                <div class="{{ implode(' ', $cellClasses) }}"></div>
            @endfor
        @endfor
    </div>
    
    <p>Criado em: {{ $map->created_at->format('d/m/Y H:i') }}</p>
    
    <div class="actions">
        <a href="{{ route('maps.index') }}" class="btn">Voltar para Lista</a>
        <a href="{{ route('maps.edit', $map->id) }}" class="btn btn-edit">Definir Origem/Destino</a>
    </div>
    
    <style>
        .map-legend {
            display: flex;
            margin-bottom: 15px;
            flex-wrap: wrap;
        }
        .legend-item {
            display: flex;
            align-items: center;
            margin-right: 20px;
            margin-bottom: 10px;
        }
        .color-box {
            width: 20px;
            height: 20px;
            margin-right: 5px;
            border: 1px solid #ddd;
        }
        .grid-container {
            display: grid;
            grid-template-columns: repeat({{ $cols }}, 20px);
            gap: 1px;
            margin: 20px 0;
        }
        .grid-cell {
            width: 20px;
            height: 20px;
            border: 1px solid #ddd;
        }
        .cell-0 {
            background-color: #f0f0f0; 
        }
        .cell-1 {
            background-color: #4CAF50; 
        }
        .cell-2 {
            background-color: #2196F3; 
        }
        .cell-3 {
            background-color: #FFC107; 
        }
        .origin-point {
            background-color: #8BC34A !important; 
            position: relative;
        }
        .origin-point::after {
            content: "O";
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-weight: bold;
            font-size: 12px;
            color: white;
        }
        .destination-point {
            background-color: #9C27B0 !important; 
            position: relative;
        }
        .destination-point::after {
            content: "D";
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-weight: bold;
            font-size: 12px;
            color: white;
        }
        .path-cell {
            background-color: #FF9800 !important; 
            position: relative;
        }
        .path-cell::after {
            content: "•";
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-weight: bold;
            font-size: 14px;
            color: white;
        }
        .actions {
            margin-top: 20px;
        }
        .btn {
            display: inline-block;
            padding: 8px 16px;
            background-color: #2196F3;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            margin-right: 10px;
        }
        .btn:hover {
            background-color: #0b7dda;
        }
        .btn-edit {
            background-color: #FF9800;
        }
        .btn-edit:hover {
            background-color: #e68a00;
        }
    </style>
@endsection