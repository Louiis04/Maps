
@extends('layouts.app')

@section('content')
<div class="bg-white rounded-lg shadow-lg p-6">
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">🎯 Definir Rota: {{ $map->name }}</h1>
        <p class="text-gray-600">Configure origem, destino e visualize o melhor caminho</p>
    </div>

    <!-- Status panel -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-green-50 border border-green-200 rounded-lg p-4">
            <h3 class="font-semibold text-green-800">📍 Origem</h3>
            <p class="text-sm text-green-600" id="origin-status">
                @if($origin) 
                    Definida em [{{ $origin[0] }}, {{ $origin[1] }}]
                @else 
                    Não definida
                @endif
            </p>
        </div>
        <div class="bg-purple-50 border border-purple-200 rounded-lg p-4">
            <h3 class="font-semibold text-purple-800">🎯 Destino</h3>
            <p class="text-sm text-purple-600" id="destination-status">
                @if($destination) 
                    Definido em [{{ $destination[0] }}, {{ $destination[1] }}]
                @else 
                    Não definido
                @endif
            </p>
        </div>
        <div class="bg-orange-50 border border-orange-200 rounded-lg p-4">
            <h3 class="font-semibold text-orange-800">🚦 Tráfego</h3>
            <p class="text-sm text-orange-600" id="traffic-status">Engarrafamentos: Desativados</p>
        </div>
    </div>

    <!-- Enhanced legend -->
    <div class="bg-gray-50 rounded-lg p-4 mb-6">
        <h3 class="text-lg font-semibold mb-3">🗺️ Legenda</h3>
        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-8 gap-3">
            <div class="flex items-center space-x-2 bg-white p-2 rounded shadow-sm">
                <div class="w-4 h-4 bg-gray-200 border border-gray-300 rounded cell-0"></div>
                <span class="text-xs">🛣️ Rua (0)</span>
            </div>
            <div class="flex items-center space-x-2 bg-white p-2 rounded shadow-sm">
                <div class="w-4 h-4 bg-green-500 rounded cell-1"></div>
                <span class="text-xs">🏠 Casa (1)</span>
            </div>
            <div class="flex items-center space-x-2 bg-white p-2 rounded shadow-sm">
                <div class="w-4 h-4 bg-blue-500 rounded cell-2"></div>
                <span class="text-xs">🏢 Prédio (2)</span>
            </div>
            <div class="flex items-center space-x-2 bg-white p-2 rounded shadow-sm">
                <div class="w-4 h-4 bg-yellow-500 rounded cell-3"></div>
                <span class="text-xs">🌳 Praça (3)</span>
            </div>
            <div class="flex items-center space-x-2 bg-white p-2 rounded shadow-sm">
                <div class="w-4 h-4 bg-red-500 rounded cell-4"></div>
                <span class="text-xs">🚨 Engarrafamento (4)</span>
            </div>
            <div class="flex items-center space-x-2 bg-white p-2 rounded shadow-sm">
                <div class="w-4 h-4 bg-green-400 rounded origin-point"></div>
                <span class="text-xs">📍 Origem</span>
            </div>
            <div class="flex items-center space-x-2 bg-white p-2 rounded shadow-sm">
                <div class="w-4 h-4 bg-purple-400 rounded destination-point"></div>
                <span class="text-xs">🎯 Destino</span>
            </div>
            <div class="flex items-center space-x-2 bg-white p-2 rounded shadow-sm">
                <div class="w-4 h-4 bg-orange-500 rounded path-cell"></div>
                <span class="text-xs">🛤️ Caminho</span>
            </div>
        </div>
    </div>

    <!-- Enhanced controls -->
    <div class="bg-blue-50 rounded-lg p-4 mb-6">
        <div class="grid grid-cols-2 md:grid-cols-3 gap-3 mb-3">
            <button type="button" onclick="setMode('origin')" 
                    class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                📍 Definir Origem
            </button>
            <button type="button" onclick="setMode('destination')" 
                    class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                🎯 Definir Destino
            </button>
            <button type="button" onclick="findPath()" 
                    class="bg-orange-600 hover:bg-orange-700 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                🚀 Calcular Rota
            </button>
        </div>
        <div class="flex flex-wrap gap-3 mb-3">
            <button type="button" onclick="clearPath()" 
                    class="bg-gray-600 hover:bg-gray-700 text-white px-3 py-1 rounded font-medium transition-colors">
                🧹 Limpar Caminho
            </button>
            <button type="button" onclick="toggleTraffic()" 
                    class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded font-medium transition-colors">
                🚦 Toggle Tráfego
            </button>
            <button type="button" onclick="startCarAnimation()" 
                    class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded font-medium transition-colors">
                🚗 Simular Viagem
            </button>
        </div>
    </div>

    <!-- Enhanced grid -->
    <div class="bg-white border-2 border-gray-200 rounded-lg p-4 mb-6">
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
    </div>

    <!-- Form para salvar dados -->
    <form action="{{ route('maps.update', $map->id) }}" method="POST">
        @csrf
        @method('PUT')
        <input type="hidden" name="origin" id="origin-data" value="{{ json_encode($origin) }}">
        <input type="hidden" name="destination" id="destination-data" value="{{ json_encode($destination) }}">
        <input type="hidden" name="path" id="path-data" value="{{ json_encode($map->data['path'] ?? []) }}">
        <input type="hidden" name="grid" id="grid-data" value="{{ json_encode($grid) }}">
        
        <div class="flex gap-3">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-medium transition-colors">
                💾 Salvar Configurações
            </button>
            <a href="{{ route('maps.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-2 rounded-lg font-medium transition-colors">
                ⬅️ Voltar
            </a>
        </div>
    </form>
