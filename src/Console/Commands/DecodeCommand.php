<?php

namespace RTL433DumpDec\Console\Commands;

use RTL433DumpDec\Application;
use RTL433DumpDec\Classes\RTL433Block;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class DecodeCommand extends Command {
    /**
     * Our command name.
     */
    protected static $name = "decode";

    /**
     * Our command description.
     */
    protected static $description = "Main decode command.";

    /**
     * The IO interface.
     */
    protected ?SymfonyStyle $io = null;

    /**
     * Configure our command.
     */
    protected function configure() {
        $this
            ->setDescription( static::$description )
            ->setName( static::$name );
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
        if ( ( ! ( $data = @file_get_contents( "./data/data.txt" ) ) ) || ( ! strlen( $data ) ) ) {
            $this->io->error( "data.txt is missing or empty." );
            return static::FAILURE;
        }

        $this->io->info( "Data length: " . strlen( $data ) );

        $blocks = $this->splitBlocks( $data );

        if ( ! count( $blocks ) ) {
            $this->io->error( "No blocks found" );
            return static::FAILURE;
        }

        $this->io->info( "Block count: " . count( $blocks ) );

        $validatedBlocks = array_filter( $blocks, [ $this, "isBlocKValid" ] );

        if ( ! count( $blocks ) ) {
            $this->io->error( "No valid blocks found" );
            return static::FAILURE;
        }

        $this->io->info( "Valid block count: " . count( $validatedBlocks ) );

        $cleanedBlocks = array_map( [ $this, "cleanBlockStart" ], $validatedBlocks );
        $cleanedBlocks = array_values( array_map( [ $this, "cleanBlockEnd" ], $cleanedBlocks ) );

        try {
            $blockObjects = $this->getBlockObjects( $cleanedBlocks );
        } catch ( \Exception $error ) {
            $this->io->error( "Caught exception while decoding: " . $error->getMessage() );
            return static::FAILURE;
        }

        print_r( $blockObjects[ 0 ]->getLines() );

        return static::SUCCESS;
    }

    /**
     * Splits the blocks by a common output sequence.
     */
    private function splitBlocks( string $rawData ) : array {
        return explode( "*** signal_start", $rawData );
    }

    /**
     * Determines if a block has what we're looking for.
     */
    private function isBlockValid( string $block ) : bool {
        return !! strpos( $block, "[00]" );
    }

    /**
     * Cleans a blocks start data.
     */
    private function cleanBlockStart( string $block ) : string {
        return trim( substr( $block, strpos( $block, "[00]" ) ) );
    }

    /**
     * Cleans a blocks end data.
     */
    private function cleanBlockEnd( string $block ) : string {
        return trim( substr( $block, 0, strpos( $block, "..." ) ) );
    }

    /**
     * Convert our array of strings into the block objects, and run the "decode" to extract the components.
     * @return RTL433Block[]
     */
    private function getBlockObjects( array $cleanBlockData ) : array {
        $out = [];
        foreach ( $cleanBlockData as $_ => $block ) {
            $out[] = ( new RTL433Block( $block ) )->decode();
        }

        return $out;
    }
}