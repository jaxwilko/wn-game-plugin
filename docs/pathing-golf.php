<?php

// Data

$world = [400, 200];

$target = [
    'vector' => [20, 160],
    'dimensions' => [20, 20]
];

$objects = [
    [
        'vector' => [128, 0],
        'dimensions' => [2, 180]
    ],
    [
        'vector' => [68, 40],
        'dimensions' => [2, 160]
    ],
    [
        'vector' => [188, 0],
        'dimensions' => [2, 180]
    ],
    [
        'vector' => [248, 40],
        'dimensions' => [2, 160]
    ],
    [
        'vector' => [318, 0],
        'dimensions' => [2, 180]
    ],
];


$entity = [
    'vector' => [360, 80],
    'dimensions' => [20, 20]
];

// Render Logic

function intersects(array $a, array $b): bool
{
    return (
            $a['vector'][0] < ($b['vector'][0] + $b['dimensions'][0])
            && ($a['vector'][0] + $a['dimensions'][0]) > $b['vector'][0]
        ) && (
            $a['vector'][1] < ($b['vector'][1] + $b['dimensions'][1])
            && ($a['vector'][1] + $a['dimensions'][1]) > $b['vector'][1]
        );
}

function translateToGrid(array $entity): array
{
    return [
        (int) floor($entity['vector'][0] / $entity['dimensions'][0]),
        (int) floor($entity['vector'][1] / $entity['dimensions'][1])
    ];
}

function makeGrid(array $cells, array $entity, array $objects, array $target): array
{
    $grid = [];
    foreach (range(0, $cells[0]) as $x) {
        foreach (range(0, $cells[1]) as $y) {
            $cell = [
                'vector' => [
                    (int) floor($x * $entity['dimensions'][0]),
                    (int) floor($y * $entity['dimensions'][1])
                ],
                'dimensions' => [
                    $entity['dimensions'][0],
                    $entity['dimensions'][1]
                ]
            ];
            if (intersects($target, $cell)) {
                $grid[$x][$y] = 3;
                continue;
            }
            if (intersects($entity, $cell)) {
                $grid[$x][$y] = 2;
                continue;
            }
            foreach ($objects as $object) {
                if (intersects($object, $cell)) {
                    $grid[$x][$y] = 1;
                    break;
                }
            }
            $grid[$x][$y] = $grid[$x][$y] ?? 0;
        }
    }

    return $grid;
}

function printGrid(array $cells, array $grid, bool $coords = false): void
{
    for ($y = 0; $y < $cells[1]; $y++) {
        for ($x = 0; $x < $cells[0]; $x++) {
            echo match ($grid[$x][$y]) {
                    0 => "\e[m0 ",
                    1 => "\e[0;31m1 ",
                    2 => "\e[0;32m# ",
                    3 => "\e[0;36m* ",
                    4 => "\e[0;35m$ "
                } . ($coords ? "({$x},{$y}) " : '');
        }
        echo PHP_EOL;
    }
}

// Implementation

$cells = [
    (int) ceil($world[0] / $entity['dimensions'][0]),
    (int) ceil($world[1] / $entity['dimensions'][1])
];

$targetCell = translateToGrid($target);

$grid = makeGrid($cells, $entity, $objects, $target);

printGrid($cells, $grid);
