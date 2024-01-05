<?php

namespace JaxWilko\Game\Classes\Engine\Core\Utils;

use JaxWilko\Game\Classes\Engine\Core\Contracts\VectorInterface;

class Vector implements VectorInterface
{
    public int $x;
    public int $y;

    /**
     * Creates a new instance and passes args to set().
     *
     * @param int $x
     * @param int $y
     */
    public function __construct(int $x = 0, int $y = 0)
    {
        $this->set($x, $y);
    }

    /**
     * Creates a new instance from a string of format `$x,$y`.
     *
     * @param string $str
     * @return static
     */
    public static function fromString(string $str): static
    {
        return new static(...array_map(fn ($i) => (int) $i, explode(',', $str)));
    }

    /**
     * Returns a clone of $this.
     *
     * @return $this
     */
    public function clone(): static
    {
        return clone $this;
    }

    /**
     * Sets both the X and the Y properties of the Vector.
     *
     * @param int $x
     * @param int $y
     * @return void
     */
    public function set(int $x = 0, int $y = 0): void
    {
        $this->x($x ?? 0);
        $this->y($y ?? 0);
    }

    /**
     * Returns the Vector as [$x, $y].
     *
     * @return array
     */
    public function get(): array
    {
        return [$this->x, $this->y];
    }

    /**
     * Used to both get and set X depending on if a value is provided.
     *
     * @param int|null $x
     * @return int
     */
    public function x(int $x = null): int
    {
        return !is_null($x) ? $this->x = $x : $this->x;
    }

    /**
     * Used to both get and set Y depending on if a value is provided.
     *
     * @param int|null $y
     * @return int
     */
    public function y(int $y = null): int
    {
        return !is_null($y) ? $this->y = $y : $this->y;
    }

    /**
     * Increases / decreases X by an amount, limited by the max.
     *
     * @param int $amount
     * @param int|null $max
     * @return int
     */
    public function tapX(int $amount = 0, int $max = null): int
    {
        if (
            !is_null($max)
            && (
                $amount > 0 && $this->x + $amount >= $max
                || $amount < 0 && $this->x + $amount <= $max
            )
        ) {
            return $this->x = $max;
        }

        return $this->x += $amount;
    }

    /**
     * Increases / decreases Y by an amount, limited by the max.
     *
     * @param int $amount
     * @param int|null $max
     * @return int
     */
    public function tapY(int $amount = 0, int $max = null): int
    {
        if (
            !is_null($max)
            && (
                $amount > 0 && $this->y + $amount >= $max
                || $amount < 0 && $this->y + $amount <= $max
            )
        ) {
            return $this->y = $max;
        }

        return $this->y += $amount;
    }

    /**
     * Creates a new Vector with the adjustments provided.
     *
     * @param string $prop
     * @param int $amount
     * @return Vector
     */
    public function dry(string $prop, int $amount): Vector
    {
        return new Vector(
            $prop === 'x' ? $this->x + $amount : $this->x,
            $prop === 'y' ? $this->y + $amount : $this->y
        );
    }

    /**
     * Casts the Vector to a string.
     *
     * @return string
     */
    public function toString(): string
    {
        return $this->x() . ',' . $this->y();
    }

    /**
     * Casts the Vector to an array.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'x' => $this->x(),
            'y' => $this->y()
        ];
    }
}
