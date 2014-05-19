<?php

/**
 * Copyright (c) dotBlue (http://dotblue.net)
 */

namespace DotBlue\Migrations;

use Nette\DI;


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
	}

}
