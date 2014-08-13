<?php

/**
 * Copyright (c) dotBlue (http://dotblue.net)
 */

namespace DotBlue\Migrations;

use Exception;
use Kdyby\Events\EventManager;
use Nette\Reflection\ClassType;
use Nextras\Migrations\Controllers\ConsoleController;
use Symfony\Component\Console;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


class MigrationsCommand extends Console\Command\Command
{

    /** @var ConsoleController */
    private $controller;

    /** @var EventManager|NULL */
    private $eventManager;


    public function __construct(ConsoleController $controller)
    {
        $this->controller = $controller;
        parent::__construct();
    }


    public function setEventManager(EventManager $eventManager)
    {
        $this->eventManager = $eventManager;
    }


    public function configure()
    {
        $this->setName('migrate');
        $this->setDescription('Runs database migrations');
        $this->addArgument('names', InputArgument::IS_ARRAY);
        $this->addOption('reset', NULL, InputOption::VALUE_NONE);
        $this->addOption('init-sql', NULL, InputOption::VALUE_NONE);
    }


    public function execute(InputInterface $input, OutputInterface $output)
    {
        array_shift($_SERVER['argv']);

        // run controller
        try {
            $processArguments = ClassType::from($this->controller)->getMethod('processArguments');
            $processArguments->setAccessible(TRUE);
            $processArguments->invoke($this->controller);
            $registerGroups = ClassType::from($this->controller)->getMethod('registerGroups');
            $registerGroups->setAccessible(TRUE);
            $registerGroups->invoke($this->controller);

            $runner = ClassType::from($this->controller)->getProperty('runner');
            $runner->setAccessible(TRUE);
            $runner = $runner->getValue($this->controller);
            $orderResolver = ClassType::from($runner)->getProperty('orderResolver');
            $orderResolver->setAccessible(TRUE);
            $orderResolver = $orderResolver->getValue($runner);

            $mode = ClassType::from($this->controller)->getProperty('mode');
            $mode->setAccessible(TRUE);
            $mode = $mode->getValue($this->controller);

            $driver = ClassType::from($runner)->getProperty('driver');
            $driver->setAccessible(TRUE);
            $driver = $driver->getValue($runner);
            $migrations = $driver->getAllMigrations();

            $groups = ClassType::from($runner)->getProperty('groups');
            $groups->setAccessible(TRUE);
            $groups = $groups->getValue($runner);

            $finder = ClassType::from($runner)->getProperty('finder');
            $finder->setAccessible(TRUE);
            $finder = $finder->getValue($runner);
            $extensionsHandlers = ClassType::from($runner)->getProperty('extensionsHandlers');
            $extensionsHandlers->setAccessible(TRUE);
            $extensionsHandlers = $extensionsHandlers->getValue($runner);
            $files = $finder->find($groups, array_keys($extensionsHandlers));

            $toExecute = $orderResolver->resolve($migrations, $groups, $files, $mode);

            if ($toExecute) {
                $this->fireEvent('nextras.migrations.start');
            }

            $this->controller->run();

            if ($toExecute) {
                $this->fireEvent('nextras.migrations.success');
                $this->fireEvent('nextras.migrations.complete');
            }
        } catch (Exception $e) {
            if (isset($toExecute) && $toExecute) {
                $this->fireEvent('nextras.migrations.fail');
                $this->fireEvent('nextras.migrations.complete');
            }
            throw $e;
        }
    }


    private function fireEvent($name)
    {
        if ($this->eventManager) {
            $this->eventManager->dispatchEvent($name);
        }
    }

}
