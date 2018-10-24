<?php

declare(strict_types=1);

namespace Tests\Features;

use Tests\TestCase;

final class ScoutBladeComponentTest extends TestCase
{
    public function testViewContent(): void
    {
        $client = $this->mockClient();

        $client->shouldReceive('listApiKeys')->andReturn(['keys' => []]);

        ($addApiKeyResponse = $this->mockResponse())->shouldReceive('getBody')->andReturn();
        $client->shouldReceive('addApiKey')->andReturn($addApiKeyResponse);

        $client->shouldReceive('generateSecuredApiKey')->andReturn('fakeSecuredApiKey');

        $content = view()->file(resource_path('views/scout.blade.php'))->render();
        $this->assertEquals($content, '<ais-index index-name="threads"
           app-id=""
           api-key="fakeSecuredApiKey">
    foo
</ais-index>
');
    }
}
