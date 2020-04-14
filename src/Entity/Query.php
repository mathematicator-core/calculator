<?php

declare(strict_types=1);

namespace Mathematicator\Calculator;


interface Query
{
	/**
	 * @return int
	 */
	public function getDecimals(): int;

	/**
	 * @return string
	 */
	public function getQuery(): string;
}