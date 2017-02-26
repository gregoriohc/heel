<?php

namespace Gregoriohc\Heel;

class Manager
{
    /**
     * @var mixed
     */
    protected $inputData;

    /**
     * @var array
     */
    protected $outputData;

    /**
     * @var Transformer
     */
    protected $transformer;

    /**
     * @return static
     */
    public static function instance()
    {
        return new static();
    }

    /**
     * @param array|\ArrayAccess $data
     * @return Manager
     * @throws \Exception
     */
    public static function fromArray($data)
    {
        $instance = self::instance();

        if (!is_array($data)) {
            throw new \Exception('Input data must be an array or implements ArrayAccess');
        }

        $instance->inputData = $data;

        return $instance;
    }

    /**
     * @param string $data
     * @return Manager
     * @throws \Exception
     */
    public static function fromCsv($data)
    {
        $instance = self::instance();

        if (!is_string($data)) {
            throw new \Exception('Input data must be a string');
        }

        $data = explode("\n", $data);
        $data = array_map('trim', $data);
        $data = array_filter($data, function($value) {
            return !empty($value);
        });
        //print_r($data); die();
        $data = array_map('str_getcsv', $data);
        $header = array_shift($data);
        $columns = count($header);

        $data = array_filter($data, function($row) use ($columns) {
            return count($row) == $columns;
        });

        array_walk($data, function(&$row, $key, $header) {
            $row = array_combine($header, $row);
        }, $header);

        $instance->inputData = $data;

        return $instance;
    }

    /**
     * @param string $file
     * @return Manager
     * @throws \Exception
     */
    public static function fromCsvFile($file)
    {
        return self::fromCsv(file_get_contents($file));
    }

    /**
     * @param Transformer $transformer
     * @return Manager
     */
    public function setTransformer($transformer)
    {
        $this->transformer = $transformer;

        return $this;
    }

    public function transform()
    {
        $this->outputData = array_map(function($data) {
            return $this->transformer->input($data)->toArray();
        }, $this->inputData);
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $this->transform();

        return $this->outputData;
    }

    /**
     * @return string
     */
    public function toJson()
    {
        return json_encode($this->toArray());
    }
}