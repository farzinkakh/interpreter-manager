<?php

namespace Avapardaz\InterpreterManager;

interface InterpretableTemplate
{
    /**
     * Get interpretable content
     * 
     * @return string
     */
    public function interpretableContent(): string;
}
