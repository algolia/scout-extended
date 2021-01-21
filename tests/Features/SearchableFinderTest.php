<?php

declare(strict_types=1);

namespace Tests\Features;

use Tests\TestCase;

use function base_path;
use function file_get_contents;
use function file_put_contents;
use function unlink;

final class SearchableFinderTest extends TestCase
{
    public function testWhenThereIsAnUnresolvableClass(): void
    {
        // inject a file that cannot be resolved
        $filePath = base_path('app/UnresolvableClass.php');
        file_put_contents(
            $filePath,
            file_get_contents(base_path('app/UnresolvableClass.php.stub'))
        );

        $this->artisan('scout:sync')
            ->expectsOutput("{$filePath} could not be inspected due to an error being thrown while loading it.");
    }

    public function tearDown(): void
    {
        unlink(base_path('app/UnresolvableClass.php'));

        parent::tearDown();
    }
}
