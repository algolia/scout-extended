<?php

declare(strict_types=1);

namespace Tests\Features;

use Tests\TestCase;
use Algolia\ScoutExtended\Facades\Algolia;

final class FacadeTest extends TestCase
{
    public function testFacadeResolvedService(): void
    {
        $this->assertInstanceOf(\Algolia\ScoutExtended\Algolia::class, Algolia::getFacadeRoot());
    }
}
