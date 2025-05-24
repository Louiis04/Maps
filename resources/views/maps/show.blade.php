@extends('layouts.app')

@section('content')
    <h1>{{ $map->name }}</h1>
    
    <div class="grid-container">
        @php
            $matrix = $map->data['matrix'] ?? [];
            $rows = count($matrix);
            $cols = $rows > 0 ? count($matrix[0]) : 0;
        @endphp
        
        @for($i = 0; $i < $rows; $i++)
            @for($j = 0; $j < $cols; $j++)
                <div class="grid-cell cell-{{ $matrix[$i][$j] }}"></div>
            @endfor
        @endfor
    </div>
    
    <p>Criado em: {{ $map->created_at->format('d/m/Y H:i') }}</p>
    <p>Atualizado em: {{ $map->updated_at->format('d/m/Y H:i') }}</p>
@endsection