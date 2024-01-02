<?php

namespace Avapardaz\InterpreterManager\Adapters;

use DateTime;

class CustomAdapter implements InterpreterAdapter
{
    /**
     * Variables provider by user
     * 
     * @var array
     */
    private $variables = [];

    /**
     * Extend dynamic funcitons
     * 
     * @var array
     */
    private $extends = [];

    /**
     * Create the adapter instnace
     * 
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        foreach (['extends', 'variables'] as $attr)
            if (array_key_exists($attr, $config))
                $this->{$attr} = $config[$attr];
    }

    /**
     * Create a new adapter instance
     * 
     * @param array $config
     * @return CustomAdapter
     */
    public static function make(array $config = []): CustomAdapter
    {
        return new CustomAdapter($config);
    }

    /**
     * Functions that can be used as dynamic variable
     * 
     * @return array
     */
    private function functions(): array
    {
        return array_merge(
            [
                [
                    'key' => 'CURRENT_TIME',
                    'label' => 'Current Time',
                    'func' => fn (string $format = 'Y/m/d H:i') => (new DateTime())->format($format)
                ],
            ],
            $this->extends
        );
    }

    /**
     * Interpretation variables
     * 
     * @return array
     */
    public function variables(): array
    {
        return array_merge($this->variables, $this->functions());
    }

    /**
     * Interpretation interpretated variables
     * 
     * @return array
     */
    private function interpretatedVariables(): array
    {
        $result = [];

        foreach ($this->variables() as $value) {
            $delimited = explode(":", $value['defaultValue']);

            if ($delimited && count($delimited) > 1) {
                foreach ($this->functions() as $function) {
                    if ($function['key'] == $delimited[1]) {
                        $params = isset($delimited[2]) ? explode(",", $delimited[2]) : [];
                        $result[$value['key']] = $function['func'](...$params);
                        continue 2;
                    }
                }
            }

            $result[$value['key']] = $value['defaultValue'];
        }
        return $result;
    }

    /**
     * Interpret text base on adapter
     * 
     * @param string $text
     * @return string
     */
    public function interpret(string $text): string
    {
        $interpreted = $text;
        foreach ($this->interpretatedVariables() as $key => $value)
            $interpreted = preg_replace("/{($key)}/", $value, $interpreted);

        return $interpreted;
    }
}
