<?php 

namespace RTL433DumpDec;

use RTL433DumpDec\Console\Commands\ChatCommand;
use RTL433DumpDec\Console\Commands\DecodeCommand;
use RTL433DumpDec\Services\EnvService;
use Symfony\Component\Console\Application as SymfonyApplication;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Application extends SymfonyApplication {
    public ?EnvService $envService = null;

    /**
     * Configure our application.
     */
    public function configure() : void {
        $this->envService = new EnvService();
        $this->envService->load();

        $this->setName( "RTL433 Raw Decode | Experiment" );
    }

    /**
     * Register the commands of the application.
     */
    public function registerCommands() : void {
        $this->add( new DecodeCommand );
        $this->add( new ChatCommand );
    }

    /**
     * Run the app.
     */
    public function run( ?InputInterface $input = null, ?OutputInterface $output = null ) : int {
        $this->configure();
        $this->registerCommands();

        return parent::run( $input, $output );
    }
}