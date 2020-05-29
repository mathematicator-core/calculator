<h1 align="center">
    Robust PHP math calculator
</h1>

<p align="center">
    <a href="https://mathematicator.com" target="_blank">
        <img src="https://avatars3.githubusercontent.com/u/44620375?s=100&v=4">
    </a>
</p>

[![Integrity check](https://github.com/mathematicator-core/calculator/workflows/Integrity%20check/badge.svg)](https://github.com/mathematicator-core/calculator/actions?query=workflow%3A%22Integrity+check%22)
[![codecov](https://codecov.io/gh/mathematicator-core/calculator/branch/master/graph/badge.svg)](https://codecov.io/gh/mathematicator-core/calculator)
[![License: MIT](https://img.shields.io/badge/License-MIT-brightgreen.svg)](./LICENSE)
[![PHPStan Enabled](https://img.shields.io/badge/PHPStan-enabled%20L8-brightgreen.svg?style=flat)](https://phpstan.org/)

Simple to use robust and modular math library for symbolic-work with numbers, operations and patterns.

> Please help improve this documentation by sending a Pull request.

## Installation

Install by Composer:

```
composer require mathematicator-core/calculator
```

## Idea

Imagine you want compute some math problem, for instance:

```
(5 + 3) * (2 / (7 + 3))
```

How to compute it? Very simply:

```php
$calculator = new Calculator(/* some dependencies */);

echo $calculator->calculateString('(5 + 3) * (2 / (7 + 3))'); // \frac{8}{5}
```

Method `calculateString()` returns entity `CalculatorResult` that implements `__toString()` method.

Advance use is by an array of tokens created by `Tokenizer`:

```php
$tokenizer = new Tokenizer(/* some dependencies */);

// Convert math formule to array of tokens:
$tokens = $tokenizer->tokenize('(5+3)*(2/(7+3))');

// Now you can convert tokens to more useful format:
$objectTokens = $tokenizer->tokensToObject($tokens);

$calculator->calculate($objectTokens);
```

## Contribution

### Tests

All new contributions should have its unit tests in `/tests` directory.

Before you send a PR, please, check all tests pass.

This package uses [Nette Tester](https://tester.nette.org/). You can run tests via command:
```bash
composer test
````

Before PR, please run complete code check via command:
```bash
composer cs:install # only first time
composer fix # otherwise pre-commit hook can fail
````
