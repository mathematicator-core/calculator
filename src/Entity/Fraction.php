<?php

declare(strict_types=1);

namespace Mathematicator\Calculator\Entity;

use Brick\Math\BigNumber;
use Doctrine\ORM\Mapping as ORM;
use Stringable;

/**
 * Single entity to store simple and compound fractions
 *
 * @ORM\Entity
 */
class Fraction
{
	/**
	 * @var int
	 * @ORM\Id
	 * @ORM\Column(type="integer")
	 * @ORM\GeneratedValue
	 */
	protected $id;
	/**
	 * @var Fraction|null
	 * @ORM\Column(type="string", nullable=true)
	 */
	protected $numeratorFraction;
	/**
	 * @var string|null
	 * @ORM\Column(type="string", nullable=true)
	 */
	protected $numeratorString;
	/**
	 * @var Fraction|null
	 * @ORM\Column(type="string", nullable=true)
	 */
	protected $denominatorFraction;
	/**
	 * @var string|null
	 * @ORM\Column(type="string", nullable=true)
	 */
	protected $denominatorString;
	/**
	 * Superior fraction (if in compound structure)
	 *
	 * @var Fraction|null
	 * @ORM\OneToMany(targetEntity="Fraction", mappedBy="numeratorFraction")
	 */
	protected $parentInNumerator;
	/**
	 * Superior fraction (if in compound structure)
	 *
	 * @var Fraction|null
	 * @ORM\OneToMany(targetEntity="Fraction", mappedBy="denominatorFraction")
	 */
	protected $parentInDenominator;

	/**
	 * @param string|Stringable|BigNumber|Fraction|null $numerator optional
	 * @param string|Stringable|BigNumber|Fraction|null $denominator optional
	 */
	public function __construct($numerator = null, $denominator = null)
	{
		if ($numerator) {
			$this->setNumerator($numerator);
		}
		if ($denominator) {
			$this->setDenominator($denominator);
		}
	}

	public function __toString(): string
	{
		return $this->getNumerator() . '/' . $this->getDenominator();
	}

	/**
	 * @return Fraction|string|null
	 */
	public function getNumerator()
	{
		if ($this->numeratorFraction) {
			return $this->numeratorFraction;
		} else {
			return $this->numeratorString;
		}
	}

	/**
	 * @param int|string|Stringable|BigNumber|Fraction $numerator
	 */
	public function setNumerator($numerator): void
	{
		if ($numerator instanceof self) {
			$this->numeratorString = null;
			$this->numeratorFraction = $numerator;
		} else {
			$this->numeratorFraction = null;
			$this->numeratorString = (string) $numerator;
		}
	}

	/**
	 * @return Fraction|string|null
	 */
	public function getDenominator()
	{
		if ($this->denominatorFraction) {
			return $this->denominatorFraction;
		} else {
			return $this->denominatorString;
		}
	}

	/**
	 * @return Fraction|string
	 */
	public function getDenominatorNotNull()
	{
		return $this->getDenominator() ?: '1';
	}

	/**
	 * @param int|string|Stringable|BigNumber|Fraction $denominator
	 */
	public function setDenominator($denominator): void
	{
		if ($denominator instanceof Fraction) {
			$this->denominatorString = null;
			$this->denominatorFraction = $denominator;
		} else {
			$this->denominatorFraction = null;
			$this->denominatorString = (string) $denominator;
		}
	}

	public function getParent(): ?Fraction
	{
		if ($this->parentInNumerator) {
			return $this->parentInNumerator;
		} else {
			return $this->parentInDenominator;
		}
	}

}
