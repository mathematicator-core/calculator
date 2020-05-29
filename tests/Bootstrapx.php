<?php

declare(strict_types=1);

namespace Mathematicator\Calculator\Tests;

require __DIR__ . '/../vendor/autoload.php';

use Nette\Configurator;
use Nette\DI\Container;
use Tester\Environment;

Environment::setup();

class Bootstrapx
{
	public static function boot(): Container
	{
		$configurator = new Configurator();

		$configurator->setTimeZone('Europe/Prague');
		$configurator->setTempDirectory(__DIR__ . '/../temp');

		$configurator->createRobotLoader()
			->addDirectory(__DIR__ . '/../src')
			->register();

		$configurator
			->addConfig(__DIR__ . '/../common.neon')
			->addConfig(__DIR__ . '/config.neon');

		return $configurator->createContainer();
	}
}