<?php

declare(strict_types=1);

namespace App\Tests;

use Saloon\Config;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AppTestCase extends WebTestCase
{
    protected KernelBrowser $client;

    protected function setUp(): void
    {
        parent::setUp();

        Config::preventStrayRequests();

        $this->client = self::createClient();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->client);
    }

    /**
     * @template T of object
     *
     * @param class-string<T> $className
     *
     * @return T
     */
    protected static function getObjectFromContainer(string $className)
    {
        return self::getContainer()->get($className);
    }

    protected static function getStub(string $fileName): string
    {
        $fullStubPath = __DIR__ . '/stub/' . $fileName;
        $content      = file_get_contents($fullStubPath);

        if ($content === false) {
            self::fail('Can not load stub: ' . $fullStubPath);
        }

        return $content;
    }
}
