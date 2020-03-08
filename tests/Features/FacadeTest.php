<?php

declare(strict_types=1);

namespace Tests\Features;

use Algolia\ScoutExtended\Algolia;
use Algolia\ScoutExtended\Facades\Algolia as AlgoliaFacade;
use Tests\TestCase;

final class FacadeTest extends TestCase
{
    public function testFacadeResolvedService(): void
    {
        $this->assertInstanceOf(Algolia::class, AlgoliaFacade::getFacadeRoot());
    }
}
