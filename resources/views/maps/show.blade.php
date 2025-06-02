
@extends('layouts.app')

@section('content')
<div class="bg-white rounded-lg shadow-lg p-6">
    <div class="flex justify-between items-start mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 mb-2">👁️ {{ $map->name }}</h1>
            <p class="text-gray-600">Visualização completa do mapa e rota</p>
        </div>
        <div class="text-right">
            <p class="text-sm text-gray-500">Criado em</p>
            <p class="font-semibold">{{ $map->created_at->format('d/m/Y H:i') }}</p>
        </div>
    </div>

    @php
        $matrix = $map->data['matrix'] ?? [];
        $origin = $map->data['origin'] ?? null;
        $destination = $map->data['destination'] ?? null;
        $path = $map->data['path'] ?? [];
        $rows = count($matrix);
        $cols = $rows > 0 ? count($matrix[0]) : 0;
    @endphp

    <!-- Map statistics -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-gray-50 rounded-lg p-4 text-center">
            <div class="text-2xl font-bold text-gray-900">{{ $rows }}x{{ $cols }}</div>
            <div class="text-sm text-gray-600">Dimensões</div>
        </div>
        <div class="bg-green-50 rounded-lg p-4 text-center">
            <div class="text-2xl font-bold text-green-700">
                @if($origin) ✅ @else ❌ @endif
            </div>
            <div class="text-sm text-gray-600">Origem</div>
        </div>
        <div class="bg-purple-50 rounded-lg p-4 text-center">
            <div class="text-2xl font-bold text-purple-700">
                @if($destination) ✅ @else ❌ @endif
            </div>
            <div class="text-sm text-gray-600">Destino</div>
        </div>
        <div class="bg-orange-50 rounded-lg p-4 text-center">
            <div class="text-2xl font-bold text-orange-700">{{ count($path) }}</div>
            <div class="text-sm text-gray-600">Passos na Rota</div>
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
                <div class="w-4 h-4 bg-purple-500 rounded destination-point"></div>
                <span class="text-xs">🎯 Destino</span>
            </div>
            <div class="flex items-center space-x-2 bg-white p-2 rounded shadow-sm">
                <div class="w-4 h-4 bg-orange-500 rounded path-cell"></div>
                <span class="text-xs">🛤️ Caminho</span>
            </div>
        </div>
    </div>

    <!-- Enhanced grid with zoom controls -->
    <div class="bg-white border-2 border-gray-200 rounded-lg p-4 mb-6">
        <div class="flex justify-between items-center mb-4">
            <h3 class="font-semibold text-lg">🗺️ Mapa</h3>
            <div class="flex space-x-2">
                <button onclick="zoomIn()" class="px-3 py-1 bg-blue-600 text-white rounded text-sm hover:bg-blue-700 transition-colors">🔍+</button>
                <button onclick="zoomOut()" class="px-3 py-1 bg-blue-600 text-white rounded text-sm hover:bg-blue-700 transition-colors">🔍-</button>
                <button onclick="resetZoom()" class="px-3 py-1 bg-gray-600 text-white rounded text-sm hover:bg-gray-700 transition-colors">↺</button>
            </div>
        </div>
        
        <div class="overflow-auto bg-gray-50 rounded-lg p-4" id="map-container">
            <div class="grid-container" id="grid" style="display: grid; grid-template-columns: repeat({{ $cols }}, 24px); gap: 1px; margin: 20px 0; transform-origin: top left;">
                @for($i = 0; $i < $rows; $i++)
                    @for($j = 0; $j < $cols; $j++)
                        @php
                            $cellClasses = ['grid-cell', 'cell-' . $matrix[$i][$j]];
                            
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
                            
                            if ($origin && $origin[0] == $i && $origin[1] == $j) {
                                $cellClasses[] = 'origin-point';
                            }
                            if ($destination && $destination[0] == $i && $destination[1] == $j) {
                                $cellClasses[] = 'destination-point';
                            }
                        @endphp
                        
                        <div class="{{ implode(' ', $cellClasses) }}" title="Célula [{{ $i }},{{ $j }}]"></div>
                    @endfor
                @endfor
            </div>
        </div>
    </div>

    <!-- Enhanced actions -->
    <div class="flex flex-wrap gap-3">
        <a href="{{ route('maps.index') }}" 
           class="bg-gray-600 text-white px-6 py-3 rounded-lg font-medium hover:bg-gray-700 transition-colors">
            ⬅️ Voltar à Lista
        </a>
        <a href="{{ route('maps.edit', $map->id) }}" 
           class="bg-orange-600 text-white px-6 py-3 rounded-lg font-medium hover:bg-orange-700 transition-colors">
            ✏️ Definir Origem/Destino
        </a>
    </div>
