<?php

declare(strict_types=1);

namespace Mathematicator\Calculator\Operation;


use Mathematicator\Calculator\Step\Controller\StepMultiplicationController;
use Mathematicator\Calculator\Step\StepFactory;
use Mathematicator\Engine\Entity\Query;
use Mathematicator\Numbers\NumberFactory;
use Mathematicator\Tokenizer\Token\NumberToken;

class MultiplicationNumber
{

	/** @var NumberFactory */
	private $numberFactory;


	/**
	 * @param NumberFactory $numberFactory
	 */
	public function __construct(NumberFactory $numberFactory)
	{
		$this->numberFactory = $numberFactory;
	}


	/**
	 * @param NumberToken $left
	 * @param NumberToken $right
	 * @param Query $query
	 * @return NumberOperationResult
	 */
	public function process(NumberToken $left, NumberToken $right, Query $query): NumberOperationResult
	{
		if ($left->getNumber()->isInteger() && $right->getNumber()->isInteger()) {
			$result = bcmul($left->getNumber()->getInteger(), $right->getNumber()->getInteger(), $query->getDecimals());
		} else {
			$leftFraction = $left->getNumber()->getFraction();
			$rightFraction = $right->getNumber()->getFraction();

			$result = bcmul((string) $leftFraction[0], (string) $rightFraction[0], $query->getDecimals()) . '/' . bcmul((string) $leftFraction[1], (string) $rightFraction[1], $query->getDecimals());
		}

		$newNumber = new NumberToken($this->numberFactory->create($result));
		$newNumber->setToken($newNumber->getNumber()->getString());
		$newNumber->setPosition($left->getPosition());
		$newNumber->setType('number');

		return (new NumberOperationResult)
			->setNumber($newNumber)
			->setDescription(
				'Násobení čísel ' . $left->getNumber()->getHumanString() . ' * ' . $right->getNumber()->getHumanString()
			)
			->setAjaxEndpoint(
				StepFactory::getAjaxEndpoint(StepMultiplicationController::class, [
					'x' => $left->getNumber()->getHumanString(),
					'y' => $right->getNumber()->getHumanString(),
				])
			);
	}
}
