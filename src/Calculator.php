<?php

declare(strict_types=1);

namespace Mathematicator\Calculator;


use function count;
use Mathematicator\Calculator\Entity\CalculatorResult;
use Mathematicator\Engine\Entity\Query;
use Mathematicator\Engine\Exception\MathematicatorException;
use Mathematicator\Engine\QueryNormalizer;
use Mathematicator\Engine\Step\Step;
use Mathematicator\Tokenizer\Token\FactorialToken;
use Mathematicator\Tokenizer\Token\FunctionToken;
use Mathematicator\Tokenizer\Token\IToken;
use Mathematicator\Tokenizer\Token\NumberToken;
use Mathematicator\Tokenizer\Token\SubToken;
use Mathematicator\Tokenizer\Tokenizer;

class Calculator
{
	private Tokenizer $tokenizer;

	private TokensCalculator $tokensCalculator;

	private QueryNormalizer $queryNormalizer;


	public function __construct(
		Tokenizer $tokenizer,
		TokensCalculator $tokensCalculator,
		QueryNormalizer $queryNormalizer
	) {
		$this->tokenizer = $tokenizer;
		$this->tokensCalculator = $tokensCalculator;
		$this->queryNormalizer = $queryNormalizer;
	}


	/**
	 * @param IToken[] $tokens
	 * @throws MathematicatorException
	 */
	public function calculate(array $tokens, Query $query, int $basicTtl = 3): CalculatorResult
	{
		$result = new CalculatorResult($tokens);

		if (count($tokens) === 1 && !($tokens[0] instanceof FunctionToken || $tokens[0] instanceof FactorialToken)) {
			$result->setResultTokens($tokens);
			$result->setSteps([]);

			return $result;
		}

		$iterator = 0;
		$steps = [];

		$interpretStep = new Step;
		$interpretStep->setTitle('Zadání úlohy');
		$interpretStep->setLatex($this->tokenizer->tokensToLatex($tokens));

		$steps[] = $interpretStep;

		$stepLatexLast = null;
		$ttl = $basicTtl;
		do {
			if ($iterator++ > 128) {
				break;
			}

			$stepLatexLast = $this->tokensSerialize($tokens);
			$process = $this->tokensCalculator->process($tokens, $query);
			$tokens = $process->getResult();

			$stepLatexCurrent = $this->tokenizer->tokensToLatex($tokens);

			$step = new Step;
			$step->setLatex($stepLatexCurrent);
			$step->setTitle($process->getStepTitle());
			$step->setDescription($process->getStepDescription());
			$step->setAjaxEndpoint($process->getAjaxEndpoint());

			$steps[] = $step;

			if ($this->tokensSerialize($tokens) === $stepLatexLast) {
				$ttl--;

				if ($ttl <= 0) {
					break;
				}
			} else {
				$ttl = $basicTtl;
			}
		} while (true);

		$result->setResultTokens($tokens);
		$result->setSteps($steps);

		return $result;
	}


	/**
	 * Human input and token output.
	 *
	 * @throws MathematicatorException
	 */
	public function calculateString(Query $query): CalculatorResult
	{
		try {
			$tokens = $this->tokenizer->tokenize(
				$this->queryNormalizer->normalize($query->getQuery()),
			);
		} catch (\Throwable $e) {
			throw new MathematicatorException($e->getMessage(), $e->getCode(), $e);
		}

		return $this->calculate(
			$this->tokenizer->tokensToObject($tokens),
			$query,
		);
	}


	/**
	 * @return IToken[]
	 * @throws MathematicatorException
	 */
	public function getTokensByString(Query $query): array
	{
		try {
			$tokens = $this->tokenizer->tokenize(
				$this->queryNormalizer->normalize($query->getQuery()),
			);
		} catch (\Throwable $e) {
			throw new MathematicatorException($e->getMessage(), $e->getCode(), $e);
		}

		return $this->tokenizer->tokensToObject($tokens);
	}


	/**
	 * @param IToken[] $tokens
	 */
	private function tokensSerialize(array $tokens = null): string
	{
		$tokensToSerialize = '';

		foreach ($tokens ?? [] as $token) {
			$tokensToSerialize .= '<{' . $token->getToken() . '}' . $token->getType() . '|';

			if ($token instanceof SubToken) {
				$tokensToSerialize .= 'SUB:' . $this->tokensSerialize($token->getTokens());
			} elseif ($token instanceof NumberToken) {
				$tokensToSerialize .= (string) $token->getNumber();
			} else {
				$tokensToSerialize .= $token->getType();
			}

			$tokensToSerialize .= '>';
		}

		return '[' . $tokensToSerialize . ']';
	}
}
