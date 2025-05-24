@extends('layouts.app')

@section('content')
    <h1>Criar Novo Mapa</h1>
    
    <div class="map-legend">
        <div class="legend-item"><div class="color-box cell-0"></div> Rua (0)</div>
        <div class="legend-item"><div class="color-box cell-1"></div> Casa (1)</div>
        <div class="legend-item"><div class="color-box cell-2"></div> Prédio (2)</div>
        <div class="legend-item"><div class="color-box cell-3"></div> Praça (3)</div>
        <div class="legend-item"><div class="color-box origin-point"></div> Origem</div>
        <div class="legend-item"><div class="color-box destination-point"></div> Destino</div>
    </div>
    
    <div class="controls">
        <p>Clique nas células para alternar entre os tipos (Rua → Casa → Prédio → Praça → Rua)</p>
        <div class="btn-group">
            <button type="button" onclick="setMode('edit')" class="btn btn-mode active" id="edit-mode">Editar Terreno</button>
            <button type="button" onclick="setMode('origin')" class="btn btn-mode" id="origin-mode">Definir Origem</button>
            <button type="button" onclick="setMode('destination')" class="btn btn-mode" id="destination-mode">Definir Destino</button>
        </div>
        <p class="mode-info" id="current-mode">Modo atual: Editar Terreno</p>
    </div>
    
    <div class="grid-container">
        @for($i = 0; $i < count($grid); $i++)
            @for($j = 0; $j < count($grid[$i]); $j++)
                <div class="grid-cell cell-{{ $grid[$i][$j] }}" 
                     data-row="{{ $i }}" 
                     data-col="{{ $j }}"
                     onclick="handleCellClick({{ $i }}, {{ $j }})"></div>
            @endfor
        @endfor
    </div>
    
    <form action="{{ route('maps.store') }}" method="POST">
        @csrf
        <div class="form-group">
            <label for="name">Nome do Mapa:</label>
            <input type="text" id="name" name="name" required>
        </div>
        
        <input type="hidden" id="grid-data" name="grid">
        <input type="hidden" id="origin-data" name="origin">
        <input type="hidden" id="destination-data" name="destination">
        
        <button type="submit">Salvar Mapa</button>
    </form>
    
    <script>
        let grid = @json($grid);
        let origin = null;
        let destination = null;
        let currentMode = 'edit'; 
        
        document.getElementById('grid-data').value = JSON.stringify(grid);
        document.getElementById('origin-data').value = JSON.stringify(origin);
        document.getElementById('destination-data').value = JSON.stringify(destination);
        
        function setMode(mode) {
            currentMode = mode;
            
            document.querySelectorAll('.btn-mode').forEach(btn => {
                btn.classList.remove('active');
            });
            document.getElementById(mode + '-mode').classList.add('active');
            
            const modeNames = {
                'edit': 'Editar Terreno',
                'origin': 'Definir Origem',
                'destination': 'Definir Destino'
            };
            document.getElementById('current-mode').textContent = 'Modo atual: ' + modeNames[mode];
        }
        
        function handleCellClick(row, col) {
            if (currentMode === 'edit') {
                toggleCell(row, col);
            } else if (currentMode === 'origin') {
                setOrigin(row, col);
            } else if (currentMode === 'destination') {
                setDestination(row, col);
            }
        }
        
        function toggleCell(row, col) {
            grid[row][col] = (grid[row][col] + 1) % 4;
            
            updateCellVisual(row, col);
            
            document.getElementById('grid-data').value = JSON.stringify(grid);
            
            console.log(`Alterado célula [${row},${col}] para ${grid[row][col]}`);
        }
        
        function setOrigin(row, col) {
            if (origin !== null) {
                removePointVisual(origin[0], origin[1], 'origin-point');
            }
            
            origin = [row, col];
            
            addPointVisual(row, col, 'origin-point');
            
            document.getElementById('origin-data').value = JSON.stringify(origin);
            
            console.log(`Origem definida em [${row},${col}]`);
            
            setMode('edit');
        }
        
        function setDestination(row, col) {
            if (destination !== null) {
                removePointVisual(destination[0], destination[1], 'destination-point');
            }
            
            destination = [row, col];
            
            addPointVisual(row, col, 'destination-point');
            
            document.getElementById('destination-data').value = JSON.stringify(destination);
            
            console.log(`Destino definido em [${row},${col}]`);
            
            setMode('edit');
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
        
        function updateCellVisual(row, col) {
            const cell = document.querySelector(`.grid-cell[data-row="${row}"][data-col="${col}"]`);
            
            cell.classList.remove('cell-0', 'cell-1', 'cell-2', 'cell-3');
            
            cell.classList.add(`cell-${grid[row][col]}`);
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
    </style>
@endsection