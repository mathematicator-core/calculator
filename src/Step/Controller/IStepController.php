<?php

declare(strict_types=1);

namespace Mathematicator\Calculator\Step\Controller;


use Mathematicator\Engine\Step\Step;
use Nette\Utils\ArrayHash;

interface IStepController
{
	/**
	 * @param ArrayHash $data
	 * @return Step[]
	 */
	public function actionDefault(ArrayHash $data): array;
}
