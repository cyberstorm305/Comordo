<?php

namespace Tests\Feature;

use Tests\TestCase;

class StaticModeSourceTest extends TestCase
{
    private function js(string $path): string
    {
        return file_get_contents(resource_path('js/'.$path));
    }

    public function test_static_mode_is_off_unless_the_build_flag_is_set(): void
    {
        $this->assertMatchesRegularExpression("/isStaticExport\s*=\s*import\.meta\.env\.VITE_STATIC_EXPORT\s*===\s*'1'/", $this->js('site.js'));
        $this->assertMatchesRegularExpression('/isStaticExport\s*\?\s*StaticLink\s*:\s*Link/', $this->js('app.js'));
    }

    public function test_contact_page_posts_to_contact_php_in_static_mode(): void
    {
        $contact = $this->js('Pages/ContactPage.vue');
        $this->assertStringContainsString("'/contact.php'", $contact);
        $this->assertStringContainsString('isStaticExport', $contact);
    }

    public function test_newsletter_only_in_the_comordo_footer(): void
    {
        $footer = $this->js('Components/SiteFooter.vue');
        $this->assertMatchesRegularExpression('/v-if="isComordo"[^>]*>(?:(?!v-else).)*<NewsletterForm/s', $footer);
        $this->assertSame(1, substr_count($footer, '<NewsletterForm'));

        $form = $this->js('Components/NewsletterForm.vue');
        $this->assertStringContainsString("'/subscribe.php'", $form);
        $this->assertStringContainsString('I agree that Comordo may store my details and contact me about its products and news, in line with the', $form);
        $this->assertStringContainsString('href="/privacy"', $form);
    }
}
