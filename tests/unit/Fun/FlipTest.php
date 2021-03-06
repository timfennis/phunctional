<?php

declare(strict_types=1);

namespace Test\Apply\Fun;

use function Apply\Fun\Curried\flip;
use Codeception\Test\Unit;

class FlipTest extends Unit
{
    public function testCurriedFlip()
    {
        $curriedSubtract = function ($a) {
            return function ($b) use ($a) {
                return $a - $b;
            };
        };

        $this->assertSame(10, flip($curriedSubtract)(10)(20));
    }

    public function testUncurriedFlip()
    {
        $subtract = function ($a, $b) {
            return $a - $b;
        };

        $this->assertSame(10, \Apply\Fun\Imperative\flip($subtract)(10, 20));
    }
}
