<?php

namespace Tests;

use Statamic\Testing\AddonTestCase;
use Statamic\Testing\Concerns\PreventsSavingStacheItemsToDisk;
use Tv2regionerne\StatamicFilterBuilder\ServiceProvider;

class TestCase extends AddonTestCase
{
    use PreventsSavingStacheItemsToDisk;

    protected $fakeStacheDirectory = __DIR__.'/__fixtures__/dev-null';

    protected string $addonServiceProvider = ServiceProvider::class;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();

        if (! file_exists($this->fakeStacheDirectory)) {
            mkdir($this->fakeStacheDirectory, 0777, true);
        }
    }
}
