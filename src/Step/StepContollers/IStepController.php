<?php

declare(strict_types=1);

namespace Mathematicator\Step\Controller;


use Mathematicator\Calculator\Step;
use Nette\Utils\ArrayHash;

interface IStepController
{
	/**
	 * @param ArrayHash $data
	 * @return Step[]
	 */
	public function actionDefault(ArrayHash $data): array;
}
