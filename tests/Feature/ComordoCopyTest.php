<?php

namespace Tests\Feature;

use Tests\TestCase;

class ComordoCopyTest extends TestCase
{
    /** Files not yet cleaned of Compli; Task 4 removes this list and its use. */
    private const PENDING_TASK_4 = [
        'Pages/HomePage.vue',
        'Pages/ImageSafeOverviewPage.vue',
    ];

    private const FILES = [
        'Pages/HomePage.vue',
        'Pages/ImageSafeOverviewPage.vue',
        'Pages/ContactPage.vue',
        'Pages/PrivacyPage.vue',
        'Pages/TermsPage.vue',
        'Components/SiteNav.vue',
        'Components/SiteFooter.vue',
        'Components/PageHeader.vue',
    ];

    public function test_no_compli_in_comordo_page_sources(): void
    {
        foreach (self::FILES as $file) {
            if (in_array($file, self::PENDING_TASK_4, true)) {
                continue;
            }

            $path = resource_path('js/'.$file);
            $this->assertFileExists($path, $file);

            $source = file_get_contents($path);
            $source = preg_replace('/<!--.*?-->/s', '', $source);
            $source = preg_replace('#/\*.*?\*/#s', '', $source);
            $source = preg_replace('#(?<!:)//[^\n]*#', '', $source);

            $this->assertSame(0, preg_match('/\bCompli\b/', $source), "{$file} mentions Compli");
        }
    }
}
