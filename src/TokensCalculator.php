<?php

declare(strict_types=1);

namespace Mathematicator\Calculator;


use function count;
use Mathematicator\Calculator\MathFunction\FunctionManager;
use Mathematicator\Calculator\Operation\BaseOperation;
use Mathematicator\Calculator\Operation\NumberOperationResult;
use Mathematicator\Engine\Entity\Query;
use Mathematicator\Engine\Exception\MathematicatorException;
use Mathematicator\Engine\Exception\UndefinedOperationException;
use Mathematicator\Numbers\Exception\NumberException;
use Mathematicator\Numbers\SmartNumber;
use Mathematicator\Tokenizer\Token\FactorialToken;
use Mathematicator\Tokenizer\Token\FunctionToken;
use Mathematicator\Tokenizer\Token\InfinityToken;
use Mathematicator\Tokenizer\Token\IToken;
use Mathematicator\Tokenizer\Token\NumberToken;
use Mathematicator\Tokenizer\Token\OperatorToken;
use Mathematicator\Tokenizer\Token\PolynomialToken;
use Mathematicator\Tokenizer\Token\SubToken;
use Mathematicator\Tokenizer\Token\VariableToken;
use Mathematicator\Tokenizer\TokenIterator;
use Mathematicator\Tokenizer\Tokens;

final class TokensCalculator
{
	private BaseOperation $baseOperation;

	private FunctionManager $functionManager;


	public function __construct(BaseOperation $baseOperation, FunctionManager $functionManager)
	{
		$this->baseOperation = $baseOperation;
		$this->functionManager = $functionManager;
	}


	/**
	 * @param IToken[] $tokens
	 * @throws MathematicatorException
	 */
	public function process(array $tokens, Query $query): TokensCalculatorResult
	{
		return $this->iterator($tokens, $query);
	}


	/**
	 * @param IToken[] $tokens
	 * @throws MathematicatorException
	 */
	private function iterator(array $tokens, Query $query, int $ttl = 1_024): TokensCalculatorResult
	{
		if ($ttl <= 0) {
			throw new MathematicatorException('Can not solve, because Calculator is in infinity recursion.');
		}

		$resultEntity = new TokensCalculatorResult;
		$result = [];
		$wasMatched = false;
		$iterator = new TokenIterator($tokens);

		while (true) {
			$token = $iterator->getToken();

			if ($wasMatched === true) {
				$result[] = $token;
			} elseif (
				$token instanceof NumberToken
				|| $token instanceof VariableToken
				|| $token instanceof InfinityToken
			) {
				if (($newEntity = $this->solveNumberToken($iterator, $query)) !== null) {
					if ($newEntity instanceof InfinityToken) {
						$result[] = $newEntity;
						$resultEntity->setStepDescription('Operace s nekonečnem');
					} elseif ($newEntity instanceof VariableToken) {
						$result[] = $newEntity;
						$iterator->next(2);
						$resultEntity->setStepDescription('Vynásobení proměnných');
					} elseif ($newEntity instanceof PolynomialToken) {
						$result[] = $newEntity;
						$iterator->next($newEntity->isAutoPower() ? 2 : 4);
						$resultEntity->setStepTitle('Převod na mnohočlen')
							->setStepDescription('Výraz: \(' . $newEntity->getToken() . '\)');
					} elseif ($newEntity instanceof NumberOperationResult) {
						$result[] = $newEntity->getNumber();
						$resultEntity->setStepTitle($newEntity->getTitle())
							->setStepDescription($newEntity->getDescription())
							->setAjaxEndpoint($newEntity->getAjaxEndpoint());
						$iterator->next($newEntity->getIteratorStep());
					}

					$wasMatched = true;
				} else {
					$result[] = $token;
					$resultEntity->setStepDescription('Přepsání výrazu');
				}
			} elseif ($token instanceof SubToken) {
				if (count($token->getTokens()) === 1) {
					if ($token instanceof FunctionToken) {
						$inputToken = $token->getTokens()[0];
						$resultEntity->setStepTitle(
							'Zavolání funkce ' . $token->getName()
							. '(' . ($inputToken instanceof NumberToken
								? $inputToken->getNumber()->toHumanString()
								: $inputToken->getToken()
							) . ')',
						);
						if (
							$token->getName() === ''
							|| ($functionResult = $this->functionManager->solve($token->getName(), $inputToken)) === null
						) {
							$result[] = $inputToken;
						} else {
							$result[] = $functionResult->getOutput();

							if ($functionResult->getStep() !== null) {
								$resultEntity->setStepDescription($functionResult->getStep()->getDescription())
									->setAjaxEndpoint($functionResult->getStep()->getAjaxEndpoint());
							}
						}
					} else {
						$resultEntity->setStepTitle('Odstranění závorky');
						$result[] = $token->getTokens()[0];
					}
				} else {
					$_result = $this->iterator($token->getTokens(), $query, $ttl - 1);
					$resultEntity->setStepTitle($_result->getStepTitle())
						->setStepDescription($_result->getStepDescription())
						->setAjaxEndpoint($_result->getAjaxEndpoint());
					$token->setObjectTokens(
						(static function (array $results) {
							if (count($results) === 1) {
								return ($results[0] ?? null) === null ? null : [$results[0]];
							}

							return $results;
						})($_result->getResult()),
					);
					$result[] = $token;
				}
				$wasMatched = true;
			} elseif ($token instanceof FactorialToken) {
				$newEntity = $this->baseOperation->processFactorial($token);
				$result[] = $newEntity->getNumber();
				$resultEntity->setStepTitle($newEntity->getTitle())
					->setStepDescription($newEntity->getDescription())
					->setAjaxEndpoint($newEntity->getAjaxEndpoint());
			} elseif (
				$token instanceof OperatorToken
				&& count($result) === 0
				&& $iterator->getNextToken() instanceof NumberToken
			) {
				$result[] = (new NumberToken(SmartNumber::of(0)))
					->setPosition($token->getPosition())
					->setToken('0')
					->setType(Tokens::M_NUMBER);
				$result[] = $token;
				$wasMatched = true;
			} else {
				$result[] = $token;
			}

			if ($wasMatched === false) {
				$this->orderByType($iterator);
			}

			$iterator->next();
			if ($iterator->isFinal()) {
				break;
			}
		}

		/** @phpstan-ignore-next-line TODO */
		return $resultEntity->setResult($result);
	}


