<?php

namespace Tests\Feature;

use Tests\TestCase;

class IdentityRedirectTest extends TestCase
{
    public function test_start_and_signup_redirect_to_the_identity_trial_page(): void
    {
        $identity = config('services.comordo.identity_url');

        $this->get('/start')->assertRedirect($identity.'/start?product=imagesafe');
        $this->get('/signup')->assertRedirect($identity.'/start?product=imagesafe');
    }

    public function test_login_and_buy_tell_identity_the_visitor_came_from_imagesafe(): void
    {
        $identity = config('services.comordo.identity_url');

        $this->get('/login')->assertRedirect($identity.'/login?product=imagesafe');
        $this->get('/buy')->assertRedirect($identity.'/register?product=imagesafe');
    }
}
