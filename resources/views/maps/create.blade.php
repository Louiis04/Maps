
@extends('layouts.app')

@section('content')
<div class="bg-white rounded-lg shadow-lg p-6">
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">🎨 Criar Novo Mapa</h1>
        <p class="text-gray-600">Desenhe seu mapa personalizado definindo diferentes tipos de terreno</p>
    </div>

    <!-- Legend com design moderno -->
    <div class="bg-gray-50 rounded-lg p-4 mb-6">
        <h3 class="text-lg font-semibold mb-3">🏗️ Tipos de Terreno</h3>
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3">
            <div class="flex items-center space-x-2 bg-white p-2 rounded shadow-sm">
                <div class="w-4 h-4 bg-gray-200 border border-gray-300 rounded"></div>
                <span class="text-sm">🛣️ Rua</span>
            </div>
            <div class="flex items-center space-x-2 bg-white p-2 rounded shadow-sm">
                <div class="w-4 h-4 bg-green-500 rounded"></div>
                <span class="text-sm">🏠 Casa</span>
            </div>
            <div class="flex items-center space-x-2 bg-white p-2 rounded shadow-sm">
                <div class="w-4 h-4 bg-blue-500 rounded"></div>
                <span class="text-sm">🏢 Prédio</span>
            </div>
            <div class="flex items-center space-x-2 bg-white p-2 rounded shadow-sm">
                <div class="w-4 h-4 bg-yellow-500 rounded"></div>
                <span class="text-sm">🌳 Praça</span>
            </div>
            <div class="flex items-center space-x-2 bg-white p-2 rounded shadow-sm">
                <div class="w-4 h-4 bg-green-400 rounded"></div>
                <span class="text-sm">📍 Origem</span>
            </div>
            <div class="flex items-center space-x-2 bg-white p-2 rounded shadow-sm">
                <div class="w-4 h-4 bg-purple-500 rounded"></div>
                <span class="text-sm">🎯 Destino</span>
            </div>
        </div>
    </div>

    <!-- Controls melhorados -->
    <div class="bg-blue-50 rounded-lg p-4 mb-6">
        <div class="flex flex-wrap gap-3 mb-3">
            <button type="button" onclick="setMode('edit')" 
                    class="mode-btn bg-blue-600 text-white px-4 py-2 rounded-lg font-medium hover:bg-blue-700 transition-all active" 
                    id="edit-mode">
                🖌️ Editar Terreno
            </button>
            <button type="button" onclick="setMode('origin')" 
                    class="mode-btn bg-gray-200 text-gray-700 px-4 py-2 rounded-lg font-medium hover:bg-gray-300 transition-all" 
                    id="origin-mode">
                📍 Definir Origem
            </button>
            <button type="button" onclick="setMode('destination')" 
                    class="mode-btn bg-gray-200 text-gray-700 px-4 py-2 rounded-lg font-medium hover:bg-gray-300 transition-all" 
                    id="destination-mode">
                🎯 Definir Destino
            </button>
        </div>
        <p class="text-sm text-blue-700" id="current-mode">Modo atual: Editar Terreno</p>
        <p class="text-xs text-blue-600 mt-1">💡 Dica: Clique nas células para alternar entre os tipos de terreno</p>
    </div>

    <!-- Grid responsivo -->
    <div class="bg-white border-2 border-gray-200 rounded-lg p-4 mb-6 overflow-auto">
        <div class="grid-container" style="display: grid; grid-template-columns: repeat({{ count($grid[0]) }}, 24px); gap: 1px;">
            @for($i = 0; $i < count($grid); $i++)
                @for($j = 0; $j < count($grid[$i]); $j++)
                    <div class="grid-cell w-6 h-6 border border-gray-300 rounded-sm cursor-pointer transition-all hover:scale-110 hover:z-10 hover:shadow-lg cell-{{ $grid[$i][$j] }}" 
                         data-row="{{ $i }}" 
                         data-col="{{ $j }}"
                         onclick="handleCellClick({{ $i }}, {{ $j }})"
                         title="Célula [{{ $i }},{{ $j }}]"></div>
                @endfor
            @endfor
        </div>
    </div>

    <!-- Form melhorado -->
    <form action="{{ route('maps.store') }}" method="POST" class="bg-white border rounded-lg p-6">
        @csrf
        <div class="mb-6">
            <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                📝 Nome do Mapa
            </label>
            <input type="text" 
                   id="name" 
                   name="name" 
                   required
                   placeholder="Ex: Mapa do Centro da Cidade"
                   class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
        </div>
        
        <input type="hidden" id="grid-data" name="grid">
        <input type="hidden" id="origin-data" name="origin">
        <input type="hidden" id="destination-data" name="destination">
        
        <div class="flex space-x-3">
            <button type="submit" 
                    class="flex-1 bg-green-600 text-white py-3 px-6 rounded-lg font-medium hover:bg-green-700 transition-colors">
                💾 Salvar Mapa
            </button>
            <a href="{{ route('maps.index') }}" 
               class="flex-1 bg-gray-300 text-gray-700 py-3 px-6 rounded-lg font-medium text-center hover:bg-gray-400 transition-colors">
                ❌ Cancelar
            </a>
        </div>
    </form>
