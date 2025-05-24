@extends('layouts.app')

@section('content')
    <h1>Lista de Mapas</h1>
    
    <div class="map-list">
        @if(count($maps) > 0)
            @for($i = 0; $i < count($maps); $i++)
                <div class="map-item">
                    <h3>{{ $maps[$i]->name }}</h3>
                    <p>Criado em: {{ $maps[$i]->created_at->format('d/m/Y H:i') }}</p>
                    <a href="{{ route('maps.show', $maps[$i]->id) }}">Visualizar</a>
                </div>
            @endfor
        @else
            <p>Nenhum mapa encontrado. <a href="{{ route('maps.create') }}">Crie um novo mapa</a>.</p>
        @endif
    </div>
@endsection