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

### Add package registery

Add the package registry URL to your project’s `composer.json` file using the following command:

```
composer config repositories.908 composer https://git.abrsa.ir/api/v4/group/908/-/packages/composer/packages.json
```

Result in the `composer.json` file:

```json
{
    ...,
    "repositories": [
        {
            "type": "composer",
            "url": "https://git.abrsa.ir/api/v4/group/908/-/packages/composer/packages.json"
        },
        ...
    ]
}
```

### Add GitLab auth token

Create an `auth.json` file with your GitLab credentials:

#### Using a personal access token:

```
composer config gitlab-token.git.abrsa.ir <personal_access_token>
```

Result in the `auth.json` file:

```json
{
  ...
  "gitlab-token": {
    "git.abrsa.ir": "<personal_access_token>",
    ...
  }
}
```

#### Using a deploy token:

```
composer config gitlab-token.git.abrsa.ir <deploy_token_username> <deploy_token>
```

Result in the `auth.json` file:

```json
{
  ...
  "gitlab-token": {
    "git.abrsa.ir": {
      "username": "<deploy_token_username>",
      "token": "<deploy_token>",
    ...
  }
}
```

### Install via composer

Install the package using the following command:

```
composer require avapardaz/interpreter-manager
```

<br/>

## CONFIGURATION

For every entity that is interpretable, you must create a class which extends from InterpreterManager. For example:

```php
namespace app\services;

use Avapardaz\InterpreterManager;

class ContractInterpreterManager extends InterpreterManager
{
    ...
```
<br/>

## API Reference

### InterpreterManager

All the operations implemented using this class

| Property                                 | Type                                             | Default Value    | Description    
| ---------------------------------------- | ------------------------------------------------ | ---------------- | --------------
| $extractionPattern                       | string                                           | `'/{(.*?)}/'`    | The pattern that use to extract variables from interpretable template content
| defaults()                               | fn() : array                                     |                  | Default interpreter adapters for global variables
| variables()                              | fn() : array                                     |                  | Return array of all interpreter registered variables with their label, key and default value
| inject(array $values)                    | fn($values : array) : InterpreterManager         |                  | Inject values to interpreter at runtime to override default values with exact values
| extract(InterpretableTemplate $template) | fn(InterpretableTemplate $template) : array      |                  | Extract all interpretable variable from interpretable template
| storables(Interpretable $interpretable)  | fn(Interpretable $interpretable) : array         |                  | Get all interpretables storable variable along their values
| interpret(Interpretable $interpretable)  | fn(Interpretable $interpretable) : string        |                  | Interpret interpretable using registered variables