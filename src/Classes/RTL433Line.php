<?php

namespace RTL433DumpDec\Classes;

class RTL433Line {
    /**
     * The RegEx used to extract the components from our line.
     */
    protected static string $decodeRegex = '/\[(\d+)\] \{ *(\d+) *\} ([0-9a-f ]+)(?: : ([01 ]+))?/i'; //'/\[(\d+)\] \{ *(\d+) *\} ([0-9a-f ]+) : ([01 ]+)/i';

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
     * The converted data, as integers.
     */
    protected ?array $data = [];

    /**
     * Constructor.
     */
    public function __construct( protected string $rawLineData ) {}

    /**
     * Decode the inputted data.
     */
    public function parse() : static {
        // Perform the pattern match
        if ( preg_match( static::$decodeRegex, $this->rawLineData, $matches ) ) {
            $this->lineIndex = $matches[ 1 ]; // Number inside square brackets
            $this->bitLength = $matches[ 2 ]; // Number inside curly braces
            $this->rawHexGroups = trim( $matches[ 3 ] ); // Hexadecimal groups, trimmed to remove trailing space
            $this->rawBinGroups = trim( @$matches[ 4 ] ?? "" ); // Binary groups, trimmed to remove trailing space. This may be empty.
        } else {
            throw new \Exception( "Failed to decode line: \"" . $this->rawLineData . "\"" );
        }

        $hexData = preg_replace('/\s+/', '', $this->rawHexGroups); // Remove spaces
        $hexBytes = str_split($hexData, 2); // Split into bytes
        $this->data = array_map('hexdec', $hexBytes); // Convert hex to integers

        return $this;
    }

    /**
     * Get the parsed data.
     * @return int[]
     */
    public function getData() : array {
        return $this->data;
    }
}