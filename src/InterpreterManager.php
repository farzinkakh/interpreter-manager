<?php

namespace Avapardaz\InterpreterManager;

abstract class InterpreterManager
{
    use Singleton;

    /**
     * The interpreter
     * 
     * @var Interpreter
     */
    private Interpreter $interpreter;

    /**
     * Extraction pattern
     * 
     * @var string
     */
    protected string $extractionPattern = '/{(.*?)}/';

    /**
     * Create the constract interpreter instance
     * 
     */
    protected function __construct()
    {
        $this->interpreter = Interpreter::make($this->defaults());
    }

    /**
     * The interpreter default adapters
     * 
     * @return array
     */
    protected function defaults(): array
    {
        return [];
    }

    /**
     * Get interpreter all variables
     * 
     * @return array
     */
    public function variables(): array
    {
        return $this->interpreter->variables();
    }

    /**
     * inject interpreter values
     * 
     * @param array $values
     * @return InterpreterManager
     */
    public function inject(array $values): InterpreterManager
    {
        $this->interpreter->inject($values);
        return $this;
    }

    /**
     * Extract all template variables
     * 
     * @param InterpretableTemplate $template
     * @return array
     */
    private function extractAll(InterpretableTemplate $template): array
    {
        if (preg_match_all($this->extractionPattern, $template->interpretableContent(), $extractions) > 0)
            return $extractions[1];
        return [];
    }

    /**
     * Extract interpretable template registered variables with default values
     * 
     * @param InterpretableTemplate $template
     * @return array
     */
    public function extract(InterpretableTemplate $template): array
    {
        $result = [];

        foreach ($this->extractAll($template) as $variable)
            if ($this->interpreter->hasVariable($variable))
                array_push($result, $this->interpreter->getVariable($variable));

        return $result;
    }

    /**
     * Get interpretable storable variables
     * 
     * @param Interpretable $interpretable
     * @return array
     */
    public function storables(Interpretable $interpretable): array
    {
        $result = [];

        foreach ($this->extract($interpretable->interpretableTemplate()) as $variable) {

            $fillable = isset($variable['fillable']) && $variable['fillable'] == true;

            if (!$fillable) {
                $result[$variable['key']] = $variable['defaultValue'];
                continue;
            }

            $result[$variable['key']] = $interpretable->variables[$variable['key']] ?? '';
        };

        return $result;
    }

    /**
     * Interpret interpretable
     * 
     * @param Interpretable $interpretable
     * @return string
     */
    public function interpret(Interpretable $interpretable): string
    {
        return $this->interpreter->interpret(
            $interpretable->interpretableTemplate()->interpretableContent(),
            $interpretable->interpretableVariables()
        );
    }
}
