<?php

declare(strict_types=1);

namespace Mathematicator\MathFunction;


use Mathematicator\Engine\MathErrorException;
use Mathematicator\Numbers\NumberException;
use Mathematicator\Step\Controller\StepSqrtController;
use Mathematicator\Step\StepFactory;
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
			throw new MathErrorException('Sqrt is smaller than 0, but number "' . $number->getHumanString() . '" given.');
		}

		$sqrt = bcsqrt($number->getFloatString(), 100);
		$token->getNumber()->setValue($sqrt);
		$token->setToken($sqrt);

		$step = StepFactory::addStep();
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
