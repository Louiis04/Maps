@extends('layouts.app')

@section('content')
    <h1>Criar Novo Mapa</h1>
    
    <div class="grid-container">
        @for($i = 0; $i < count($grid); $i++)
            @for($j = 0; $j < count($grid[$i]); $j++)
                <div class="grid-cell cell-{{ $grid[$i][$j] }}" 
                     onclick="toggleCell('{{ $i }}', '{{ $j }}')"></div>
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
        
        <button type="submit">Salvar Mapa</button>
    </form>
    
    <script type="text/javascript">
        let grid = JSON.parse('@json($grid)');
        
        document.getElementById('grid-data').value = JSON.stringify(grid);
        
        function toggleCell(row, col) {
            grid[row][col] = (grid[row][col] + 1) % 4;
            
            const cell = document.querySelector(`.grid-container .grid-cell:nth-child(${row * 20 + col + 1})`);
            cell.className = `grid-cell cell-${grid[row][col]}`;
            
            document.getElementById('grid-data').value = JSON.stringify(grid);
        }
    </script>
@endsection