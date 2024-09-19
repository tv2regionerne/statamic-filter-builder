<?php

namespace Tests;

use Statamic\Testing\AddonTestCase;
use Tv2regionerne\StatamicFilterBuilder\ServiceProvider;

class TestCase extends AddonTestCase
{
    protected string $addonServiceProvider = ServiceProvider::class;
}
