<?php

namespace Apply\EvalM;

use RuntimeException;

/**
 * Class EvalM
 *
 * @property mixed $value
 */
abstract class EvalM
{
    /**
     * @param EvalM $e
     * @return mixed
     */
    protected static function evaluate(EvalM $e)
    {
        $curr = $e;
        $fs = [];

        while (true) {
            if ($curr instanceof FlatMap) {
                $currComp = $curr;
                $cc = $curr->start();

                if ($cc instanceof FlatMap) {
                    $inStartFun = static function ($a) use ($cc) {
                        return $cc->run($a);
                    };
                    $outStartFun = static function ($a) use ($currComp) {
                        return $currComp->run($a);
                    };

                    $curr = $cc->start();
                    array_unshift($fs, $outStartFun);
                    array_unshift($fs, $inStartFun);
                } else {
                    $curr = $currComp->run($cc->value);
                }
            } else {
                if (count($fs) > 0) {
                    /** @var callable $fun */
                    $fun = array_shift($fs);
                    $curr = $fun($curr->value);
                } else {
                    break;
                }
            }
        }

        return $curr->value;
    }

    public function map(callable $func): EvalM
    {
        return $this->flatMap(static function ($arg) use ($func) {
            return new Now($func($arg));
        });
    }

    /**
     * WHAT THE FUCK DID I DOO??!??!?
     *
     * @param callable $fn
     *
     * @return EvalM
     */
    public function flatMap(callable $fn): EvalM
    {
        if ($this instanceof FlatMap) {
            return new class($this, $fn) extends FlatMap {

                /** @var FlatMap */
                private $outer;

                /** @var callable */
                private $fn;

                public function __construct(FlatMap $outer, callable $fn)
                {
                    $this->outer = $outer;
                    $this->fn = $fn;
                }

                public function start(): EvalM
                {
                    return $this->outer->start();
                }

                public function run($s): EvalM
                {
                    return new class($this->outer, $this->fn, $s) extends FlatMap {

                        /** @var FlatMap */
                        private $outer;
                        /** @var callable */
                        private $fn;
                        /** @var mixed */
                        private $s;

                        public function __construct(FlatMap $outer, callable $fn, $s)
                        {
                            $this->outer = $outer;
                            $this->fn = $fn;
                            $this->s = $s;
                        }

                        public function start(): EvalM
                        {
                            return $this->outer->run($this->s);
                        }

                        public function run($s1): EvalM
                        {
                            return ($this->fn)($s1);
                        }
                    };
                }
            };
        } else {
            return new class($this, $fn) extends FlatMap {

                /** @var EvalM */
                private $outer;
                /** @var callable */
                private $fn;

                public function __construct(EvalM $outer, callable $fn)
                {
                    $this->outer = $outer;
                    $this->fn = $fn;
                }

                public function start(): EvalM
                {
                    return $this->outer;
                }

                public function run($s): EvalM
                {
                    return ($this->fn)($s);
                }
            };
        }
    }

    /**
     * @return mixed
     */
    abstract public function value();

    public function __get($name)
    {
        if ($name === 'value') {
            return $this->value();
        }

        throw new RuntimeException('Fuck off');
    }

    public function __set($name, $value)
    {
        throw new RuntimeException('Fuck off');
    }

    public function __isset($name)
    {
        return $name === 'value';
    }


    public static function now($value): Now
    {
        return new Now($value);
    }

    public static function always(callable $callable): Always
    {
        return new Always($callable);
    }

    public static function later(callable $callable): Later
    {
        return new Later($callable);
    }
}
