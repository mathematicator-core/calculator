<?php

declare(strict_types=1);

namespace Mathematicator\Calculator;


use Mathematicator\Tokenizer\Token\BaseToken;
use Mathematicator\Tokenizer\Token\IToken;
use RuntimeException;

/**
 * @property BaseToken[] $result
 * @property string|null $stepTitle
 * @property string|null $stepDescription
 * @property bool $wasModified
 * @property string|null $ajaxEndpoint
 */
class TokensCalculatorResult
{
	/** @var BaseToken[] */
	private array $result;

	private ?string $stepTitle = null;

	private ?string $stepDescription = null;

	private bool $wasModified = false;

	private ?string $ajaxEndpoint = null;


	/**
	 * @return BaseToken[]
	 */
	public function getResult(): array
	{
		return $this->result;
	}


	/**
	 * @param IToken[] $result
	 */
	public function setResult(array $result): self
	{
		$return = [];
		foreach ($result as $item) {
			if (!$item instanceof BaseToken) {
				throw new RuntimeException('Result item should be instance of "' . BaseToken::class . '".');
			}
			$return[] = $item;
		}

		$this->result = $return;

		return $this;
	}


	public function getStepTitle(): ?string
	{
		return $this->stepTitle;
	}


	public function setStepTitle(?string $stepTitle = null): self
	{
		$this->stepTitle = $stepTitle;

		return $this;
	}


	public function getStepDescription(): ?string
	{
		return $this->stepDescription;
	}


	public function setStepDescription(string $stepDescription = null): self
	{
		$this->stepDescription = $stepDescription;

		return $this;
	}


	public function getWasModified(): bool
	{
		return $this->wasModified;
	}


	public function setWasModified(bool $wasModified): self
	{
		$this->wasModified = $wasModified;

		return $this;
	}


	public function getAjaxEndpoint(): ?string
	{
		return $this->ajaxEndpoint;
	}


	public function setAjaxEndpoint(string $ajaxEndpoint = null): self
	{
		$this->ajaxEndpoint = $ajaxEndpoint;

		return $this;
	}
}
