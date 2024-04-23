<?php 

namespace RTL433DumpDec;

use RTL433DumpDec\Console\Commands\DecodeCommand;
use Symfony\Component\Console\Application as SymfonyApplication;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Application extends SymfonyApplication {
    /**
     * Configure our application.
     */
    public function configure() : void {
        $this->setName( "RTL433 Raw Decode | Experiment" );
    }

    /**
     * Register the commands of the application.
     */
    public function registerCommands() : void {
        $this->add( new DecodeCommand );
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