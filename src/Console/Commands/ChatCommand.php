<?php

namespace RTL433DumpDec\Console\Commands;

use RTL433DumpDec\Services\OpenAIService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ChatCommand extends Command
{
    /**
     * Our command name.
     */
    protected static $name = "chat";

    /**
     * Our command description.
     */
    protected static $description = "Test OpenAI";

    /**
     * The IO interface.
     */
    protected ?SymfonyStyle $io = null;

    /**
     * @var OpenAIService|null OpenAI Service.
     */
    protected ?OpenAIService $openAIService = null;

    /**
     * Configure our command.
     */
    protected function configure() {
        $this
            ->setDescription( static::$description )
            ->setName( static::$name );

        $this->openAIService = new OpenAIService();
    }

    /**
     * Run the command.
     */
    protected function execute( InputInterface $input, OutputInterface $output ) : int {
        $this->io = new SymfonyStyle( $input, $output );
        return $this->handle();
    }

    /**
     * Handle the command.
     */
    protected function handle() : int {
        $this->openAIService->test();

        return static::SUCCESS;
    }
}