<?php

declare(strict_types=1);

namespace Mathematicator\Calculator\Step;


use Baraja\Url\Url;
use Mathematicator\Engine\Step\Step;

final class StepFactory
{
	public static function addStep(?string $title = null, ?string $latex = null, ?string $description = null): Step
	{
		return new Step($title, $latex, $description);
	}


	/**
	 * Generate URL to API endpoint in format `/api/v1/mathematicator-engine/search-step`.
	 * In new version all steps will be sent as array of json objects.
	 *
	 * @param string $controllerClass implements IStepController
	 * @param mixed[] $data
	 */
	public static function getAjaxEndpoint(string $controllerClass, array $data): string
	{
		return Url::get()->getBaseUrl() . '/api/v1/mathematicator-engine/search-step?controller=' . urlencode((string) $controllerClass) . '&data=' . urlencode((string) \json_encode($data));
	}
}
