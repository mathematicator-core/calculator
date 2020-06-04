<?php

declare(strict_types=1);

namespace Mathematicator\Calculator\Operation;


use Mathematicator\Engine\Entity\Query;
use Mathematicator\Numbers\Calculation;
use Mathematicator\Numbers\Latex\MathLatexBuilder;
use Mathematicator\Numbers\Latex\MathLatexToolkit;
use Mathematicator\Numbers\SmartNumber;
use Mathematicator\Tokenizer\Token\NumberToken;
use Nette\Utils\Validators;

final class DivisionNumbers
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
		$leftFraction = $leftNumber->toFraction();
		$rightFraction = $rightNumber->toFraction();

		if ($leftNumber->isInteger() && $rightNumber->isInteger()) {
			$bcDiv = Calculation::of($leftNumber)->dividedBy($rightNumber->getInteger(), $query->getDecimals())->getResult()->toBigInteger();
			if (Validators::isNumericInt($bcDiv)) {
				$result = $bcDiv;
			} else {
				$result = $leftNumber->toBigInteger() . '/' . $rightNumber->toBigInteger();
			}
		} else {
			$result = $leftFraction->getNumerator()->multipliedBy($rightFraction->getDenominator())
				. '/' . $leftFraction->getDenominator()->multipliedBy($rightFraction->getNumerator());
		}

		$newNumber = new NumberToken(SmartNumber::of($result));
		$newNumber->setToken((string) $newNumber->getNumber())
			->setPosition($left->getPosition())
			->setType('number');

		return (new NumberOperationResult())
			->setNumber($newNumber)
			->setTitle('Dělení čísel')
			->setDescription(
				'Na dělení dvou čísel se můžeme dívat také jako na zlomek. '
				. 'Čísla převedeme na zlomek, který se následně pokusíme zkrátit (pokud to bude možné).'
				. "\n\n"
				. $this->renderDescription($leftNumber, $rightNumber, $newNumber->getNumber())
				. "\n"
			);
	}


	/**
	 * @param SmartNumber $left
	 * @param SmartNumber $right
	 * @param SmartNumber $result
	 * @return string
	 */
	private function renderDescription(SmartNumber $left, SmartNumber $right, SmartNumber $result): string
	{
		$isEqual = ($left->toHumanString() . '/' . $right->toHumanString()) === $result->toHumanString();

		$fraction = MathLatexToolkit::frac($left, $right);

		$return = !$isEqual
			? 'Zlomek \(' . $fraction . '\) lze zkrátit na \(' . $result->getString() . '\).'
			: 'Zlomek \(' . $fraction . '\) je v základním tvaru a nelze dále zkrátit.';

		$returnLatex = (new MathLatexBuilder($left->toLatex()))
			->dividedBy($right->toLatex());

		if (!$isEqual) {
			$returnLatex->equals($fraction);
		}

		$returnLatex->equals($result->toLatex())
			->wrap("\n\n\\(", "\\)\n");

		return $return . $returnLatex;
	}
}
