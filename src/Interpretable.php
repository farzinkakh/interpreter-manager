<?php

namespace Avapardaz\InterpreterManager;

interface Interpretable
{
    /**
     * Get variables
     * 
     * @return array
     */
    public function interpretableVariables(): array;

    /**
     * Get template
     * 
     * @return InterpretableTemplate
     */
    public function interpretableTemplate(): InterpretableTemplate;
}
