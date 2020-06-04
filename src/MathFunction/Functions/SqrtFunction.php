<?php

declare(strict_types=1);

namespace Mathematicator\Calculator\MathFunction\Functions;


use Mathematicator\Calculator\MathFunction\FunctionResult;
use Mathematicator\Calculator\MathFunction\IFunction;
use Mathematicator\Calculator\Step\Controller\StepSqrtController;
use Mathematicator\Calculator\Step\StepFactory;
use Mathematicator\Engine\Exception\MathErrorException;
use Mathematicator\Engine\Step\Step;
use Mathematicator\Numbers\Exception\NumberException;
use Mathematicator\Tokenizer\Token\IToken;
use Mathematicator\Tokenizer\Token\NumberToken;

class SqrtFunction implements IFunction
{

	/**
	 * @param NumberToken|IToken $token
	 * @return FunctionResult
	 * @throws MathErrorException|NumberException
	 */
	public function process(IToken $token): FunctionResult
	{
		assert($token instanceof NumberToken);
		$result = new FunctionResult();
		$number = $token->getNumber();

		if ($number->isNegative() === true) {
			throw new MathErrorException('Sqrt is smaller than 0, but number "' . $number->toHumanString() . '" given.');
		}

		$sqrt = bcsqrt($number->getFloatString(), 100);
		$token->getNumber()->setValue($sqrt);
		$token->setToken($sqrt);

		$step = new Step();
		$step->setAjaxEndpoint(
			StepFactory::getAjaxEndpoint(StepSqrtController::class, [
				'n' => $number->getFloat(),
			])
		);

		$result->setStep($step);
		$result->setOutput($token);

		return $result;
	}


	/**
	 * @param IToken $token
	 * @return bool
	 */
	public function isValidInput(IToken $token): bool
	{
		return $token instanceof NumberToken;
	}
}