</div>

<style>
    .grid-container {
        display: grid;
        grid-template-columns: repeat({{ count($grid[0]) }}, 24px);
        gap: 1px;
        justify-content: center;
        background-color: #f3f4f6;
        padding: 8px;
        border-radius: 8px;
        position: relative;
    }

    .grid-cell {
        width: 24px;
        height: 24px;
        border: 1px solid #d1d5db;
        transition: all 0.2s ease;
        cursor: pointer;
        border-radius: 2px;
        position: relative;
    }
    
    .grid-cell:hover {
        transform: scale(1.1);
        z-index: 10;
        box-shadow: 0 2px 8px rgba(0,0,0,0.3);
    }

    .cell-0 { background-color: #f9fafb; }
    .cell-1 { background-color: #10b981; }
    .cell-2 { background-color: #3b82f6; }
    .cell-3 { background-color: #f59e0b; }
    .cell-4 { 
        background: linear-gradient(135deg, #ef4444, #dc2626);
        border: 1px solid #b91c1c;
        animation: traffic-blink 1s infinite alternate;
    }
    
    .path-cell {
        background: linear-gradient(45deg, #f97316, #ea580c) !important;
        border: 2px solid #c2410c !important;
        position: relative;
        overflow: hidden;
    }
    
    .path-animated {
        animation: pathGlow 0.8s ease-in-out;
    }
    
    /* 🚗 CARRO ANIMADO */
    .car {
        position: absolute;
        width: 20px;
        height: 20px;
        background: #1f2937;
        border-radius: 50%;
        z-index: 100;
        transition: all 0.5s ease-in-out;
        border: 2px solid #f59e0b;
        box-shadow: 0 0 10px rgba(245, 158, 11, 0.8);
    }
    
    .car::before {
        content: '🚗';
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        font-size: 12px;
    }
    
    .car.moving {
        animation: carBounce 0.5s ease-in-out;
    }
    
    @keyframes carBounce {
        0%, 100% { transform: scale(1) rotate(0deg); }
        50% { transform: scale(1.2) rotate(5deg); }
    }
    
    @keyframes pathGlow {
        0% { 
            transform: scale(0.8);
            opacity: 0;
            box-shadow: 0 0 0 rgba(249, 115, 22, 0.7);
        }
        50% { 
            transform: scale(1.2);
            opacity: 1;
            box-shadow: 0 0 20px rgba(249, 115, 22, 0.7);
        }
        100% { 
            transform: scale(1);
            opacity: 1;
            box-shadow: 0 0 10px rgba(249, 115, 22, 0.3);
        }
    }
    
    .origin-point {
        background: radial-gradient(circle, #10b981, #059669) !important;
        border: 3px solid #047857 !important;
        animation: pulse 2s infinite;
    }
    
    .destination-point {
        background: radial-gradient(circle, #8b5cf6, #7c3aed) !important;
        border: 3px solid #6d28d9 !important;
        animation: pulse 2s infinite;
    }
    
    @keyframes pulse {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.1); }
    }
    
    @keyframes traffic-blink {
        0% { opacity: 1; }
        100% { opacity: 0.7; }
    }
    
    .animate-fade-in {
        animation: fadeIn 0.5s ease-in;
    }
    
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>

<script>
    let origin = @json($origin);
    let destination = @json($destination);
    let grid = @json($grid);
    let path = @json($map->data['path'] ?? []);
    let currentMode = 'origin'; 
    let trafficInterval = null;
    let trafficActive = false;
    let currentSummary = null; // ✅ Controle da notificação única
    let carElement = null; // ✅ Elemento do carro
    let isCarMoving = false; // ✅ Estado do carro
    
    if (path && path.length > 0) {
        displayPath(path);
    }
    
    function setMode(mode) {
        currentMode = mode;
        console.log(`Modo alterado para: ${mode}`);
    }
    
    function handleCellClick(row, col) {
        if (currentMode === 'origin') {
            setOrigin(row, col);
        } else if (currentMode === 'destination') {
            setDestination(row, col);
        }
    }
    
    function setOrigin(row, col) {
        if (origin) {
            removePointVisual(origin[0], origin[1], 'origin-point');
        }
        
        origin = [row, col];
        addPointVisual(row, col, 'origin-point');
        
        document.getElementById('origin-data').value = JSON.stringify(origin);
        document.getElementById('origin-status').textContent = `Definida em [${row}, ${col}]`;
        
        console.log(`Origem definida em [${row},${col}]`);
        
        clearPath();
        removeCar(); // ✅ Remove carro ao redefinir origem
    }
    
    function setDestination(row, col) {
        if (destination) {
            removePointVisual(destination[0], destination[1], 'destination-point');
        }
        
        destination = [row, col];
        addPointVisual(row, col, 'destination-point');
        
        document.getElementById('destination-data').value = JSON.stringify(destination);
        document.getElementById('destination-status').textContent = `Definido em [${row}, ${col}]`;
        
        console.log(`Destino definido em [${row},${col}]`);
        
        clearPath();
        removeCar(); // ✅ Remove carro ao redefinir destino
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
    
    function clearPath() {
        const pathCells = document.querySelectorAll('.path-cell');
        for (let i = 0; i < pathCells.length; i++) {
            pathCells[i].classList.remove('path-cell', 'path-animated');
        }
        path = [];
        document.getElementById('path-data').value = JSON.stringify([]);
        
        // ✅ Remove notificação anterior
        if (currentSummary && currentSummary.parentElement) {
            currentSummary.remove();
            currentSummary = null;
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
            document.getElementById('traffic-status').textContent = 'Engarrafamentos: Desativados';
            console.log('Tráfego desativado');
        } else {
            trafficActive = true;
            generateRandomTraffic();
            trafficInterval = setInterval(generateRandomTraffic, 3000);
            document.getElementById('traffic-status').textContent = 'Engarrafamentos: Ativados';
            console.log('Tráfego ativado');
        }
    }
    
    // Geração de Tráfego Aleatório
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
                                pathCells[pathCells.length] = [i, j];
                                break;
                            }
                        }
                        
                        if (!isInPath) {
                            streetCells[streetCells.length] = [i, j];
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
                    const selectedCell = pathCells[randomIndex];
                    const row = selectedCell[0];
                    const col = selectedCell[1];
                    
                    updateCellType(row, col, 4);
                    
                    for (let j = randomIndex; j < pathCells.length - 1; j++) {
                        pathCells[j] = pathCells[j + 1];
                    }
                    pathCells.length--;
                }
            }
        }
        
        const numStreetTraffic = Math.min(3, streetCells.length); 
        
        for (let i = 0; i < numStreetTraffic; i++) {
            if (streetCells.length > 0) {
                const randomIndex = Math.floor(Math.random() * streetCells.length);
                const selectedCell = streetCells[randomIndex];
                const row = selectedCell[0];
                const col = selectedCell[1];
                
                updateCellType(row, col, 4);
                
                for (let j = randomIndex; j < streetCells.length - 1; j++) {
                    streetCells[j] = streetCells[j + 1];
                }
                streetCells.length--;
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
        
        showLoadingIndicator();
        clearPath();
        
        const rows = grid.length;
        const cols = grid[0].length;
        

        // Arrays bidimensionais para o algoritmo de busca de caminho
        const visited = [];
        const prev = [];
        const cost = [];
        
        for (let i = 0; i < rows; i++) {
            visited[i] = [];
            prev[i] = [];
            cost[i] = [];
            for (let j = 0; j < cols; j++) {
                visited[i][j] = false;
                prev[i][j] = null;
                cost[i][j] = 999999;
            }
        }
        
        const queueRow = [];
        const queueCol = [];
        const queueCost = [];
        let queueSize = 0;
        
        function insertQueue(row, col, costValue) {
            queueRow[queueSize] = row;
            queueCol[queueSize] = col;
            queueCost[queueSize] = costValue;
            queueSize++;
        }
        
        function sortQueue() {
            for (let i = 0; i < queueSize - 1; i++) {
                for (let j = 0; j < queueSize - 1 - i; j++) {
                    if (queueCost[j] > queueCost[j + 1]) {
                        let tempCost = queueCost[j];
                        let tempRow = queueRow[j];
                        let tempCol = queueCol[j];
                        
                        queueCost[j] = queueCost[j + 1];
                        queueRow[j] = queueRow[j + 1];
                        queueCol[j] = queueCol[j + 1];
                        
                        queueCost[j + 1] = tempCost;
                        queueRow[j + 1] = tempRow;
                        queueCol[j + 1] = tempCol;
                    }
                }
            }
        }
        
        function removeFromQueue() {
            if (queueSize === 0) return null;
            
            const row = queueRow[0];
            const col = queueCol[0];
            
            for (let i = 0; i < queueSize - 1; i++) {
                queueRow[i] = queueRow[i + 1];
                queueCol[i] = queueCol[i + 1];
                queueCost[i] = queueCost[i + 1];
            }
            queueSize--;
            
            return [row, col];
        }
        
        const directionRow = [-1, 0, 1, 0];
        const directionCol = [0, 1, 0, -1];
        
        insertQueue(origin[0], origin[1], 0);
        visited[origin[0]][origin[1]] = true;
        cost[origin[0]][origin[1]] = 0;
        
        let foundPath = false;
        
        while (queueSize > 0) {
            sortQueue();
            
            const current = removeFromQueue();
            const currentRow = current[0];
            const currentCol = current[1];
            
            if (currentRow === destination[0] && currentCol === destination[1]) {
                foundPath = true;
                break;
            }
            
            for (let d = 0; d < 4; d++) {
                const newRow = currentRow + directionRow[d];
                const newCol = currentCol + directionCol[d];
                
                if (newRow >= 0 && newRow < rows && newCol >= 0 && newCol < cols) {
                    if (grid[newRow][newCol] === 0 || grid[newRow][newCol] === 4) {
                        const moveCost = grid[newRow][newCol] === 4 ? 3 : 1;
                        const newCost = cost[currentRow][currentCol] + moveCost;
                        
                        if (newCost < cost[newRow][newCol]) {
                            cost[newRow][newCol] = newCost;
                            prev[newRow][newCol] = [currentRow, currentCol];
                            
                            if (!visited[newRow][newCol]) {
                                visited[newRow][newCol] = true;
                                insertQueue(newRow, newCol, newCost);
                            }
                        }
                    }
                }
            }
        }
        
        hideLoadingIndicator();
        
        if (foundPath) {
            const pathRows = [];
            const pathCols = [];
            let pathSize = 0;
            
            let currentRow = destination[0];
            let currentCol = destination[1];
            
            while (!(currentRow === origin[0] && currentCol === origin[1])) {
                if (!(currentRow === destination[0] && currentCol === destination[1])) {
                    pathRows[pathSize] = currentRow;
                    pathCols[pathSize] = currentCol;
                    pathSize++;
                }
                
                const prevPoint = prev[currentRow][currentCol];
                if (!prevPoint) break;
                
                currentRow = prevPoint[0];
                currentCol = prevPoint[1];
            }
            
            const finalPath = [];
            for (let i = pathSize - 1; i >= 0; i--) {
                finalPath[pathSize - 1 - i] = [pathRows[i], pathCols[i]];
            }
            
            displayPath(finalPath);
            document.getElementById('path-data').value = JSON.stringify(finalPath);
            
            console.log(`Caminho encontrado com ${pathSize} células`);
        } else {
            alert('Não foi possível encontrar um caminho entre a origem e o destino.');
            console.log('Caminho não encontrado');
        }
    }
    
    function displayPath(pathCells) {
        clearPath();
        
        for (let i = 0; i < pathCells.length; i++) {
            setTimeout(() => {
                const row = pathCells[i][0];
                const col = pathCells[i][1];
                
                const cell = document.querySelector(`[data-row="${row}"][data-col="${col}"]`);
                cell.classList.add('path-cell', 'path-animated');
                
                // ✅ Mostra notificação apenas quando termina de desenhar o caminho
                if (i === pathCells.length - 1) {
                    console.log('🎯 Rota calculada com sucesso!');
                    showPathSummary(pathCells);
                }
            }, i * 100);
        }
        
        path = pathCells;
    }
    
    // ✅ NOTIFICAÇÃO ÚNICA
    function showPathSummary(pathCells) {
        // Remove notificação anterior se existir
        if (currentSummary && currentSummary.parentElement) {
            currentSummary.remove();
        }
        
        currentSummary = document.createElement('div');
        currentSummary.className = 'path-summary';
        currentSummary.innerHTML = `
            <div class="bg-green-100 border border-green-300 rounded-lg p-4 mb-4 animate-fade-in">
                <h4 class="font-bold text-green-800">🛤️ Rota Encontrada!</h4>
                <p class="text-green-700">
                    📏 Distância: ${pathCells.length} células<br>
                    ⏱️ Tempo estimado: ${pathCells.length * 0.1} minutos
                </p>
            </div>
        `;
        
        const gridContainer = document.querySelector('.grid-container').parentElement;
        gridContainer.insertBefore(currentSummary, gridContainer.firstChild);
        
        // Remove após 5 segundos
        setTimeout(() => {
            if (currentSummary && currentSummary.parentElement) {
                currentSummary.remove();
                currentSummary = null;
            }
        }, 5000);
    }
    
    // ✅ SISTEMA DE CARRO ANIMADO
    function createCar() {
        if (!carElement) {
            carElement = document.createElement('div');
            carElement.className = 'car';
            document.querySelector('.grid-container').appendChild(carElement);
        }
        return carElement;
    }
    
    function removeCar() {
        if (carElement && carElement.parentElement) {
            carElement.remove();
            carElement = null;
        }
        isCarMoving = false;
    }
    
    function positionCar(row, col) {
        if (!carElement) return;
        
        const cell = document.querySelector(`[data-row="${row}"][data-col="${col}"]`);
        if (cell) {
            const rect = cell.getBoundingClientRect();
            const containerRect = document.querySelector('.grid-container').getBoundingClientRect();
            
            const x = rect.left - containerRect.left + 2;
            const y = rect.top - containerRect.top + 2;
            
            carElement.style.left = x + 'px';
            carElement.style.top = y + 'px';
        }
    }
    
    function startCarAnimation() {
        if (!origin || !destination || path.length === 0) {
            alert('Calcule uma rota primeiro!');
            return;
        }
        
        if (isCarMoving) {
            alert('Carro já está em movimento!');
            return;
        }
        
        isCarMoving = true;
        
        // Cria e posiciona carro na origem
        const car = createCar();
        positionCar(origin[0], origin[1]);
        
        // Array completo: origem + caminho + destino
        const fullPath = [];
        fullPath[0] = origin;
        
        for (let i = 0; i < path.length; i++) {
            fullPath[fullPath.length] = path[i];
        }
        
        fullPath[fullPath.length] = destination;
        
        let currentStep = 0;
        
        function moveToNextPosition() {
            if (currentStep >= fullPath.length - 1) {
                // Chegou ao destino
                setTimeout(() => {
                    removeCar();
                    alert('🎯 Viagem concluída! Chegou ao destino.');
                }, 500);
                return;
            }
            
            currentStep++;
            const nextPosition = fullPath[currentStep];
            
            car.classList.add('moving');
            positionCar(nextPosition[0], nextPosition[1]);
            
            setTimeout(() => {
                car.classList.remove('moving');
            }, 300);
            
            // Continue para próxima posição
            setTimeout(moveToNextPosition, 600);
        }
        
        // Inicia movimento após 1 segundo
        setTimeout(moveToNextPosition, 1000);
    }
    
    function showLoadingIndicator() {
        const indicator = document.createElement('div');
        indicator.id = 'loading-indicator';
        indicator.className = 'fixed top-4 right-4 bg-blue-600 text-white px-4 py-2 rounded-lg shadow-lg z-50';
        indicator.innerHTML = '🔄 Calculando rota...';
        document.body.appendChild(indicator);
    }
    
    function hideLoadingIndicator() {
        const indicator = document.getElementById('loading-indicator');
        if (indicator) {
            indicator.remove();
        }
    }
</script>
@endsection