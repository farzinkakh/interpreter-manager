<?php

namespace Avapardaz\InterpreterManager;

use Avapardaz\InterpreterManager\Adapters\InterpreterAdapter;
use Exception;

class Interpreter
{

    /**
     * The interpreter adaptors
     * 
     * @var InterpreterAdapter[]
     */
    private $adapters = [];

    /**
     * Create the interpreter instance
     * 
     * @param array $config
     */
    public function __construct(array $config)
    {
        if (!array_key_exists('adapters', $config) || count($config['adapters']) < 1)
            throw new Exception('Interpreter must have at least one adapter.');

        $this->adapters = array_map(
            fn (array $config, string $adapter): InterpreterAdapter => $adapter::make($config),
            $config['adapters'],
            array_keys($config['adapters'])
        );
    }

    /**
     * Make a new interpreter instance
     *
     * @param array $config
     */
    public static function make(array $config): Interpreter
    {
        return new Interpreter($config);
    }

    /**
     * Interpret a text base on adapters
     * 
     * @param string $text
     * @param array $values
     * @return string
     */
    public function interpret(string $text, array $values = []): string
    {
        $interpreted = $text;

        foreach ($this->adapters as $adapter)
            $interpreted = $adapter->interpret($interpreted, $values);

        return $interpreted;
    }

    /**
     * Get interpreter available variables
     * 
     * @return array
     */
    public function variables(): array
    {
        $result = [];
        foreach ($this->adapters as $adapter)
            array_push($result, ...$adapter->variables());
        return $result;
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
     * Inject new values for adapters variables
     * 
     * @param array $values
     * @return void
     */
    public function inject(array $values): void
    {
        foreach ($values as $adapter => $values)
            if ($this->hasAdapter($adapter))
                $this->getAdapter($adapter)->inject($values);
    }

    /**
     * Check if adapter exists
     * 
     * @param InterpreterAdapter|string $adapter
     * @return bool
     */
    public function hasAdapter(InterpreterAdapter|string $adapter): bool
    {
        return count(
            array_filter(
                $this->adapters,
                fn ($entry) => $entry instanceof $adapter
            )
        ) > 0;
    }

    /**
     * Get specific adaptor if exists and null otherwise
     * 
     * @param InterpreterAdapter|string $adapter
     * @return InterpreterAdapter|null
     */
    public function getAdapter(InterpreterAdapter|string $adapter): mixed
    {
        return current(
            array_filter(
                $this->adapters,
                function ($entry) use ($adapter) {
                    return $entry instanceof $adapter;
                }
            )
        );
    }
}
