<?php

namespace App\Parser\Interfaces;

use App\Interfaces\ParseSiteInterface;
use App\Parser\ExternalPost;
use App\Parser\Nodes;
use Illuminate\Support\Collection;
use Symfony\Component\DomCrawler\Crawler;

interface ParseHtmlInterface extends ParseSiteInterface
{
    public function parsePage(): Collection;

    public function parseNodes(Crawler $node): Nodes;

    public function parsePost(Nodes $nodes): ExternalPost;
}
