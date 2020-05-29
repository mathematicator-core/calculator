<?php

declare(strict_types=1);

namespace Mathematicator\Calculator\Latex;


use Stringable;

class LatexBuilder implements Stringable
{

	/**
	 * @var string
	 */
	private $latex;

	/**
	 * @param string|Stringable $latex
	 */
	public function __construct($latex = '')
	{
		$this->latex = (string) $latex;
	}

	/**
	 * @param string|Stringable $latex
	 * @return LatexBuilder
	 */
	public static function create($latex = ''): self
	{
		return new self($latex);
	}

	/**
	 * @param string|Stringable $numerator
	 * @param string|Stringable $denominator
	 * @return LatexBuilder
	 */
	public static function frac($numerator, $denominator): self
	{
		return new self('\frac{' . $numerator . '}{' . $denominator . '}');
	}

	public function __toString()
	{
		return $this->latex;
	}

	/**
	 * @param string|Stringable $add
	 * @return LatexBuilder
	 */
	public function plus($add): self
	{
		$this->latex = ' + ' . $add;
		return $this;
	}

	/**
	 * @param string|Stringable $with
	 * @return LatexBuilder
	 */
	public function multipliedBy($with): self
	{
		$this->latex = '\ \cdot\ ' . $with;
		return $this;
	}

	/**
	 * @param string|Stringable $to
	 * @return LatexBuilder
	 */
	public function equals($to): self
	{
		$this->latex = ' = ' . $to;
		return $this;
	}
}
