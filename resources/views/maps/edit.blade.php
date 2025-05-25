@extends('layouts.app')

@section('content')
    <h1>Definir Origem e Destino para: {{ $map->name }}</h1>
    
    <div class="map-legend">
        <div class="legend-item"><div class="color-box cell-0"></div> Rua (0)</div>
        <div class="legend-item"><div class="color-box cell-1"></div> Casa (1)</div>
        <div class="legend-item"><div class="color-box cell-2"></div> Prédio (2)</div>
        <div class="legend-item"><div class="color-box cell-3"></div> Praça (3)</div>
        <div class="legend-item"><div class="color-box cell-4"></div> Engarrafamento (4)</div>
        <div class="legend-item"><div class="color-box origin-point"></div> Origem</div>
        <div class="legend-item"><div class="color-box destination-point"></div> Destino</div>
        <div class="legend-item"><div class="color-box path-cell"></div> Caminho</div>
    </div>
    
    <div class="controls">
        <div class="btn-group">
            <button type="button" onclick="setMode('origin')" class="btn btn-mode active" id="origin-mode">Definir Origem</button>
            <button type="button" onclick="setMode('destination')" class="btn btn-mode" id="destination-mode">Definir Destino</button>
            <button type="button" onclick="findPath()" class="btn btn-mode btn-find-path" id="find-path">Encontrar Caminho</button>
        </div>
        <div class="btn-group" style="margin-top: 10px;">
            <button type="button" onclick="toggleTraffic()" class="btn btn-mode btn-traffic" id="toggle-traffic">Iniciar Engarrafamentos</button>
            <span id="traffic-status" style="margin-left: 10px; font-size: 14px; color: #666;">Engarrafamentos: Desativados</span>
        </div>
        <p class="mode-info" id="current-mode">Modo atual: Definir Origem</p>
        <p>Clique em uma célula do mapa para definir o ponto.</p>
    </div>
    
    <div class="grid-container">
        @for($i = 0; $i < count($grid); $i++)
            @for($j = 0; $j < count($grid[$i]); $j++)
                @php
                    $cellClasses = ['grid-cell', 'cell-' . $grid[$i][$j]];
                    
                    if ($origin && $origin[0] == $i && $origin[1] == $j) {
                        $cellClasses[] = 'origin-point';
                    }
                    if ($destination && $destination[0] == $i && $destination[1] == $j) {
                        $cellClasses[] = 'destination-point';
                    }
                    
                    $path = $map->data['path'] ?? [];
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
        
        <input type="hidden" id="origin-data" name="origin" value="{{ json_encode($origin) }}">
        <input type="hidden" id="destination-data" name="destination" value="{{ json_encode($destination) }}">
        <input type="hidden" id="path-data" name="path" value="{{ json_encode($map->data['path'] ?? []) }}">
        <input type="hidden" id="grid-data" name="grid" value="{{ json_encode($grid) }}">
        
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
        let trafficInterval = null;
        let trafficActive = false;
        
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
        
        function updateCellType(row, col, type) {
            const cell = document.querySelector(`.grid-cell[data-row="${row}"][data-col="${col}"]`);
            
            cell.classList.remove('cell-0', 'cell-1', 'cell-2', 'cell-3', 'cell-4');
            
            cell.classList.add(`cell-${type}`);
            
            grid[row][col] = type;
            
            document.getElementById('grid-data').value = JSON.stringify(grid);
        }
        
        function toggleTraffic() {
            if (trafficActive) {
                clearInterval(trafficInterval);
                trafficActive = false;
                document.getElementById('toggle-traffic').textContent = 'Iniciar Engarrafamentos';
                document.getElementById('traffic-status').textContent = 'Engarrafamentos: Desativados';
            } else {
                trafficActive = true;
                document.getElementById('toggle-traffic').textContent = 'Parar Engarrafamentos';
                document.getElementById('traffic-status').textContent = 'Engarrafamentos: Ativos';
                
                generateRandomTraffic();
                trafficInterval = setInterval(generateRandomTraffic, 3000);
            }
        }
        
        function generateRandomTraffic() {
            const rows = grid.length;
            const cols = grid[0].length;
            
            const streetCells = [];
            const pathCells = [];
            
            for (let i = 0; i < rows; i++) {
                for (let j = 0; j < cols; j++) {
                    if (grid[i][j] === 0) {
                        if (!(origin && origin[0] === i && origin[1] === j) && 
                            !(destination && destination[0] === i && destination[1] === j)) {
                            
                            let isInPath = false;
                            for (let k = 0; k < path.length; k++) {
                                if (path[k][0] === i && path[k][1] === j) {
                                    isInPath = true;
                                    pathCells.push([i, j]);
                                    break;
                                }
                            }
                            
                            if (!isInPath) {
                                streetCells.push([i, j]);
                            }
                        }
                    } 
                    else if (grid[i][j] === 4 && Math.random() < 0.3) {
                        updateCellType(i, j, 0);
                    }
                }
            }
            
            if (pathCells.length > 0 && Math.random() < 0.5) {
                const numPathTraffic = Math.min(Math.floor(Math.random() * 2) + 1, pathCells.length);
                
                for (let i = 0; i < numPathTraffic; i++) {
                    if (pathCells.length > 0) {
                        const randomIndex = Math.floor(Math.random() * pathCells.length);
                        const [row, col] = pathCells[randomIndex];
                        
                        updateCellType(row, col, 4);
                        
                        pathCells.splice(randomIndex, 1);
                    }
                }
            }
            
            const numStreetTraffic = Math.min(3, streetCells.length); 
            
            for (let i = 0; i < numStreetTraffic; i++) {
                if (streetCells.length > 0) {
                    const randomIndex = Math.floor(Math.random() * streetCells.length);
                    const [row, col] = streetCells[randomIndex];
                    
                    updateCellType(row, col, 4);
                    
                    streetCells.splice(randomIndex, 1);
                }
            }
            
            if (origin && destination) {
                findPath();
            }
            
            console.log('Engarrafamentos atualizados');
        }
        
        function findPath() {
            if (!origin || !destination) {
                alert('Defina pontos de origem e destino antes de calcular a rota.');
                return;
            }
            
            clearPath();
            
            const rows = grid.length;
            const cols = grid[0].length;
            
            const visited = Array(rows).fill().map(() => Array(cols).fill(false));
            const prev = Array(rows).fill().map(() => Array(cols).fill(null));
            const cost = Array(rows).fill().map(() => Array(cols).fill(Infinity));
            
            const directions = [[-1, 0], [0, 1], [1, 0], [0, -1]];
            
            const queue = [];
            
            queue.push({point: origin, cost: 0});
            visited[origin[0]][origin[1]] = true;
            cost[origin[0]][origin[1]] = 0;
            
            let foundPath = false;
            
            while (queue.length > 0) {
                queue.sort((a, b) => a.cost - b.cost);
                
                const {point: current} = queue.shift();
                
                if (current[0] === destination[0] && current[1] === destination[1]) {
                    foundPath = true;
                    break;
                }
                
                for (const [dx, dy] of directions) {
                    const newRow = current[0] + dx;
                    const newCol = current[1] + dy;
                    
                    if (newRow >= 0 && newRow < rows && newCol >= 0 && newCol < cols) {
                        if (grid[newRow][newCol] === 0 || grid[newRow][newCol] === 4) {
                            const moveCost = grid[newRow][newCol] === 4 ? 3 : 1;
                            const newCost = cost[current[0]][current[1]] + moveCost;
                            
                            if (newCost < cost[newRow][newCol]) {
                                cost[newRow][newCol] = newCost;
                                prev[newRow][newCol] = current;
                                
                                if (!visited[newRow][newCol]) {
                                    visited[newRow][newCol] = true;
                                    queue.push({point: [newRow, newCol], cost: newCost});
                                }
                            }
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
                alert('Não foi possível encontrar um caminho entre a origem e o destino. Talvez haja muitos engarrafamentos ou obstáculos bloqueando todas as rotas possíveis.');
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
        
        window.addEventListener('beforeunload', function() {
            if (trafficInterval) {
                clearInterval(trafficInterval);
            }
        });
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
        .cell-4 {
            background-color: #F44336;
            position: relative;
        }
        .cell-4::after {
            content: "!";
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-weight: bold;
            font-size: 12px;
            color: white;
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
        .btn-find-path {
            background-color: #FF5722;
            color: white;
        }
        .btn-find-path:hover {
            background-color: #E64A19;
        }
        .btn-traffic {
            background-color: #673AB7;
            color: white;
        }
        .btn-traffic:hover {
            background-color: #5E35B1;
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
    </style>
@endsection