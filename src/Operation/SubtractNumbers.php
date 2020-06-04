<?php

declare(strict_types=1);

namespace Mathematicator\Calculator\Operation;


use Mathematicator\Engine\Entity\Query;
use Mathematicator\Numbers\SmartNumber;
use Mathematicator\Tokenizer\Token\NumberToken;

class SubtractNumbers
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
			$result = $leftNumber->getInteger()->minus($rightNumber);
		} else {
			$leftFraction = $leftNumber->getRational();
			$rightFraction = $rightNumber->getRational();

			$resultNumerator = $rightFraction->getDenominator()->multipliedBy($leftFraction->getNumerator())
				->minus($leftFraction->getDenominator()->multipliedBy($rightFraction->getNumerator()));
			$resultDenominator = $leftFraction->getDenominator()->multipliedBy($rightFraction->getDenominator());

			$result = "$resultNumerator/$resultDenominator";
		}

		$newNumber = new NumberToken(SmartNumber::of($result));
		$newNumber->setToken($newNumber->getNumber()->getString())
			->setPosition($left->getPosition())
			->setType('number');

		return (new NumberOperationResult())
			->setNumber($newNumber)
			->setDescription(
				'Odčítání čísel ' . $leftNumber->toHumanString() . ' - ' . $rightNumber->toHumanString()
			);
	}
}
