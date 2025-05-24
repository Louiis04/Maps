<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Visualizador de Mapas</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background-color: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
        }
        .grid-container {
            display: grid;
            grid-template-columns: repeat(20, 20px);
            gap: 1px;
            margin: 20px 0;
        }
        .grid-cell {
            width: 20px;
            height: 20px;
            border: 1px solid #ddd;
            cursor: pointer;
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
        .map-legend {
            display: flex;
            margin-bottom: 15px;
        }
        .legend-item {
            display: flex;
            align-items: center;
            margin-right: 20px;
        }
        .color-box {
            width: 20px;
            height: 20px;
            margin-right: 5px;
            border: 1px solid #ddd;
        }
        nav {
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        nav a {
            margin-right: 15px;
            text-decoration: none;
            color: #2196F3;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
        }
        input[type="text"] {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        button {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 4px;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="container">
        <nav>
            <a href="{{ route('maps.index') }}">Lista de Mapas</a>
            <a href="{{ route('maps.create') }}">Criar Novo Mapa</a>
        </nav>
        
        @if(session('success'))
            <div style="padding: 10px; background-color: #dff0d8; margin-bottom: 15px; border-radius: 4px;">
                {{ session('success') }}
            </div>
        @endif
        
        @yield('content')
    </div>
</body>
</html>