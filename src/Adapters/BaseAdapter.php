<?php

namespace Avapardaz\InterpreterManager\Adapters;

use Exception;



class BaseAdapter implements InterpreterAdapter
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
     * key seperator
     * 
     * @var string
     */
    private $seperator = ".";

    /**
     * Adapter variables
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
     * @return BaseAdapter
     */
    public static function make(array $config = []): BaseAdapter
    {
        return new BaseAdapter($config);
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
     * @param string|null $value
     * @return void
     */
    private function setDefaultValue(string $key, string|null $value): void
    {
        foreach ($this->variables as &$variable) {
            if ($this->getKey($variable['key']) == $this->getKey($key)) {
                $variable['defaultValue'] = $value;
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
     * Check if variable has value or default value
     * 
     * @param array $variable adapter registered variable
     * @param string|null $value interpret passed variable
     * @return bool
     */
    private function hasValueOrDefaultValue(array $variable, string|null $value): bool
    {
        return isset($variable['defaultValue']) || isset($value);
    }

    /**
     * Interpretation variables
     * 
     * @return array
     */
    public function variables(): array
    {
        return array_map(
            function (array $variable): array {

                if (!array_key_exists('key', $variable))
                    throw new Exception('Interpreter base adapter variable has no key property');

                return [
                    'fillable' => $variable['fillable'] ?? true,
                    'label' => $variable['label'],
                    'key' => $this->getKey($variable['key']),
                    'defaultValue' => $variable['defaultValue'] ?? '',
                    'directive' => array_key_exists('inputDirectives', $variable) ? $variable['inputDirectives'] : []
                ];
            },
            $this->variables
        );
    }

    /**
     * Interpretation interpretated variables
     * 
     * @param array $values
     * @return array
     */
    private function interpretatedVariables(array $values): array
    {
        $result = [];

        foreach ($this->variables as $variable) {

            if (
                $this->mode == InterpreterAdapterMode::Strict &&
                !$this->hasValueOrDefaultValue($variable, $values[$this->getKey($variable['key'])])
            ) throw new Exception('Variable has no value nor default value.');

            $result[$this->getKey($variable['key'])] = $values[$this->getKey($variable['key'])] ?? $this->getDefaultValue($variable['key']);
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
    public function interpret(string $text, array $values = []): string
    {
        $interpreted = $text;

        foreach ($this->interpretatedVariables($values) as $key => $value)
            $interpreted = preg_replace("/{($key)}/", $value, $interpreted);

        return $interpreted;
    }
}
