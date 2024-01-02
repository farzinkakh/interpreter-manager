<?php

namespace Avapardaz\InterpreterManager\Adapters;

use Error;

class ModelAdapter implements InterpreterAdapter
{
    /**
     * Interpret mode
     * mode in case no value or default value not provided for variable
     * 
     * Strict: throw error 
     * Empty: return empty string
     * Key: return key
     * 
     * 
     * @var bool
     */
    private $mode = InterpreterAdapterMode::Empty;

    /**
     * Attribute seperator
     * 
     * @var string
     */
    private $seperator = ".";

    /**
     * Variables provider by user
     * 
     * @var array
     */
    private $variables = [];

    /**
     * Create the adapter instnace
     * 
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        foreach (['seperator', 'variables'] as $attr)
            if (array_key_exists($attr, $config))
                $this->{$attr} = $config[$attr];
    }

    /**
     * Create a new adapter instance
     * 
     * @param array $config
     * @return ModelAdapter
     */
    public static function make(array $config = []): ModelAdapter
    {
        return new ModelAdapter($config);
    }

    /**
     * Get key string
     * 
     * @param string|array $key
     * @return string
     */
    private function getKey(string|array $key): string
    {
        return is_array($key) ? implode($this->seperator, $key) : $key;
    }

    /**
     * Get variable default value
     * 
     * @param string|array $key
     * @return string
     */
    private function getDefaultValue(string|array $key): string
    {
        return $this->mode == InterpreterAdapterMode::Key ? "{{$this->getKey($key)}}" : "";
    }

    /**
     * check if variable exists
     * 
     * @param string $key
     * @return bool
     */
    public function hasVariable(string $key): bool
    {
        return count(
            array_filter(
                $this->variables(),
                fn ($variable) => $variable['key'] == $key
            )
        ) > 0;
    }

    /**
     * Get specific variable if exists and null otherwise
     * 
     * @param string $key
     * @return array
     */
    public function getVariable(string $key): array
    {
        return current(
            array_filter(
                $this->variables(),
                fn ($var) => $var['key'] == $key
            )
        );
    }

    /**
     * Set new default value for specific variable
     * 
     * @param string $key
     * @param mixed $value
     * @return void
     */
    private function setDefaultValue(string $key, mixed $value): void
    {
        foreach ($this->variables as &$variable) {
            if ($this->getKey($variable['key']) == $this->getKey($key)) {
                $variable['model'] = $value;
                break;
            }
        }
    }

    /**
     * Inject new values for variables
     * 
     * @param array $values
     * @return void
     */
    public function inject(array $values): void
    {
        foreach ($values as $key => $value)
            $this->setDefaultvalue($key, $value);
    }

    /**
     * Get attribute label from variable
     * 
     * @param string $attribute
     * @param array $variable
     * @return string
     */
    private function getAttributeLabel(string $attribute, array $variable): string
    {
        $prefix = $attribute;

        if (array_key_exists('attributeLabels', $variable) && array_key_exists($attribute, $variable['attributeLabels']))
            $prefix = $variable['attributeLabels'][$attribute];

        return implode(" ", [$prefix, $variable['label']]);
    }

    /**
     * Interpretation variables
     * 
     * @return array
     */
    public function variables(): array
    {
        $result = [];

        foreach ($this->variables as $variable) {
            $model = is_string($variable['model']) ? $variable['model']::instance() : $variable['model'];

            if (!array_key_exists('attributes', $variable) || count($variable['attributes']) < 1)
                throw new Error('Model adapter variable must contain attributes property');

            foreach ($variable['attributes'] as $attr) {

                if (!array_key_exists($attr, $model->attributes))
                    throw new Error("Model has no attribute call '$attr'");

                array_push($result, [
                    'label' => $this->getAttributeLabel($attr, $variable),
                    'key' => implode($this->seperator, [$variable['key'], $attr]),
                    'defaultValue' => $model->{$attr}
                ]);
            }
        }

        return $result;
    }

    /**
     * Interpretation interpretated variables
     * 
     * @return array
     */
    private function interpretatedVariables(): array
    {
        $result = [];

        foreach ($this->variables as $variable) {
            foreach ($variable['model']->attributes as $attr => $value) {
                if (is_string($value)) {
                    $key = implode($this->seperator, [$variable['key'], $attr]);
                    $result[$key] = $value;
                }
            }
        }

        return $result;
    }

    /**
     * Interpret text base on adapter
     * 
     * @param string $text
     * @param array $values
     * @return string
     */
    public function interpret(string $text,  array $values = []): string
    {
        $interpreted = $text;

        foreach ($this->interpretatedVariables() as $key => $value)
            $interpreted = preg_replace("/{($key)}/", $value, $interpreted);

        return $interpreted;
    }
}
