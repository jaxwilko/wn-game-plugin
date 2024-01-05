<?php

namespace JaxWilko\Game\Classes\Engine\Core\Utils;

use JaxWilko\Game\Classes\Engine\Core\Objects\Entities\Actor;
use JaxWilko\Game\Classes\Engine\Core\Objects\Entities\Entity;
use JaxWilko\Game\Classes\Engine\Core\Objects\WorldObject;
use JaxWilko\Game\Classes\Engine\Modules\World\Level;

class Pathing
{
    public const CELL_EMPTY = 0;
    public const CELL_BLOCKED = 1;
    public const CELL_TARGET = 2;

    public static function makePath(Level $level, Actor $entity, WorldObject $target): array
    {
        $worldSize = $level->getSize();

        $cells = [
            (int) ceil($worldSize[1]->x / $entity->getSize()->x()),
            (int) ceil($worldSize[1]->y / $entity->getSize()->y())
        ];

        $simpleEntity = [
            'vector' => [
                $entity->getVector()->x(),
                $entity->getVector()->y()
            ],
            'size' => [
                $entity->getSize()->x(),
                $entity->getSize()->y()
            ]
        ];

        $simpleTarget = [
            'vector' => [
                $target->getVector()->x(),
                $target->getVector()->y()
            ],
            'size' => [
                $target->getSize()->x(),
                $target->getSize()->y()
            ]
        ];

        $moves = static::calculatePath(
            $cells,
            $simpleEntity,
            $simpleTarget,
            static::getBlockingObjects($level, $entity)
        );

        $moves = static::reducePath($cells, $moves);

        return array_map(fn ($move) => static::translateFromGrid($simpleEntity, $move), $moves);
    }

    protected static function translateEntityToGrid(Entity $entity): array
    {
        return [
            (int) floor($entity->getVector()->x() / $entity->getSize()->x()),
            (int) floor($entity->getVector()->y() / $entity->getSize()->y())
        ];
    }

    protected static function translateArrayToGrid(array $entity): array
    {
        return [
            (int) floor($entity['vector'][0] / $entity['size'][0]),
            (int) floor($entity['vector'][1] / $entity['size'][1])
        ];
    }

    protected static function getBlockingObjects(Level $level, Actor $entity): array
    {
        $objects = array_merge($level->search(
            new WorldObject(
                ...$level->getSize()
            ),
            $entity->getBlockingLayers(),
            true,
            $entity->id,
            true
        ));

        return array_map(function (WorldObject $entity) {
            return [
                'vector' => [
                    $entity->getVector()->x(),
                    $entity->getVector()->y()
                ],
                'size' => [
                    $entity->getSize()->x(),
                    $entity->getSize()->y()
                ]
            ];
        }, $objects);
    }

    protected static function calculatePath(array $cells, array $entity, array $target, array $objects): array
    {
        $targetCell = static::translateArrayToGrid($target);
        $triedCells = [];
        $moves = [];

        for ($i = 0; $i < 50; $i++) {
            $grid = static::makeGrid($cells, $entity, $objects, $target);
            $current = static::translateArrayToGrid($entity);
            $neighbors = static::getNeighbors($current, $cells);
            $distances = [];

            // Filter invalid moves
            foreach ($neighbors as $index => $neighbor) {
                if (($grid[$neighbor[0]][$neighbor[1]] ?? 0) === static::CELL_TARGET) {
                    return $moves;
                }
                if (
                    ($grid[$neighbor[0]][$neighbor[1]] ?? 1) !== static::CELL_EMPTY
                    || in_array($neighbor[0] . ',' . $neighbor[1], $triedCells)
                ) {
                    unset($neighbors[$index]);
                    continue;
                }
                $distances[static::getDistance($neighbor, $targetCell)][] = $neighbor;
            }

            if (empty($distances)) {
                $triedCells = [];
                continue;
            }

            ksort($distances);

            $best = array_first($distances);

            if (count($best) > 1) {
                usort($best, fn ($a, $b) => $a[0] + $a[1] > $b[0] + $b[1] ? 1 : 0);
            }

            $move = $best[0];

            $entity['vector'] = static::translateFromGrid($entity, $move);
            $moves[] = $move;
            $triedCells[] = $move[0] . ',' . $move[1];
        }

        return $moves;
    }

