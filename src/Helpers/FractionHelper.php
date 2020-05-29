<?php

declare(strict_types=1);

namespace Mathematicator\Calculator\Helpers;

use Brick\Math\BigNumber;
use Mathematicator\Calculator\Entity\Fraction;
use Mathematicator\Calculator\Latex\LatexBuilder;
use Mathematicator\Engine\Exception\MathematicatorException;


class FractionHelper
{

	/**
	 * @param string $input
	 * @return Fraction
	 * @throws MathematicatorException
	 */
	public static function stringToSimpleFraction(string $input): Fraction
	{
		$explode = explode('/', $input);

		$fraction = new Fraction();

		switch (count($explode)) {
			case 1:
				$fraction->setNumerator($input);
				$fraction->setDenominator(1);
				return $fraction;
			case 2:
				$fraction->setNumerator($explode[0]);
				$fraction->setDenominator($explode[1]);
				return $fraction;
			default:
				throw new MathematicatorException("Parsing of compound fractions is not supported.");
		}
	}

	/**
	 * @param Fraction $fraction
	 * @param bool $simplify Remove denominator if it is unnecessary
	 * @return string
	 */
	public static function fractionToLatex(Fraction $fraction, $simplify = false): string
	{
		$numerator = $fraction->getNumerator();
		$denominator = $fraction->getDenominatorNotNull();

		// Simplify (remove denominator) if it's wanted and possible
		if ($simplify && BigNumber::of((string) $denominator)->isEqualTo(1)) {
			return (string) $numerator;
		}

		// Compose LaTeX
		if ($numerator instanceof Fraction) {
			$numeratorLatex = self::fractionToLatex($numerator, $simplify);
		} else {
			$numeratorLatex = (string) $numerator;
		}

		if ($denominator instanceof Fraction) {
			$denominatorLatex = self::fractionToLatex($denominator, $simplify);
		} else {
			$denominatorLatex = $denominator;
		}

		// Create LaTeX
		return (string) LatexBuilder::frac($numeratorLatex, $denominatorLatex);
	}

}
