<?php

namespace Gregoriohc\Heel\Tests;

use Gregoriohc\Heel\Manager;
use Gregoriohc\Heel\Transformer;

class RouteTest extends TestCase
{
    protected $inputArrayData = [
        [
            'CODE' => '123',
        ]
    ];

    protected $outputArrayData = [
        [
            'code' => 123,
        ]
    ];

    public function testPreviewFound()
    {
        $transformer = Transformer::create()
            ->define('code', ['inputName' => 'CODE', 'cast' => 'integer']);

        $data = Manager::fromArray($this->inputArrayData)->setTransformer($transformer)->toArray();

        $this->assertTrue($this->arraysAreEqual($data, $this->outputArrayData));
    }
}