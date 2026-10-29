<?php

namespace Tests\Feature;

use Tests\TestCase;

class IdentityRedirectTest extends TestCase
{
    public function test_start_and_signup_redirect_to_the_identity_trial_page(): void
    {
        $identity = config('services.comordo.identity_url');

        $this->get('/start')->assertRedirect($identity.'/start');
        $this->get('/signup')->assertRedirect($identity.'/start');
    }
}
