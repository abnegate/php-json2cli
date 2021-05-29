<?php


namespace Json2CLI\Test;

use Utopia\CLI\Console;

class Installer
{
    function execute(
        string $path,
        string $version,
        string $composeFile,
    )
    {
        Console::log($path);
        Console::log($version);
        Console::log($composeFile);
    }
}