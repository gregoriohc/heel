<?php

namespace Gregoriohc\Heel;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Translation\FileLoader;
use Illuminate\Translation\Translator;
use Illuminate\Validation\Factory;

class Transformer implements \JsonSerializable
{
    /**
     * @var Factory
     */
    protected $validatorFactory;

    /**
     * @var mixed
     */
    protected $inputData = [];

    /**
     * @var array
     */
    protected $transformedData;

    /**
     * @var array
     */
    protected $mapping = [];

    /**
     * @var array
     */
    protected $casting = [];

    /**
     * @var array
     */
    protected $castingCallableExtraArguments = [];

    /**
     * @var array
     */
    protected $preValidationRules = [];

    /**
     * @var array
     */
    protected $postValidationRules = [];

    /**
     * Transformer constructor.
     */
    public function __construct()
    {
        $this->validatorFactory = new Factory(new Translator(new FileLoader(new Filesystem(), ''), 'en_US'));
    }

    /**
     * @return static
     */
    public static function create()
    {
        return new static();
    }

    /**
     * @param mixed $data
     * @param string|null $type
     * @return static
     * @throws \Exception
     */
    public static function createWithInput($data, $type = null)
    {
        $type = $type ? 'input' . ucfirst($type) : 'input';

        return self::create()->$type($data);
    }

    /**
     * @param array|\ArrayAccess $data
     * @return $this
     * @throws \Exception
     */
    public function input($data)
    {
        if (!is_array($data)) {
            throw new \Exception('Input data must be an array or implements ArrayAccess');
        }

        $this->inputData = $data;

        return $this;
    }

    /**
     * @param string $property
     * @param array $options
     * @return $this
     */
    public function define($property, $options = [])
    {
        $inputName = isset($options['inputName']) ? $options['inputName'] : $property;
        $this->mapping[$property] = $inputName;

        if (isset($options['cast'])) {
            $this->casting[$property] = $options['cast'];
        }

        if (isset($options['castCallableExtraArguments'])) {
            $this->castingCallableExtraArguments[$property] = $options['castCallableExtraArguments'];
        }

        if (isset($options['preValidationRules'])) {
            $this->preValidationRules[$inputName] = $options['preValidationRules'];
        }

        if (isset($options['postValidationRules'])) {
            $this->postValidationRules[$property] = $options['postValidationRules'];
        }

        return $this;
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function transform()
    {
        $this->preValidate((array)$this->inputData);

        $this->transformedData = [];

        foreach ($this->mapping as $transformedKey => $inputKey) {
            $this->transformedData[$transformedKey] = $this->cast($transformedKey, $this->inputData[$inputKey]);
        }

        $this->postValidate($this->transformedData);

        return $this;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $this->transform();

        return $this->transformedData;
    }

    /**
     * @return string
     */
    public function toJson()
    {
        return json_encode($this);
    }

    /**
     * @param array $preValidationRules
     * @return Transformer
     */
    public function setPreValidationRules($preValidationRules)
    {
        foreach ($preValidationRules as $key => $value) {
            $this->preValidationRules[$key] = $preValidationRules[$key];
        }

        return $this;
    }

    /**
     * @param array $postValidationRules
     * @return Transformer
     */
    public function setPostValidationRules($postValidationRules)
    {
        foreach ($postValidationRules as $key => $value) {
            $this->postValidationRules[$key] = $postValidationRules[$key];
        }

        return $this;
    }

    /**
     * @param string $key
     * @param mixed $value
     * @return mixed|null
     */
    private function cast($key, $value)
    {
        if (!isset($this->casting[$key])) {
            return $value;
        }

        $caster = $this->casting[$key];

        if (is_string($caster) && !is_callable($caster)) {
            switch ($caster) {
                case 'int':
                case 'integer':
                    return (int)$value;
                    break;
                case 'bool':
                case 'boolean':
                    return (bool)$value;
                    break;
                case 'float':
                case 'double':
                case 'real':
                    return (float)$value;
                    break;
                case 'string':
                    return (string)$value;
                    break;
                case 'array':
                    return (array)$value;
                    break;
                case 'object':
                    return (object)$value;
                    break;
                case 'unset':
                case 'null':
                    return null;
                    break;
            }
        }

        if (is_callable($caster)) {
            $casterArguments = [$value];
            if (isset($this->castingCallableExtraArguments[$key])) {
                $casterArguments = array_merge($casterArguments, $this->castingCallableExtraArguments[$key]);
            }
            return call_user_func_array($caster, $casterArguments);
        }

        return $value;
    }

    /**
     * @param array $data
     * @throws \Exception
     */
    private function preValidate($data)
    {
        $validator = $this->validatorFactory->make($data, $this->preValidationRules);

        if ($validator->fails()) {
            throw new \Exception('Pre-validation failed');
        }
    }

    /**
     * @param array $data
     * @throws \Exception
     */
    private function postValidate($data)
    {
        $validator = $this->validatorFactory->make($data, $this->postValidationRules);

        if ($validator->fails()) {
            throw new \Exception('Post-validation failed');
        }
    }

    /**
     * @return mixed
     */
    function jsonSerialize()
    {
        return $this->toArray();
    }
}