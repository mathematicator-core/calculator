<?php

declare(strict_types=1);

namespace Mathematicator\Step;


use Mathematicator\Engine\TerminateException;
use Mathematicator\Step\Controller\IStepController;
use Nette\Utils\ArrayHash;
use Psr\Container\ContainerInterface;

final class StepEndpoint
{

	/** @var StepFactory */
	private $stepFactory;

	/** @var ContainerInterface */
	private $container;

	/** @var IStepController */
	private $callback;


	/**
	 * @param StepFactory $stepFactory
	 * @param ContainerInterface $container
	 */
	public function __construct(StepFactory $stepFactory, ContainerInterface $container)
	{
		$this->stepFactory = $stepFactory;
		$this->container = $container;
	}


	/**
	 * @param string $type
	 * @param string $data
	 * @return mixed[]
	 */
	public function getStep(string $type, string $data): array
	{
		$this->callback = $this->container->get($type);

		try {
			$data = json_decode($data);
			$arrayHash = new ArrayHash();
			foreach ($data as $k => $v) {
				$arrayHash->{$k} = $v;
			}
			$steps = $this->callback->actionDefault($arrayHash);
		} catch (TerminateException $e) {
			$step = $this->stepFactory->create();
			$step->setTitle('Nepodařilo se najít postup pro [' . $type . ']');
			$steps[] = $step;
		}

		$return = [];

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
