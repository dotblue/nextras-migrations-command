<?php

/**
 * Copyright (c) dotBlue (http://dotblue.net)
 */

namespace DotBlue\Migrations;

use Nextras;
use Symfony\Component\Console;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


class UnlockCommand extends Console\Command\Command
{

    /** @var Nextras\Migrations\IDriver */
    private $driver;


    public function __construct(Nextras\Migrations\IDriver $driver)
    {
        $this->driver = $driver;
        parent::__construct();
    }


    public function configure()
    {
        $this->setName('migrate:unlock');
        $this->setDescription('Unlocks database migrations');
    }


    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->driver->unlock();
        $output->writeln('<info>Migrations unlocked.</info>');
    }

}