	/**
	 * @throws UndefinedOperationException|MathematicatorException|NumberException
	 */
	private function solveNumberToken(
		TokenIterator $iterator,
		Query $query
	): IToken|Operation\NumberOperationResult|InfinityToken|VariableToken|null {
		$leftNumber = $iterator->getToken();
		$rightNumber = $iterator->getNextToken(2);
		$operator = $iterator->getNextToken();
		$nextOperator = $iterator->getNextToken(3);

		// 1. Polynomial in format `a * x^b`
		if ($leftNumber instanceof NumberToken
			&& $rightNumber instanceof VariableToken
			&& $operator instanceof OperatorToken && $operator->getToken() === '*'
			&& !$iterator->getNextToken(4) instanceof SubToken
		) {
			$powerToken = $iterator->getNextToken(4);
			if ($powerToken instanceof NumberToken
				&& $nextOperator instanceof OperatorToken && $nextOperator->getToken() === '^'
			) { // Format a * x^b
				return new PolynomialToken($leftNumber, $powerToken, $rightNumber);
			}

			// Format a * x^1
			return new PolynomialToken($leftNumber, null, $rightNumber);
		}

		// 2. Variable times number without power in format `x * n` or `n * x`
		if (($leftNumber instanceof VariableToken && $rightNumber instanceof NumberToken)
				|| ($leftNumber instanceof NumberToken && $rightNumber instanceof VariableToken) && $operator instanceof OperatorToken && $operator->getToken() === '*'
			&& ($nextOperator instanceof OperatorToken && $nextOperator->getToken() === '^') === false
		) {
			/** @var VariableToken|null $variable */
			$variable = $leftNumber instanceof VariableToken ? $leftNumber : $rightNumber;
			/** @var NumberToken|null $number */
			$number = $leftNumber instanceof NumberToken ? $leftNumber : $rightNumber;

			if ($variable !== null && $number !== null) {
				if (($newVariable = $this->baseOperation->process(new NumberToken($variable->getTimes()), $number, '*', $query)) === null) {
					return null;
				}

				return (new VariableToken(
					$variable->getToken(),
					$newVariable->getNumber()->getNumber(),
				))->setPosition($variable->getPosition());
			}
		}

		// 3. Variable times variable in format `x [+-*/] y` for `x === y`
		if ($leftNumber instanceof VariableToken
			&& $rightNumber instanceof VariableToken
			&& $operator instanceof OperatorToken
			&& $leftNumber->getToken() === $rightNumber->getToken()
		) {
			$newVariable = $this->baseOperation->process(
				new NumberToken($leftNumber->getTimes()),
				new NumberToken($rightNumber->getTimes()),
				$operator->getToken(),
				$query,
			);

			if ($newVariable === null) {
				return null;
			}

			return (new VariableToken(
				$leftNumber->getToken(),
				$newVariable->getNumber()->getNumber(),
			))->setPosition($leftNumber->getPosition());
		}

		// 4. Factorial in format `n!`
		if ($leftNumber instanceof NumberToken && $operator instanceof OperatorToken && $operator->getToken() === '!') {
			return $this->baseOperation->processNumberToFactorial($leftNumber);
		}

		if ($leftNumber !== null && $operator !== null && $rightNumber !== null) {
			if (($leftNumber instanceof InfinityToken || $rightNumber instanceof InfinityToken)
				&& ($tryInfinity = $this->solveInfinityToken($iterator, $iterator->getNextToken())) !== null
			) {
				return $tryInfinity;
			}

			if ($leftNumber instanceof NumberToken
				&& $operator instanceof OperatorToken
				&& $rightNumber instanceof NumberToken
				&& ($nextOperator === null
					|| ($nextOperator instanceof OperatorToken && $nextOperator->getPriority() <= $operator->getPriority())
					|| !$nextOperator instanceof OperatorToken
				)
			) {
				return $this->baseOperation->process($leftNumber, $rightNumber, $operator->getToken(), $query);
			}
		}

		return null;
	}


	/**
	 * @throws UndefinedOperationException
	 */
	private function solveInfinityToken(
		TokenIterator $iterator,
		IToken|OperatorToken |null $operator
	): Operation\NumberOperationResult|InfinityToken|null {
		if (($leftNumber = $iterator->getToken()) !== null && ($rightNumber = $iterator->getNextToken(2)) !== null
			&& ($leftNumber instanceof InfinityToken || $rightNumber instanceof InfinityToken)
			&& ($leftNumber instanceof NumberToken || $leftNumber instanceof InfinityToken)
			&& ($rightNumber instanceof NumberToken || $rightNumber instanceof InfinityToken)
			&& $operator instanceof OperatorToken
			&& (($nextOperator = $iterator->getNextToken(3)) === null
				|| ($nextOperator instanceof OperatorToken && $nextOperator->getPriority() <= $operator->getPriority())
				|| !$nextOperator instanceof OperatorToken
			)
		) {
			return $this->baseOperation->processInfinity($leftNumber, $rightNumber, $operator->getToken());
		}

		return null;
	}


	private function orderByType(TokenIterator $iterator): TokenIterator
	{
		return $iterator;
	}
}
