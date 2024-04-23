<?php

namespace RTL433DumpDec\Classes;

class RTL433Line {
    /**
     * The RegEx used to extract the components from our line.
     */
    protected static string $decodeRegex = '/\[(\d+)\] \{ *(\d+) *\} ([0-9a-f ]+) : ([01 ]+)/i';

    /**
     * The line index relative to the full block, from zero.
     */
    protected ?int $lineIndex = null;

    /**
     * The number of bits in the line.
     */
    protected ?int $bitLength = null;

    /**
     * The raw hexadecimal data.
     */
    protected ?string $rawHexGroups = null;

    /**
     * The raw binary data.
     */
    protected ?string $rawBinGroups = null;

    /**
     * Constructor.
     */
    public function __construct( protected string $rawLineData ) {}

    /**
     * Decode the inputted data.
     */
    public function decode() : static {
        // Perform the pattern match
        if ( preg_match( static::$decodeRegex, $this->rawLineData, $matches ) ) {
            $this->lineIndex = $matches[ 1 ]; // Number inside square brackets
            $this->bitLength = $matches[ 2 ]; // Number inside curly braces
            $this->rawHexGroups = trim( $matches[ 3 ] ); // Hexadecimal groups, trimmed to remove trailing space
            $this->rawBinGroups = trim( $matches[ 4 ] ); // Binary groups, trimmed to remove trailing space
        } else {
            throw new \Exception( "Failed to decode line: \"" . $this->rawLineData . "\"" );
        }

        return $this;
    }
}