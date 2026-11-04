<?php

namespace Tests\Feature;

use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class PricingPageTest extends TestCase
{
    public function test_the_pricing_page_renders(): void
    {
        $this->get('/pricing')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('PricingPage', false));
    }
}
