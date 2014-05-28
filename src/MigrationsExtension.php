<?php

/**
 * Copyright (c) dotBlue (http://dotblue.net)
 */

namespace DotBlue\Migrations;

use Nette\DI;
use Nette\PhpGenerator as Code;


class MigrationsExtension extends DI\CompilerExtension
{

	/** @var array */
	private $defaults = [
		'table' => 'migrations',
		'groups' => [],
		'extensions' => [],
	];



	public function loadConfiguration()
	{
		$config = $this->getConfig($this->defaults);
		$container = $this->getContainerBuilder();

		$container->addDefinition($this->prefix('driver'))
			->setClass('Nextras\Migrations\Drivers\MySqlNetteDbDriver', [
				'tableName' => $config['table'],
			]);

		$controller = $container->addDefinition($this->prefix('controller'))
			->setClass('Nextras\Migrations\Controllers\ConsoleController');

		foreach ($config['extensions'] as $extension => $implementation) {
			$this->compiler->parseServices($container, [
				'services' => [
					$this->prefix('extension_' . $extension) => $implementation,
				],
			]);

			$controller->addSetup('addExtension', [
				$extension,
				$this->prefix('@extension_' . $extension),
			]);
		}

		$container->addDefinition($this->prefix('command'))
			->setClass('DotBlue\Migrations\MigrationsCommand')
			->addTag('kdyby.console.command');

		foreach ($config['groups'] as $name => $group) {
			$path = is_array($group) ? $group['path'] : $group;
			$controller->addSetup('addGroup', [
				$name,
				$path,
				isset($group['deps']) ? $group['deps'] : [],
			]);
		}

		$container->addDefinition($this->prefix('unlockCommand'))
			->setClass('DotBlue\Migrations\UnlockCommand')
			->addTag('kdyby.console.command');
	}



	public function afterCompile(Code\ClassType $class)
	{
		$container = $this->getContainerBuilder();

		if ($eventManager = $container->getByType('Kdyby\Events\EventManager')) {
			$methodName = 'createService' . ucfirst($this->name) . '__command';
			$method = $class->methods[$methodName];

			$body = explode(';', substr(trim($method->getBody()), 0, -1));
			$return = array_pop($body);
			$body[] = Code\Helpers::format(
				PHP_EOL . '$service->setEventManager($this->getService(?))',
				$eventManager
			);
			$body[] = $return;
			$method->setBody(implode(';', $body) . ';');
		}
	}

}
