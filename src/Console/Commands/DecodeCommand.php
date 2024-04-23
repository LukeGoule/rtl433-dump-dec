<?php

namespace RTL433DumpDec\Console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class DecodeCommand extends Command {
    protected static $defaultName = 'decode';

    protected function configure()
    {
        $this
            ->setDescription('Greet someone')
            ->addArgument('name', InputArgument::OPTIONAL, 'Who do you want to greet?', 'World')
            ->setName( static::$defaultName );
    }

    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        $io = new SymfonyStyle($input, $output);
        $name = $input->getArgument('name');
        $io->success("Hello, {$name}!");

        return Command::SUCCESS;
    }
}