<?php

declare(strict_types=1);

namespace Mathematicator\Calculator\Step\Controller;


use Brick\Math\BigDecimal;
use Mathematicator\Calculator\Helpers\FractionHelper;
use Mathematicator\Calculator\Latex\LatexBuilder;
use Mathematicator\Calculator\Numbers\NumberHelper;
use Mathematicator\Engine\Exception\MathematicatorException;
use Mathematicator\Engine\Step\Step;
use Nette\Application\UI\InvalidLinkException;
use Nette\Utils\ArrayHash;

final class StepPlusController implements IStepController
{

	/** @var NumberHelper */
	private $number;


	/**
	 * @param NumberHelper $number
	 */
	public function __construct(NumberHelper $number)
	{
		$this->number = $number;
	}


	/**
	 * @param ArrayHash $data
	 * @return Step[]
	 * @throws InvalidLinkException
	 * @throws MathematicatorException
	 */
	public function actionDefault(ArrayHash $data): array
	{
		$steps = [];

		$x = FractionHelper::stringToSimpleFraction($data->x);
		$xNumerator = BigDecimal::of((string) $x->getNumerator());
		$xDenominator = BigDecimal::of((string) $x->getDenominator());

		$y = FractionHelper::stringToSimpleFraction($data->y);
		$yNumerator = BigDecimal::of((string) $y->getNumerator());
		$yDenominator = BigDecimal::of((string) $y->getDenominator());

		if ($xDenominator->isEqualTo(1) && $yDenominator->isEqualTo('1')) {
			$steps[] = new Step(
				'Sčítání čísel',
				null,
				$this->number->getAddStepAsHtml((string) $x->getNumerator(), (string) $y->getNumerator())
			);
		} else {
			$steps[] = new Step(
				'Sčítání čísel',
				LatexBuilder::create(FractionHelper::fractionToLatex($x, true))
					->plus(FractionHelper::fractionToLatex($y, true))
					->__toString()
			);

			$sp = $xDenominator->multipliedBy($yDenominator);

			$steps[] = new Step(
				'Nalezení společného jmenovatele',
				LatexBuilder::create((string) $xDenominator)
					->multipliedBy((string) $yDenominator)
					->equals((string) $sp)
					->__toString()
			);

			$left = $yDenominator->multipliedBy((string) $xNumerator)
				->plus($xDenominator->multipliedBy((string) $yNumerator));

			$steps[] = new Step(
				'Převod na jeden zlomek',
				LatexBuilder::frac((string) $xNumerator, (string) $xDenominator)
					->plus(LatexBuilder::frac((string) $yNumerator, (string) $yDenominator))
					->equals(
						LatexBuilder::frac(
							LatexBuilder::create((string) $yDenominator)
								->multipliedBy((string) $xNumerator)
								->plus((string) $xDenominator)
								->multipliedBy((string) $yNumerator)
								->__toString(),
							(string) $sp
						)
					)
					->equals(LatexBuilder::frac((string) $left, (string) $sp))
					->__toString()
			);
		}

		return $steps;
	}

}
