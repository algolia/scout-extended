<?php

declare(strict_types=1);

namespace Tests\Unit;

use Tests\TestCase;
use Algolia\LaravelScoutExtended\Facades\Algolia;

final class FacadeTest extends TestCase
{
    public function testFacadeResolvedService(): void
    {
        $this->assertInstanceOf(\Algolia\LaravelScoutExtended\Algolia::class, Algolia::getFacadeRoot());
    }
}
