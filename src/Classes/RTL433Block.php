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
    public function decode() : static {
        $this->rawData = trim( $this->rawData );
        
        foreach ( explode( "\n", $this->rawData ) as $rawLine ) {
            if ( ! strlen( trim( $rawLine ) ) ) {
                continue;
            }
            
            $this->lines[] = ( new RTL433Line( $rawLine ) )->decode();
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
}