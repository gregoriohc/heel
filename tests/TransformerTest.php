<?php

namespace Gregoriohc\Heel\Tests;

use Gregoriohc\Heel\Manager;
use Gregoriohc\Heel\Transformer;

class RouteTest extends TestCase
{
    public function testInputName()
    {
        $transformer = Transformer::create()->define('code', ['inputName' => 'CODE', 'cast' => 'string']);

        $data = Manager::fromArray([
            ['CODE' => '123'],
        ])->setTransformer($transformer)->toArray();

        $this->assertTrue($this->arraysAreEqual($data, [
            ['code' => '123'],
        ]));
    }

    public function testCastInteger()
    {
        $transformer = Transformer::create()->define('code', ['cast' => 'integer']);

        $data = Manager::fromArray([
            ['code' => '123'],
            ['code' => 456],
            ['code' => 789.01],
        ])->setTransformer($transformer)->toArray();

        $this->assertTrue($this->arraysAreEqual($data, [
            ['code' => 123],
            ['code' => 456],
            ['code' => 789],
        ]));
    }

    public function testCastFloat()
    {
        $transformer = Transformer::create()->define('code', ['cast' => 'float']);

        $data = Manager::fromArray([
            ['code' => '123'],
            ['code' => 456],
            ['code' => 789.01],
        ])->setTransformer($transformer)->toArray();

        $this->assertTrue($this->arraysAreEqual($data, [
            ['code' => 123.0],
            ['code' => 456.0],
            ['code' => 789.01],
        ]));
    }

    public function testCastString()
    {
        $transformer = Transformer::create()->define('code', ['cast' => 'string']);

        $data = Manager::fromArray([
            ['code' => '123'],
            ['code' => 456],
            ['code' => 789.01],
        ])->setTransformer($transformer)->toArray();

        $this->assertTrue($this->arraysAreEqual($data, [
            ['code' => '123'],
            ['code' => '456'],
            ['code' => (string) 789.01],
        ]));
    }

    public function testCastClosure()
    {
        $transformer = Transformer::create()->define('code', ['cast' => function($value) {
            return intval($value) * 2;
        }]);

        $data = Manager::fromArray([
            ['code' => '123'],
            ['code' => 456],
            ['code' => 789.01],
        ])->setTransformer($transformer)->toArray();

        $this->assertTrue($this->arraysAreEqual($data, [
            ['code' => 246],
            ['code' => 912],
            ['code' => 1578],
        ]));
    }

    public function testCastClosureWithArguments()
    {
        $transformer = Transformer::create()->define('code', ['cast' => function($value, $multiplier) {
            return intval($value) * $multiplier;
        }, 'castCallableExtraArguments' => [2]]);

        $data = Manager::fromArray([
            ['code' => '123'],
            ['code' => 456],
            ['code' => 789.01],
        ])->setTransformer($transformer)->toArray();

        $this->assertTrue($this->arraysAreEqual($data, [
            ['code' => 246],
            ['code' => 912],
            ['code' => 1578],
        ]));
    }

    public function testPreValidationRules()
    {
        $transformer = Transformer::create()->define('code', ['cast' => 'string', 'preValidationRules' => 'required|numeric']);

        $data = Manager::fromArray([
            ['code' => 123],
        ])->setTransformer($transformer)->toArray();

        $this->assertTrue($this->arraysAreEqual($data, [
            ['code' => '123'],
        ]));
    }

    public function testPostValidationRules()
    {
        $transformer = Transformer::create()->define('code', ['cast' => 'integer', 'postValidationRules' => 'required|numeric']);

        $data = Manager::fromArray([
            ['code' => '123'],
        ])->setTransformer($transformer)->toArray();

        $this->assertTrue($this->arraysAreEqual($data, [
            ['code' => 123],
        ]));
    }

    public function testJsonOutput()
    {
        $transformer = Transformer::create()->define('code', ['cast' => 'string']);

        $data = Manager::fromArray([
            ['code' => '123'],
        ])->setTransformer($transformer)->toJson();

        $this->assertEquals($data, json_encode([['code' => '123']]));
    }
}