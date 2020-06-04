<?php

declare(strict_types=1);

namespace Mathematicator\Calculator\Operation;


use Mathematicator\Calculator\Step\Controller\StepPlusController;
use Mathematicator\Calculator\Step\StepFactory;
use Mathematicator\Engine\Entity\Query;
use Mathematicator\Numbers\Calculation;
use Mathematicator\Numbers\SmartNumber;
use Mathematicator\Tokenizer\Token\NumberToken;

class AddNumbers
{


	/**
	 * @param NumberToken $left
	 * @param NumberToken $right
	 * @param Query $query
	 * @return NumberOperationResult
	 */
	public function process(NumberToken $left, NumberToken $right, Query $query): NumberOperationResult
	{
		$leftNumber = $left->getNumber();
		$rightNumber = $right->getNumber();

		if ($leftNumber->isInteger() && $rightNumber->isInteger()) {
			$result = Calculation::of($leftNumber)->plus($rightNumber);
		} else {
			$leftFraction = $leftNumber->toFraction();
			$rightFraction = $rightNumber->toFraction();

			$result = $rightFraction->getDenominator()->multipliedBy($leftFraction->getNumerator())
					->plus(
						$leftFraction->getDenominator()->multipliedBy($rightFraction->getNumerator())
					) . '/' .
				$leftFraction->getDenominator()->multipliedBy($rightFraction->getDenominator());
		}

		$newNumber = new NumberToken(SmartNumber::of($result));
		$newNumber
			->setToken($newNumber->getNumber()->getString())
			->setPosition($left->getPosition())
			->setType('number');

		$_left = $leftNumber->toHumanString();
		$_right = $rightNumber->toHumanString();

		return (new NumberOperationResult)
			->setNumber($newNumber)
			->setDescription(
				'Sčítání čísel '
				. (strpos($_left, '-') === 0 ? '(' . $_left . ')' : $_left)
				. ' + '
				. (strpos($_right, '-') === 0 ? '(' . $_right . ')' : $_right)
			)
			->setAjaxEndpoint(
				StepFactory::getAjaxEndpoint(StepPlusController::class, [
					'x' => $leftNumber->toHumanString(),
					'y' => $rightNumber->toHumanString(),
				])
			);
	}
}
