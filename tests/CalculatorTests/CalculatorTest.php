<?php

declare(strict_types=1);

namespace Mathematicator\Calculator\Tests;


use Mathematicator\Calculator\Calculator;
use Mathematicator\Engine\Query;
use Nette\DI\Container;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../bootstrap.php';

class CalculatorTest extends TestCase
{
	/**
	 * @var Calculator
	 */
	private $calculator;

	public function __construct(Container $container)
	{
		$this->calculator = $container->getService('calculator');
	}

	/**
	 * @dataprovider getCalculateStringCases
	 * @param string $expected
	 * @param string $query
	 */
	public function testCalculateString(string $expected, string $query): void
	{
		Assert::same($expected, (string)$this->calculator->calculateString(new Query($query, $query)));
	}

	/**
	 * @return string[]
	 */
	public function getCalculateStringCases(): array
	{
		return [
			['1', '1'],
			['2', '1+1'],
			['27', '5*5+2'],
			['5', '10/2'],
			['-1', '-1'],
			['4', '2^2'],
			['8', '(5 + 3)'],
			['\frac{8}{5}', '(5 + 3) * 2/10'],
			['\frac{8}{5}', '(5 + 3) * (2 / (7 + 3))'],
			['121', '11^2'],
			['24', '4!'],
			// TODO: ['2-x','(1*2)-x']
			// TODO: ['x', 'x/1'],
			// TODO: ['1/x','1/x'],
		];
	}
}

if (isset($_SERVER['NETTE_TESTER_RUNNER'])) {
	$container = Bootstrap::boot();
	(new CalculatorTest($container))->run();
}
