<?php

namespace RTL433DumpDec\Classes;

class RTL433Block {
    protected ?array $lines = null;


    /**
     * Constructor.
     */
    public function __construct( protected string $rawData ) {}

    /**
     * Decode our data.
     */
    public function parse() : static {
        $this->rawData = trim( $this->rawData );
        
        $this->lines = [];
        foreach ( explode( "\n", $this->rawData ) as $rawLine ) {
            if ( ! strlen( trim( $rawLine ) ) ) {
                continue;
            }

            $this->lines[] = ( new RTL433Line( $rawLine ) )->parse();
        } 

        return $this;
    }

    /**
     * Get the lines.
     * @return RTL433Line[]
     */
    public function getLines() : array {
        return $this->lines;
    }

    /**
     * Get the 2D array of data.
     * @return array<array<int>>
     */
    public function getData() : array {
        return array_map( function ( RTL433Line $rTL433Line ) {
            return $rTL433Line->getData();
        }, $this->getLines() );
    }
}