<?php

/**
 * Copyright (c) dotBlue (http://dotblue.net)
 */

namespace DotBlue\Migrations;

use Exception;
use Kdyby\Events\EventManager;
use Nextras\Migrations\Controllers\ConsoleController;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


class MigrationsCommand extends Command
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
        $originalCliArgs = $_SERVER['argv'];

        $_SERVER['argv'] = [
            'migrations', // this item isn't used by ConsoleController
        ];

        if ($input->getOption('init-sql')) {
            $_SERVER['argv'][] = '--init-sql';
        }
        if ($input->getOption('reset')) {
            $_SERVER['argv'][] = '--reset';
        }

        foreach ($input->getArgument('names') as $name) {
            $_SERVER['argv'][] = $name;
        }

        // run controller
        try {
            $this->controller->run();
            $this->fireEvent('nextras.migrations.success');
            $this->fireEvent('nextras.migrations.complete');

            $this->getApplication()->find('migrate:unlock')
                ->run(new ArrayInput([
                    'command' => 'migrate:unlock',
                ]), $output);
        } catch (Exception $e) {
            $this->fireEvent('nextras.migrations.fail');
            $this->fireEvent('nextras.migrations.complete');
            throw $e;
        }

        $_SERVER['argv'] = $originalCliArgs;
    }


    private function fireEvent($name)
    {
        if ($this->eventManager) {
            $this->eventManager->dispatchEvent($name);
        }
    }

}
