<?php

declare(strict_types=1);

namespace Mathematicator\Calculator\MathFunction;


use Mathematicator\Calculator\MathFunction\Functions\AbsFunction;
use Mathematicator\Calculator\MathFunction\Functions\SinFunction;
use Mathematicator\Calculator\MathFunction\Functions\SqrtFunction;
use Mathematicator\Engine\Exception\MathematicatorException;
use Mathematicator\Tokenizer\Token\IToken;
use Psr\Container\ContainerInterface;

class FunctionManager
{

	/** @var string[][] */
	private static $functions = [
		'sqrt' => [
			SqrtFunction::class,
		],
		'abs' => [
			AbsFunction::class,
		],
		'sin' => [
			SinFunction::class,
		],
	];

	/** @var ContainerInterface */
	private $container;

	/** @var IFunction */
	private $callback;


	/**
	 * @param ContainerInterface $container
	 */
	public function __construct(ContainerInterface $container)
	{
		$this->container = $container;
	}


	/**
	 * @param string $function
	 * @param IToken $token
	 * @return FunctionResult|null
	 * @throws MathematicatorException
	 */
	public function solve(string $function, IToken $token): ?FunctionResult
	{
		if (!isset(self::$functions[$function])) {
			throw new FunctionDoesNotExistsException('Function [' . $function . '] does not exists.', 500, null, $function);
		}

		foreach (self::$functions[$function] as $callback) {
			$this->callCallback($callback);

			if ($this->callback->isValidInput($token)) {
				return $this->callback->process($token);
			}
		}

		return null; // If token can't solve
	}


	/**
	 * @param string $callback
	 */
	private function callCallback(string $callback): void
	{
		$this->callback = $this->container->get($callback);
	}
}
