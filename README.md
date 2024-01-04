<p align="center">
    <a href="https://github.com/avx" target="_blank">
        <img src="https://avapardaz.ir/_nuxt/img/avapardaz-logo.56c88f3.png" height="100px">
    </a>
    <h1 align="center">Avapardaz Contract Interpreter Manager</h1>
    <br>
</p>

Contract interpreter manager handle interpreting and storing values for any entity that is interpretable.

<br/>

## INSTALLATION

### Install via composer

Install the package using the following command:

```
composer require avapardaz/interpreter-manager
```

<br/>

## CONFIGURATION

For any entity or model that is interpretable, do the following:

Add package Interpreable interface on interpretable entity. For example:

```php
namespace app\models;

use Avapardaz\InterpreterManager;

class Contract implements Interpretable
{
    ...

```

Then add package InterpretableTemplate interface on interprable template entity. For example:

```php
namespace app\models;

use Avapardaz\InterpreterManager;

class ContractTemplate implements InterpretableTemplate
{
    ...

```

And lastly create a InterpreterManager class which extends InterpreterManager where you set default configuations and overrides. For example:

```php
namespace app\services;

use Avapardaz\InterpreterManager;

class ContractInterpreterManager extends InterpreterManager
{

```

Here is an example of full InterpreterManager methods with types:

```php
<?php

namespace common\services;

use app\models\Contract;
use Avapardaz\InterpreterManager\Adapters\BaseAdapter;
use Avapardaz\InterpreterManager\Adapters\ModelAdapter;
use Avapardaz\InterpreterManager\InterpreterManager;

class ContractInterpreterManager extends InterpreterManager
{
    /**
     * Extraction pattern
     *
     * @var string
     */
    public string $extractionPattern = '/{(.*?)}/';

    /**
     * The interpreter default adapters
     *
     * @return array
     */
    protected function defaults(): array
    {
        return [
            'adapters' => [

                BaseAdapter::class => [
                    'variables' => [
                        [
                            'label' => 'Current date',
                            'key' => 'CURRENT_DATE',
                            'fillable' => false,
                            'defaultValue' => '' // can be anything as default or can set in when interpreting via inject
                        ],
                        // ...
                    ]
                ],

                ModelAdapter::class => [
                    'variables' => [
                        [
                            'label' => 'Contract',
                            'key' => 'CONTRACT',
                            'model' => Contract::class,
                            'attributes' => ['id', 'name'],
                            'attributeLabels' => [
                                'id' => 'ID',
                                'name' => 'Name',
                                // ...
                            ]
                        ],
                        // ...
                    ]
                ],

                // ...
            ]
        ];
    }
}
```

<br/>

## API Reference

### InterpreterManager

All the operations implemented using this class

| Property                                 | Type                                        | Default Value | Description                                                                                  |
| ---------------------------------------- | ------------------------------------------- | ------------- | -------------------------------------------------------------------------------------------- |
| $extractionPattern                       | string                                      | `'/{(.*?)}/'` | The pattern that use to extract variables from interpretable template content                |
| defaults()                               | fn() : array                                |               | Default interpreter adapters for global variables                                            |
| variables()                              | fn() : array                                |               | Return array of all interpreter registered variables with their label, key and default value |
| inject(array $values)                    | fn($values : array) : InterpreterManager    |               | Inject values to interpreter at runtime to override default values with exact values         |
| extract(InterpretableTemplate $template) | fn(InterpretableTemplate $template) : array |               | Extract all interpretable variable from interpretable template                               |
| storables(Interpretable $interpretable)  | fn(Interpretable $interpretable) : array    |               | Get all interpretables storable variable along their values                                  |
| interpret(Interpretable $interpretable)  | fn(Interpretable $interpretable) : string   |               | Interpret interpretable using registered variables                                           |
