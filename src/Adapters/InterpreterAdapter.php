<?php

namespace Avapardaz\InterpreterManager\Adapters;

enum InterpreterAdapterMode
{
    case Strict;
    case Empty;
    case Key;
}

interface InterpreterAdapter
{

    /**
     * Create the adapter instnace
     * 
     * @param array $config
     */
    public function __construct(array $config = []);

    /**
     * Make a new adapter instance
     * 
     * @param array $config
     * @return InterpreterAdapter
     */
    public static function make(array $config = []): InterpreterAdapter;

    /**
     * Interpret text base on adapter
     * 
     * @param string $text
     * @param array $values
     * @return string
     */
    public function interpret(string $text, array $values = []): string;

    /**
     * Interpretation variables
     * 
     * @return array
     */
    public function variables(): array;

    /**
     * check if variable exists
     * 
     * @param string $key
     * @return bool
     */
    public function hasVariable(string $key): bool;

    /**
     * Get specific variable if exists and null otherwise
     * 
     * @param string $key
     * @return array
     */
    public function getVariable(string $key): array;

    /**
     * Inject new values for adapter variables
     * 
     * @param array $values
     * @return void
     */
    public function inject(array $values): void;
}
