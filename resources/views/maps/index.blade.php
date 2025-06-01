
@extends('layouts.app')

@section('content')
<div class="mb-6">
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Meus Mapas</h1>
            <p class="text-gray-600">Gerencie e visualize todos os seus mapas</p>
        </div>
    </div>
</div>

<div class="map-list">
    @if(count($maps) > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @for($i = 0; $i < count($maps); $i++)
                <div class="map-item bg-white rounded-lg shadow-lg overflow-hidden hover:shadow-xl transition-shadow border border-gray-200">
                    <!-- Map preview header -->
                    <div class="h-32 bg-gradient-to-br from-blue-50 to-purple-50 p-4 flex items-center justify-center relative">
                        <div class="text-4xl">🗺️</div>
                        <div class="absolute top-2 right-2 bg-white px-2 py-1 rounded-full text-xs font-medium text-gray-600">
                            ID: {{ $maps[$i]->id }}
                        </div>
                    </div>
                    
                    <!-- Card content -->
                    <div class="p-4">
                        <h3 class="text-lg font-bold text-gray-900 mb-2 truncate" title="{{ $maps[$i]->name }}">
                            {{ $maps[$i]->name }}
                        </h3>
                        
                        <div class="flex items-center text-gray-500 text-sm mb-3">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Criado em: {{ $maps[$i]->created_at->format('d/m/Y H:i') }}
                        </div>
                        
                        <!-- Status indicators em grid compacto -->
                        <div class="grid grid-cols-3 gap-2 mb-4">
                            <div class="text-center">
                                <div class="text-lg">
                                    @if(isset($maps[$i]->data['origin'])) 
                                        <span class="text-green-600">✅</span>
                                    @else 
                                        <span class="text-red-500">❌</span>
                                    @endif
                                </div>
                                <div class="text-xs text-gray-600">Origem</div>
                            </div>
                            <div class="text-center">
                                <div class="text-lg">
                                    @if(isset($maps[$i]->data['destination'])) 
                                        <span class="text-purple-600">✅</span>
                                    @else 
                                        <span class="text-red-500">❌</span>
                                    @endif
                                </div>
                                <div class="text-xs text-gray-600">Destino</div>
                            </div>
                            <div class="text-center">
                                <div class="text-lg font-bold text-orange-600">
                                    {{ isset($maps[$i]->data['path']) ? count($maps[$i]->data['path']) : 0 }}
                                </div>
                                <div class="text-xs text-gray-600">Passos</div>
                            </div>
                        </div>
                        
                        <!-- Map dimensions -->
                        @php
                            $matrix = $maps[$i]->data['matrix'] ?? [];
                            $rows = count($matrix);
                            $cols = $rows > 0 ? count($matrix[0]) : 0;
                        @endphp
                        
                        <div class="bg-gray-50 rounded-lg p-2 mb-4">
                            <div class="flex justify-between items-center text-sm">
                                <span class="text-gray-600">Dimensões:</span>
                                <span class="font-medium text-gray-900">{{ $rows }}x{{ $cols }}</span>
                            </div>
                            @if(isset($maps[$i]->data['path']) && count($maps[$i]->data['path']) > 0)
                                <div class="flex justify-between items-center text-sm mt-1">
                                    <span class="text-gray-600">Status:</span>
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        🛤️ Rota Definida
                                    </span>
                                </div>
                            @else
                                <div class="flex justify-between items-center text-sm mt-1">
                                    <span class="text-gray-600">Status:</span>
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                        ⚠️ Incompleto
                                    </span>
                                </div>
                            @endif
                        </div>
                        
                        <!-- Actions -->
                        <div class="flex space-x-2">
                            <a href="{{ route('maps.show', $maps[$i]->id) }}" 
                               class="flex-1 bg-blue-600 text-white text-center py-2 px-3 rounded-lg text-sm font-medium hover:bg-blue-700 transition-colors">
                                👁️ Visualizar
                            </a>
                            <a href="{{ route('maps.edit', $maps[$i]->id) }}" 
                               class="flex-1 bg-orange-600 text-white text-center py-2 px-3 rounded-lg text-sm font-medium hover:bg-orange-700 transition-colors">
                                ✏️ Editar
                            </a>
                        </div>
                    </div>
                </div>
            @endfor
        </div>
    @else
        <div class="text-center py-16 bg-white rounded-lg shadow-lg">
            <div class="text-6xl mb-4">🗺️</div>
            <h2 class="text-2xl font-bold text-gray-900 mb-2">Nenhum mapa encontrado</h2>
            <p class="text-gray-600 mb-6 max-w-md mx-auto">
                Comece criando seu primeiro mapa personalizado! Você pode desenhar diferentes tipos de terreno e definir rotas inteligentes.
            </p>
            <a href="{{ route('maps.create') }}" 
               class="inline-flex items-center bg-blue-600 text-white px-8 py-3 rounded-lg font-medium hover:bg-blue-700 transition-colors">
                🎨 Criar Primeiro Mapa
            </a>
        </div>
    @endif
</div>
@endsection