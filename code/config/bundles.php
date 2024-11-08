<?php

declare(strict_types=1);

use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\MonologBundle\MonologBundle;
use Symfony\Bundle\TwigBundle\TwigBundle;
use Symfony\Bundle\WebProfilerBundle\WebProfilerBundle;

return [
    FrameworkBundle::class   => ['all' => true],
    TwigBundle::class        => ['all' => true],
    WebProfilerBundle::class => ['dev' => true, 'test' => true],
    MonologBundle::class     => ['all' => true],
];
