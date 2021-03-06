<?php

declare(strict_types=1);

namespace Apply\Either;

use Apply\EvalM\EvalM;
use Apply\Option\Option;
use Apply\Option\Some;
use JetBrains\PhpStorm\Pure;

/**
 * Class Right.
 *
 * @inherits B
 */
class Right extends Either
{
    /**
     * @var mixed
     * @phan-var B
     */
    private $value;

    /**
     * @param mixed $value
     * @phan-param B $value
     */
    public function __construct($value)
    {
        $this->value = $value;
    }

    public function map(callable $f): Either
    {
        return new self($f($this->value));
    }

    public function flatMap(callable $f): Either
    {
        return $f($this->value);
    }

    public function fold(callable $ifLeft, callable $ifRight)
    {
        return $ifRight($this->value);
    }

    public function foldLeft($initial, callable $rightOperation)
    {
        return $rightOperation($initial, $this->value);
    }

    public function foldRight(EvalM $initial, callable $rightOperation): EvalM
    {
        return $rightOperation($this->value, $initial);
    }

    public function isLeft(): bool
    {
        return false;
    }

    public function isRight(): bool
    {
        return true;
    }

    #[Pure]
    public function swap(): Either
    {
        return new Left($this->value);
    }

    public function mapLeft(callable $f): Either
    {
        return $this;
    }

    public function bimap(callable $leftOperation, callable $rightOperation): Either
    {
        return $this->map($rightOperation);
    }

    public function exists(callable $predicate): bool
    {
        return $predicate($this->value);
    }

    public function toOption(): Option
    {
        return Some::fromValue($this->value);
    }

    public function getOrElse(callable $default)
    {
        return $this->value;
    }

    public function orNull()
    {
        return $this->value;
    }

    public function getOrHandle(callable $default)
    {
        return $this->value;
    }

    public function handleErrorWith(callable $handler): Either
    {
        return $this;
    }

    public function leftIfNull(callable $default): Either
    {
        return null === $this->value
            ? new Left($default())
            : $this;
    }

    public function filterOrElse(callable $predicate, callable $default): Either
    {
        return $predicate($this->value)
            ? $this
            : new Left($default());
    }

    public function filterOrOther(callable $predicate, callable $default): Either
    {
        return $predicate($this->value)
            ? $this
            : $default();
    }
}
