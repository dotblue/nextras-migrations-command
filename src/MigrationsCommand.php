<?php

/**
 * Copyright (c) dotBlue (http://dotblue.net)
 */

namespace DotBlue\Migrations;

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


    public function __construct(ConsoleController $controller)
    {
        $this->controller = $controller;
        parent::__construct();
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
        $this->controller->run();
    }

}