    protected static function reducePath(array $cells, array $moves): array
    {
        $movesLength = count($moves);

        for ($index = 0; $index < $movesLength; $index++) {
            $move = $moves[$index];

            if (!$move) {
                continue;
            }

            $neighbors = static::getNeighbors($move, $cells);

            for ($nextIndex = $index + 3; $nextIndex < $movesLength; $nextIndex++) {
                $next = $moves[$nextIndex];

                if (!$next) {
                    continue;
                }

                foreach ($neighbors as $neighbor) {
                    if ($neighbor[0] === $next[0] && $neighbor[1] === $next[1]) {
                        for ($i = $index + 1; $i < $nextIndex; $i++) {
                            $moves[$i] = null;
                        }

                        $index = $nextIndex;
                    }
                }
            }
        }

        return array_values(array_filter($moves));
    }

    protected static function makeGrid(array $cells, array $entity, array $objects, array $target): array
    {
        $grid = [];
        foreach (range(0, $cells[0]) as $x) {
            foreach (range(0, $cells[1]) as $y) {
                $cell = [
                    'vector' => [
                        (int) floor($x * $entity['size'][0]),
                        (int) floor($y * $entity['size'][1])
                    ],
                    'size' => [
                        $entity['size'][0],
                        $entity['size'][1]
                    ]
                ];
                if (static::intersects($target, $cell)) {
                    $grid[$x][$y] = static::CELL_TARGET;
                    continue;
                }
                foreach ($objects as $object) {
                    if (static::intersects($object, $cell)) {
                        $grid[$x][$y] = static::CELL_BLOCKED;
                        break;
                    }
                }
                $grid[$x][$y] = $grid[$x][$y] ?? static::CELL_EMPTY;
            }
        }

        return $grid;
    }

    protected static function intersects(array $a, array $b): bool
    {
        return (
            $a['vector'][0] < ($b['vector'][0] + $b['size'][0])
            && ($a['vector'][0] + $a['size'][0]) > $b['vector'][0]
        ) && (
            $a['vector'][1] < ($b['vector'][1] + $b['size'][1])
            && ($a['vector'][1] + $a['size'][1]) > $b['vector'][1]
        );
    }

    protected static function getNeighbors(array $cell, array $cells): array
    {
        $neighbors = [
            [$cell[0] - 1, $cell[1] - 1],
            [$cell[0] + 1, $cell[1] - 1],
            [$cell[0] - 1, $cell[1] + 1],
            [$cell[0] + 1, $cell[1] + 1],
            [$cell[0] - 1, $cell[1]],
            [$cell[0], $cell[1] - 1],
            [$cell[0] + 1, $cell[1]],
            [$cell[0], $cell[1] + 1],
        ];

        foreach ($neighbors as $index => $neighbor) {
            if (is_null($neighbor)) {
                continue;
            }

            if (
                ($neighbor[0] < 0 || $neighbor[1] < 0)
                || ($neighbor[0] >= $cells[0] || $neighbor[1] >= $cells[1])
            ) {
                $neighbors[$index] = null;
            }
        }

        return array_values(array_filter($neighbors));
    }

    protected static function getDistance(array $a, array $b): int
    {
        $x = abs($a[0] - $b[0]);
        $y = abs($a[1] - $b[1]);
        return $x + $y;
    }

    protected static function translateFromGrid(array $entity, array $cell): array
    {
        return [
            (int) floor($cell[0] * $entity['size'][0]),
            (int) floor($cell[1] * $entity['size'][1])
        ];
    }

    protected static function printGrid(array $cells, array $grid, bool $coords = false): void
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
}