</div>

<!-- Scripts mantendo a mesma lógica -->
<script>
    let grid = @json($grid);
    let currentMode = 'edit';
    let origin = null;
    let destination = null;

    function setMode(mode) {
        currentMode = mode;
        
        // Reset all buttons
        document.querySelectorAll('.mode-btn').forEach(btn => {
            btn.className = 'mode-btn bg-gray-200 text-gray-700 px-4 py-2 rounded-lg font-medium hover:bg-gray-300 transition-all';
        });
        
        // Highlight active button
        const activeBtn = document.getElementById(mode + '-mode');
        activeBtn.className = 'mode-btn bg-blue-600 text-white px-4 py-2 rounded-lg font-medium hover:bg-blue-700 transition-all active';
        
        const modeNames = {
            'edit': '🖌️ Editar Terreno',
            'origin': '📍 Definir Origem', 
            'destination': '🎯 Definir Destino'
        };
        document.getElementById('current-mode').textContent = 'Modo atual: ' + modeNames[mode];
    }

    function handleCellClick(row, col) {
        if (currentMode === 'edit') {
            // Cycle through cell types
            grid[row][col] = (grid[row][col] + 1) % 4;
            updateCellDisplay(row, col);
        } else if (currentMode === 'origin') {
            // Clear previous origin
            if (origin) {
                document.querySelector(`[data-row="${origin[0]}"][data-col="${origin[1]}"]`).classList.remove('origin-point');
            }
            origin = [row, col];
            document.querySelector(`[data-row="${row}"][data-col="${col}"]`).classList.add('origin-point');
        } else if (currentMode === 'destination') {
            // Clear previous destination
            if (destination) {
                document.querySelector(`[data-row="${destination[0]}"][data-col="${destination[1]}"]`).classList.remove('destination-point');
            }
            destination = [row, col];
            document.querySelector(`[data-row="${row}"][data-col="${col}"]`).classList.add('destination-point');
        }
        
        updateFormData();
    }

    function updateCellDisplay(row, col) {
        const cell = document.querySelector(`[data-row="${row}"][data-col="${col}"]`);
        cell.className = cell.className.replace(/cell-\d+/, `cell-${grid[row][col]}`);
    }

    function updateFormData() {
        document.getElementById('grid-data').value = JSON.stringify(grid);
        document.getElementById('origin-data').value = origin ? JSON.stringify(origin) : '';
        document.getElementById('destination-data').value = destination ? JSON.stringify(destination) : '';
    }

    // Initialize form data
    updateFormData();
</script>

<style>
    .grid-cell {
        transition: all 0.2s ease;
        position: relative;
    }
    
    .grid-cell:hover {
        transform: scale(1.1);
        z-index: 10;
        box-shadow: 0 2px 8px rgba(0,0,0,0.3);
    }

    .cell-0 { 
        background: linear-gradient(135deg, #f3f4f6, #e5e7eb); 
        border: 1px solid #d1d5db;
    }
    
    .cell-1 { 
        background: linear-gradient(135deg, #10b981, #059669); 
        border: 1px solid #047857;
    }
    
    .cell-2 { 
        background: linear-gradient(135deg, #3b82f6, #2563eb); 
        border: 1px solid #1d4ed8;
    }
    
    .cell-3 { 
        background: linear-gradient(135deg, #f59e0b, #d97706); 
        border: 1px solid #b45309;
    }
    
    .origin-point { 
        background: linear-gradient(135deg, #84cc16, #65a30d) !important; 
        border: 2px solid #4d7c0f !important;
        box-shadow: 0 0 10px rgba(132, 204, 22, 0.5);
    }
    
    .destination-point { 
        background: linear-gradient(135deg, #a855f7, #9333ea) !important; 
        border: 2px solid #7c3aed !important;
        box-shadow: 0 0 10px rgba(168, 85, 247, 0.5);
    }

    .mode-btn.active {
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.3);
    }

    @media (max-width: 768px) {
        .grid-container {
            grid-template-columns: repeat({{ count($grid[0]) }}, 20px) !important;
        }
        
        .grid-cell {
            width: 20px !important;
            height: 20px !important;
        }
    }

    @media (max-width: 480px) {
        .grid-container {
            grid-template-columns: repeat({{ count($grid[0]) }}, 16px) !important;
        }
        
        .grid-cell {
            width: 16px !important;
            height: 16px !important;
        }
    }
</style>
@endsection