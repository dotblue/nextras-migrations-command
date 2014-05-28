<?php

/**
 * Copyright (c) dotBlue (http://dotblue.net)
 */

namespace DotBlue\Migrations;

use Nette\Database;
use Symfony\Component\Console;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


class UnlockCommand extends Console\Command\Command
{

    /** @var Database\Context */
    private $database;


    public function __construct(Database\Context $database)
    {
        $this->database = $database;
        parent::__construct();
    }


    public function configure()
    {
        $this->setName('migrate:unlock');
        $this->setDescription('Unlocks database migrations');
    }


    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->database->query('DROP TABLE IF EXISTS `migrations_lock`');
        $output->writeln('<info>Migrations unlocked.</info>');
    }

}
