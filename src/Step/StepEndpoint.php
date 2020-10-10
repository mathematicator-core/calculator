<?php

declare(strict_types=1);

namespace Mathematicator\Calculator\Step;


use Mathematicator\Engine\Exception\TerminateException;
use Mathematicator\Engine\Step\Step;
use Nette\Utils\ArrayHash;
use Psr\Container\ContainerInterface;

final class StepEndpoint
{

	/** @var ContainerInterface */
	private $container;


	public function __construct(ContainerInterface $container)
	{
		$this->container = $container;
	}


	/**
	 * @return mixed[]
	 */
	public function getStep(string $type, string $data): array
	{
		$callback = $this->container->get($type);

		try {
			$data = json_decode($data);
			$arrayHash = new ArrayHash();
			foreach ($data as $k => $v) {
				$arrayHash->{$k} = $v;
			}
			$steps = $callback->actionDefault($arrayHash);
		} catch (TerminateException $e) {
			$steps[] = StepFactory::addStep('Nepodařilo se najít postup pro [' . $type . ']');
		}

		$return = [];

		/** @var Step[] $steps */
		foreach ($steps as $step) {
			$return[] = [
				'title' => $step->getTitle(),
				'latex' => $step->getLatex(),
				'description' => $step->getDescription(),
				'ajaxEndpoint' => $step->getAjaxEndpoint(),
			];
		}

		return $return;
	}
}
