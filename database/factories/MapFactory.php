<?php

namespace Database\Factories;

use App\Models\Map;
use Illuminate\Database\Eloquent\Factories\Factory;

class MapFactory extends Factory
{
    protected $model = Map::class;
    public function definition()
    {
        $grid = [];
        $size = 20; 
        
        for ($i = 0; $i < $size; $i++) {
            $grid[$i] = [];
            for ($j = 0; $j < $size; $j++) {
                $grid[$i][$j] = 0; 
            }
        }

        return [
            'name' => 'Cidade ' . $this->faker->city,
            'data' => [
                'matrix' => $grid,
                'origin' => null,
                'destination' => null,
                'path' => []
            ]
        ];
    }

    public function withRandomCity()
    {
        return $this->state(function (array $attributes) {
            $grid = $attributes['data']['matrix'];
            $size = count($grid);
            
            $numBuildings = rand(15, 30);   
            $numHouses = rand(40, 80);       
            $numParks = rand(5, 10);         
            
            $this->addRandomElements($grid, 2, $numBuildings);
            
            $this->addRandomElements($grid, 1, $numHouses);
            
            $this->addRandomElements($grid, 3, $numParks);
            
            $origin = $this->findEmptyCell($grid);
            $destination = $this->findEmptyCell($grid, [$origin]);
            
            $path = $this->findPath($grid, $origin, $destination);
            
            return [
                'data' => [
                    'matrix' => $grid,
                    'origin' => $origin,
                    'destination' => $destination,
                    'path' => $path
                ]
            ];
        });
    }
    
    private function addRandomElements(&$grid, $type, $count)
    {
        $size = count($grid);
        $added = 0;
        
        while ($added < $count) {
            $row = rand(0, $size - 1);
            $col = rand(0, $size - 1);
            
            if ($grid[$row][$col] === 0) {
                $grid[$row][$col] = $type;
                $added++;
                
                if ($type === 3 && $row + 1 < $size && $col + 1 < $size) {
                    if ($grid[$row+1][$col] === 0) {
                        $grid[$row+1][$col] = $type;
                    }
                    if ($grid[$row][$col+1] === 0) {
                        $grid[$row][$col+1] = $type;
                    }
                    if ($grid[$row+1][$col+1] === 0) {
                        $grid[$row+1][$col+1] = $type;
                    }
                }
                
                if ($type === 2) {
                    if (rand(0, 1) === 0 && $col + 1 < $size && $grid[$row][$col+1] === 0) {
                        $grid[$row][$col+1] = $type;
                    } elseif ($row + 1 < $size && $grid[$row+1][$col] === 0) {
                        $grid[$row+1][$col] = $type;
                    }
                }
            }
        }
    }
    
    private function findEmptyCell($grid, $exclude = [])
    {
        $size = count($grid);
        
        while (true) {
            $row = rand(0, $size - 1);
            $col = rand(0, $size - 1);
            
            if ($grid[$row][$col] === 0) {
                $isExcluded = false;
                foreach ($exclude as $point) {
                    if ($point && $point[0] === $row && $point[1] === $col) {
                        $isExcluded = true;
                        break;
                    }
                }
                
                if (!$isExcluded) {
                    return [$row, $col];
                }
            }
        }
    }
    
    private function findPath($grid, $origin, $destination)
    {
        $rows = count($grid);
        $cols = count($grid[0]);
        
        $visited = [];
        for ($i = 0; $i < $rows; $i++) {
            $visited[$i] = [];
            for ($j = 0; $j < $cols; $j++) {
                $visited[$i][$j] = false;
            }
        }
        
        $prev = [];
        for ($i = 0; $i < $rows; $i++) {
            $prev[$i] = [];
            for ($j = 0; $j < $cols; $j++) {
                $prev[$i][$j] = null;
            }
        }
        
        $directions = [[-1, 0], [0, 1], [1, 0], [0, -1]];
        
        $queue = [];
        $front = 0;
        
        $queue[] = $origin;
        $visited[$origin[0]][$origin[1]] = true;
        
        $foundPath = false;
        
        while ($front < count($queue)) {
            $current = $queue[$front];
            $front++;
            
            if ($current[0] === $destination[0] && $current[1] === $destination[1]) {
                $foundPath = true;
                break;
            }
            
            foreach ($directions as $dir) {
                $newRow = $current[0] + $dir[0];
                $newCol = $current[1] + $dir[1];
                
                if ($newRow >= 0 && $newRow < $rows && $newCol >= 0 && $newCol < $cols) {
                    if (!$visited[$newRow][$newCol] && $grid[$newRow][$newCol] === 0) {
                        $visited[$newRow][$newCol] = true;
                        $prev[$newRow][$newCol] = $current;
                        $queue[] = [$newRow, $newCol];
                    }
                }
            }
        }
        
        $path = [];
        if ($foundPath) {
            $current = $destination;
            
            while (!($current[0] === $origin[0] && $current[1] === $origin[1])) {
                if (!($current[0] === $destination[0] && $current[1] === $destination[1])) {
                    $path[] = $current;
                }
                $current = $prev[$current[0]][$current[1]];
                if (!$current) break;
            }
            
            $path = array_reverse($path);
        }
        
        return $path;
    }
}