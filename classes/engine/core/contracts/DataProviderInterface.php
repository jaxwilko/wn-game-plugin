<?php

namespace JaxWilko\Game\Classes\Engine\Core\Contracts;

interface DataProviderInterface extends ToArrayInterface
{
    public function register(): static;
}
