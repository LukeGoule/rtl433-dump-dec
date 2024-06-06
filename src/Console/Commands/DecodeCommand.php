<?php

namespace RTL433DumpDec\Console\Commands;

use RTL433DumpDec\Application;
use RTL433DumpDec\Classes\RTL433Block;
use RTL433DumpDec\Services\EnvService;
use RTL433DumpDec\Services\OpenAIService;
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

        $this->io->comment( "Data length: " . strlen( $data ) );

        $blocks = $this->splitBlocks( $data );

        if ( ! count( $blocks ) ) {
            $this->io->error( "No blocks found" );
            return static::FAILURE;
        }

        $this->io->comment( "Block count: " . count( $blocks ) );

        $validatedBlocks = array_filter( $blocks, [ $this, "isBlocKValid" ] );

        if ( ! count( $blocks ) ) {
            $this->io->error( "No valid blocks found" );
            return static::FAILURE;
        }

        $this->io->comment( "Valid block count: " . count( $validatedBlocks ) );

        $cleanedBlocks = array_map( [ $this, "cleanBlockStart" ], $validatedBlocks );
        $cleanedBlocks = array_values( array_map( [ $this, "cleanBlockEnd" ], $cleanedBlocks ) );

        try {
            $blockObjects = $this->getBlockObjects( $cleanedBlocks );
        } catch ( \Exception $error ) {
            $this->io->error( "Caught exception while decoding: " . $error->getMessage() );
            return static::FAILURE;
        }

        $allData = [];
        foreach ( $blockObjects as $block ) {
            $allData[] = $block->getData();
        }

        $this->io->comment( "Writing latest CSV dump." );
        $this->writeToCSV( $blockObjects );

        if ( ! ( new EnvService )->get( "OPENAI_API_KEY" ) ){
            $this->io->error( "OPENAI_API_KEY is missing." );
            return static::FAILURE;
        }

        try {
            $this->io->comment( "Attempting decode with OpenAI." );
            $message = (new OpenAIService())->analyseData($allData);

            file_put_contents( $fname = './data/' . time() . '.md', $message );

            $this->io->comment( "Wrote: " . $fname );
        } catch ( \Exception $error ) {
            $this->io->error( "Caught exception while decoding: " . $error->getMessage() );
        }

        return static::SUCCESS;
    }

    /**
     * @param RTL433Block[] $blocks
     * @return void
     */
    private function writeToCSV( array $blocks ) {
        // Open a file in write mode ('w')
        $file = fopen($fname = './data/' . time() . '.csv', 'w');

        // Iterate over the data and write each row to the CSV
        foreach ( $blocks as $id => $block ) {
            $lines = $block->getData();
            $block = [ "PULSE " . $id ];
            foreach ( $lines as $line ) {
                foreach ( $line as $data ) {
                    $block[] = sprintf( "0x%03X", $data );
                }
            }
            fputcsv($file, $block);
        }

        // Close the file
        fclose($file);

        $this->io->comment( "Wrote: $fname" );
    }

    /**
     * Display a formatted 2D array from our dataset.
     * @param array<array<int>> $twoDeeArray
     */
    private function display2DNumbers( array $twoDeeArray ) {
        $out = "";
        foreach ( $twoDeeArray as $row ) {
            $formattedRow = "";
            foreach ( $row as $number ) {
                $formattedRow .= sprintf( "0x%03X, ", $number );
            }
            $out .= $formattedRow;
        }
        $out = trim( $out );

        $this->io->writeln( $out );
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
        return trim( str_replace( "... Maximum number of rows reached. Message is likely truncated.", "", $block ) );
    }

    /**
     * Convert our array of strings into the block objects, and run the parser to extract the components.
     * @return RTL433Block[]
     */
    private function getBlockObjects( array $cleanBlockData ) : array {
        $out = [];
        foreach ( $cleanBlockData as $block ) {
            $out[] = ( new RTL433Block( $block ) )->parse();
        }

        return $out;
    }
}