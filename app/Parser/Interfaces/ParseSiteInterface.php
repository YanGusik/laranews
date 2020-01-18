<?php

namespace App\Interfaces;

interface ParseSiteInterface
{
    public function parse(): void;

    public function dump(): array;

}
