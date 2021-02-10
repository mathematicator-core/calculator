<?php

declare(strict_types=1);

namespace Mathematicator\Calculator\MathFunction;


use Mathematicator\Engine\Step\Step;
use Mathematicator\Tokenizer\Token\IToken;

/**
 * @property IToken|IToken[] $input
 * @property IToken|IToken[] $output
 * @property Step|null $step
 */
class FunctionResult
{
	/** @var IToken|IToken[] */
	private IToken|array $input;

	/** @var IToken|IToken[] */
	private IToken|array $output;

	private ?Step $step = null;


	/**
	 * @return IToken|IToken[]
	 */
	public function getInput(): IToken|array
	{
		return $this->input;
	}


	/**
	 * @param IToken|IToken[] $input
	 */
	public function setInput(IToken|array $input): self
	{
		$this->input = $input;

		return $this;
	}


	/**
	 * @return IToken|IToken[]
	 */
	public function getOutput(): IToken|array
	{
		return $this->output;
	}


	/**
	 * @param IToken|IToken[] $output
	 */
	public function setOutput(IToken|array $output): self
	{
		$this->output = $output;

		return $this;
	}


	public function getStep(): ?Step
	{
		return $this->step;
	}


	public function setStep(Step $steps): self
	{
		$this->step = $steps;

		return $this;
	}
}