</div>

<script>
    let currentZoom = 1;
    
    function zoomIn() {
        currentZoom = Math.min(currentZoom * 1.2, 3);
        updateZoom();
    }
    
    function zoomOut() {
        currentZoom = Math.max(currentZoom / 1.2, 0.5);
        updateZoom();
    }
    
    function resetZoom() {
        currentZoom = 1;
        updateZoom();
    }
    
    function updateZoom() {
        const grid = document.getElementById('grid');
        grid.style.transform = `scale(${currentZoom})`;
    }
</script>

<style>
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
    
    .cell-4 { 
        background: linear-gradient(135deg, #ef4444, #dc2626); 
        border: 1px solid #b91c1c;
        position: relative;
    }
    
    .cell-4::after {
        content: "!";
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        font-weight: bold;
        font-size: 14px;
        color: white;
        text-shadow: 1px 1px 2px rgba(0,0,0,0.5);
    }
    
    .origin-point { 
        background: linear-gradient(135deg, #84cc16, #65a30d) !important; 
        border: 2px solid #4d7c0f !important;
        box-shadow: 0 0 10px rgba(132, 204, 22, 0.5);
    }
    
    .origin-point::after {
        content: "O";
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        font-weight: bold;
        font-size: 14px;
        color: white;
        text-shadow: 1px 1px 2px rgba(0,0,0,0.5);
    }
    
    .destination-point { 
        background: linear-gradient(135deg, #a855f7, #9333ea) !important; 
        border: 2px solid #7c3aed !important;
        box-shadow: 0 0 10px rgba(168, 85, 247, 0.5);
    }
    
    .destination-point::after {
        content: "D";
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        font-weight: bold;
        font-size: 14px;
        color: white;
        text-shadow: 1px 1px 2px rgba(0,0,0,0.5);
    }
    
    .path-cell { 
        background: linear-gradient(135deg, #f97316, #ea580c) !important; 
        border: 1px solid #c2410c !important;
        position: relative;
        animation: pulse 2s infinite;
    }
    
    .path-cell::after {
        content: "•";
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        font-weight: bold;
        font-size: 16px;
        color: white;
        text-shadow: 1px 1px 2px rgba(0,0,0,0.5);
    }
    
    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.8; }
    }

    @media (max-width: 768px) {
        .grid-container {
            grid-template-columns: repeat({{ $cols }}, 20px) !important;
        }
        
        .grid-cell {
            width: 20px !important;
            height: 20px !important;
        }
        
        .cell-4::after,
        .origin-point::after,
        .destination-point::after {
            font-size: 12px !important;
        }
        
        .path-cell::after {
            font-size: 14px !important;
        }
    }

    @media (max-width: 480px) {
        .grid-container {
            grid-template-columns: repeat({{ $cols }}, 16px) !important;
        }
        
        .grid-cell {
            width: 16px !important;
            height: 16px !important;
        }
        
        .cell-4::after,
        .origin-point::after,
        .destination-point::after {
            font-size: 10px !important;
        }
        
        .path-cell::after {
            font-size: 12px !important;
        }
    }
</style>
@endsection