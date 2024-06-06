<?php

namespace RTL433DumpDec\Services;

use Dotenv\Dotenv;

class EnvService
{
    public function load() : void
    {
        $dotenv = Dotenv::createImmutable(__DIR__ . "/../../");
        $dotenv->load();
    }

    public function get( string $key ) : string|array|false
    {
        return $_ENV[ $key ];
    }
}