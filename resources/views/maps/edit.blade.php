@extends('layouts.app')

@section('content')
    <h1>Definir Origem e Destino para: {{ $map->name }}</h1>
    
    <div class="map-legend">
        <div class="legend-item"><div class="color-box cell-0"></div> Rua (0)</div>
        <div class="legend-item"><div class="color-box cell-1"></div> Casa (1)</div>
        <div class="legend-item"><div class="color-box cell-2"></div> Prédio (2)</div>
        <div class="legend-item"><div class="color-box cell-3"></div> Praça (3)</div>
        <div class="legend-item"><div class="color-box origin-point"></div> Origem</div>
        <div class="legend-item"><div class="color-box destination-point"></div> Destino</div>
    </div>
    
    <div class="controls">
        <div class="btn-group">
            <button type="button" onclick="setMode('origin')" class="btn btn-mode active" id="origin-mode">Definir Origem</button>
            <button type="button" onclick="setMode('destination')" class="btn btn-mode" id="destination-mode">Definir Destino</button>
            <button type="button" onclick="findPath()" class="btn btn-mode btn-find-path" id="find-path">Encontrar Caminho</button>
        </div>
        <p class="mode-info" id="current-mode">Modo atual: Definir Origem</p>
        <p>Clique em uma célula do mapa para definir o ponto.</p>
    </div>
    
    <div class="grid-container">
        @for($i = 0; $i < count($grid); $i++)
            @for($j = 0; $j < count($grid[$i]); $j++)
                @php
                    $cellClasses = ['grid-cell', 'cell-' . $grid[$i][$j]];
                    
                    // Adicionar classes para origem e destino existentes
                    if ($origin && $origin[0] == $i && $origin[1] == $j) {
                        $cellClasses[] = 'origin-point';
                    }
                    if ($destination && $destination[0] == $i && $destination[1] == $j) {
                        $cellClasses[] = 'destination-point';
                    }
                @endphp
                
                <div class="{{ implode(' ', $cellClasses) }}" 
                     data-row="{{ $i }}" 
                     data-col="{{ $j }}"
                     onclick="handleCellClick({{ $i }}, {{ $j }})"></div>
            @endfor
        @endfor
    </div>
    
    <form action="{{ route('maps.update', $map->id) }}" method="POST">
        @csrf
        @method('PUT')
        
        <!-- Campos ocultos para armazenar os dados -->
        <input type="hidden" id="origin-data" name="origin" value="{{ json_encode($origin) }}">
        <input type="hidden" id="destination-data" name="destination" value="{{ json_encode($destination) }}">
        <input type="hidden" id="path-data" name="path" value="{{ json_encode($map->data['path'] ?? []) }}">
        
        <div class="actions">
            <a href="{{ route('maps.show', $map->id) }}" class="btn btn-cancel">Cancelar</a>
            <button type="submit" class="btn btn-save">Salvar Alterações</button>
        </div>
    </form>
    
    <script>
        let origin = @json($origin);
        let destination = @json($destination);
        let grid = @json($grid);
        let path = @json($map->data['path'] ?? []);
        let currentMode = 'origin'; 
        
        if (path && path.length > 0) {
            displayPath(path);
        }
        
        function setMode(mode) {
            currentMode = mode;
            
            document.querySelectorAll('.btn-mode').forEach(btn => {
                btn.classList.remove('active');
            });
            document.getElementById(mode + '-mode').classList.add('active');
            
            const modeNames = {
                'origin': 'Definir Origem',
                'destination': 'Definir Destino'
            };
            document.getElementById('current-mode').textContent = 'Modo atual: ' + modeNames[mode];
        }
        
        function handleCellClick(row, col) {
            if (currentMode === 'origin') {
                setOrigin(row, col);
            } else if (currentMode === 'destination') {
                setDestination(row, col);
            }
        }
        
        function setOrigin(row, col) {
            if (origin !== null) {
                removePointVisual(origin[0], origin[1], 'origin-point');
            }
            
            origin = [row, col];
            
            addPointVisual(row, col, 'origin-point');
            
            document.getElementById('origin-data').value = JSON.stringify(origin);
            
            console.log(`Origem definida em [${row},${col}]`);
            
            clearPath();
        }
        
        function setDestination(row, col) {
            if (destination !== null) {
                removePointVisual(destination[0], destination[1], 'destination-point');
            }
            
            destination = [row, col];
            
            addPointVisual(row, col, 'destination-point');
            
            document.getElementById('destination-data').value = JSON.stringify(destination);
            
            console.log(`Destino definido em [${row},${col}]`);
            
            clearPath();
        }
        
        function addPointVisual(row, col, className) {
            const cell = document.querySelector(`.grid-cell[data-row="${row}"][data-col="${col}"]`);
            cell.classList.add(className);
        }
        
        function removePointVisual(row, col, className) {
            const cell = document.querySelector(`.grid-cell[data-row="${row}"][data-col="${col}"]`);
            if (cell) {
                cell.classList.remove(className);
            }
        }
        
        function findPath() {
            if (!origin || !destination) {
                alert('Defina pontos de origem e destino antes de calcular a rota.');
                return;
            }
            
            clearPath();
            
            const rows = grid.length;
            const cols = grid[0].length;
            
            const visited = [];
            for (let i = 0; i < rows; i++) {
                visited[i] = [];
                for (let j = 0; j < cols; j++) {
                    visited[i][j] = false;
                }
            }
            
            const prev = [];
            for (let i = 0; i < rows; i++) {
                prev[i] = [];
                for (let j = 0; j < cols; j++) {
                    prev[i][j] = null;
                }
            }
            
            const directions = [
                [-1, 0], [0, 1], [1, 0], [0, -1]
            ];
            
            const queue = [];
            let front = 0;  
            
            queue.push(origin);
            visited[origin[0]][origin[1]] = true;
            
            let foundPath = false;
            
            while (front < queue.length) {
                const current = queue[front];
                front++;  
                
                if (current[0] === destination[0] && current[1] === destination[1]) {
                    foundPath = true;
                    break;
                }
                
                for (let i = 0; i < directions.length; i++) {
                    const newRow = current[0] + directions[i][0];
                    const newCol = current[1] + directions[i][1];
                    
                    if (newRow >= 0 && newRow < rows && newCol >= 0 && newCol < cols) {
                        if (!visited[newRow][newCol] && grid[newRow][newCol] === 0) {
                            visited[newRow][newCol] = true;
                            prev[newRow][newCol] = current;
                            queue.push([newRow, newCol]);
                        }
                    }
                }
            }
            
            if (foundPath) {
                const pathCells = [];
                let current = destination;
                
                while (!(current[0] === origin[0] && current[1] === origin[1])) {
                    if (!(current[0] === destination[0] && current[1] === destination[1])) {
                        pathCells.push([current[0], current[1]]);
                    }
                    current = prev[current[0]][current[1]];
                    
                    if (!current) break;
                }
                
                pathCells.reverse();
                
                displayPath(pathCells);
                
                document.getElementById('path-data').value = JSON.stringify(pathCells);
                
                console.log(`Caminho encontrado com ${pathCells.length} células`);
            } else {
                alert('Não foi possível encontrar um caminho entre a origem e o destino.');
                console.log('Caminho não encontrado');
            }
        }
        
        function displayPath(pathCells) {
            for (let i = 0; i < pathCells.length; i++) {
                const row = pathCells[i][0];
                const col = pathCells[i][1];
                addPointVisual(row, col, 'path-cell');
            }
            
            path = pathCells;
        }
        
        function clearPath() {
            if (path) {
                for (let i = 0; i < path.length; i++) {
                    const row = path[i][0];
                    const col = path[i][1];
                    removePointVisual(row, col, 'path-cell');
                }
            }
            
            path = [];
            document.getElementById('path-data').value = JSON.stringify(path);
        }
    </script>
    
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
            grid-template-columns: repeat({{ count($grid[0]) }}, 20px);
            gap: 1px;
            margin: 20px 0;
        }
        .grid-cell {
            width: 20px;
            height: 20px;
            border: 1px solid #ddd;
            cursor: pointer;
            transition: background-color 0.2s;
            position: relative;
        }
        .grid-cell:hover {
            opacity: 0.8;
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
        .controls {
            margin-bottom: 20px;
            background-color: #f9f9f9;
            padding: 10px;
            border-radius: 4px;
        }
        .btn-group {
            display: flex;
            gap: 10px;
            margin-bottom: 10px;
        }
        .btn-mode {
            padding: 8px 12px;
            background-color: #f0f0f0;
            border: 1px solid #ddd;
            border-radius: 4px;
            cursor: pointer;
        }
        .btn-mode.active {
            background-color: #2196F3;
            color: white;
            border-color: #0b7dda;
        }
        .mode-info {
            font-size: 14px;
            color: #666;
            margin-top: 5px;
        }
        .actions {
            margin-top: 20px;
            display: flex;
            gap: 10px;
        }
        .btn {
            display: inline-block;
            padding: 8px 16px;
            background-color: #2196F3;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            border: none;
            cursor: pointer;
            font-size: 14px;
        }
        .btn-cancel {
            background-color: #f44336;
        }
        .btn-save {
            background-color: #4CAF50;
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
        .btn-find-path {
            background-color: #FF5722;
            color: white;
        }
        .btn-find-path.active {
            background-color: #E64A19;
        }
    </style>
@endsection