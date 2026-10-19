<?php

namespace Tests\Feature;

use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class PreviewPageTest extends TestCase
{
    public function test_a_preview_id_renders_the_preview_page(): void
    {
        $id = '9d3c1f2a-6b4e-4c1d-8a7f-2e5b6c7d8e9f';

        $this->get("/preview/{$id}")
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('PreviewPage', false)
                ->where('id', $id));
    }

    public function test_an_id_that_is_not_a_uuid_is_not_found(): void
    {
        $this->get('/preview/not-a-uuid')->assertNotFound();
    }
}
