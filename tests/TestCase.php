<?php

namespace Gregoriohc\Heel\Tests;

use PHPUnit_Framework_TestCase;

abstract class TestCase extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
    }

    protected function arraysAreEqual($a, $b)
    {
        return json_encode($a) === json_encode($b);
    }
}