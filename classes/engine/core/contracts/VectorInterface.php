<?php

namespace JaxWilko\Game\Classes\Engine\Core\Contracts;

interface VectorInterface extends ToArrayInterface, ToStringInterface
{
    public function get(): array;

    public function set(int $x, int $y): void;

    public function x(int $x = 0): int;

    public function y(int $y = 0): int;
}
