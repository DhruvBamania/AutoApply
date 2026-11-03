<?php

namespace Tests\Feature;

use Tests\TestCase;

class ApkDownloadTest extends TestCase
{
    public function test_app_download_page_renders_successfully()
    {
        $response = $this->get('/app-download');
        $response->assertStatus(200);
        $response->assertSee('Download Android APK');
        $response->assertSee('app-release.apk');
    }

    public function test_download_apk_redirects_to_external_or_github_release()
    {
        $response = $this->get('/download-apk');
        // Should redirect (302) to the configured or fallback GitHub release APK URL
        $response->assertStatus(302);
        $this->assertStringContainsString('app-release.apk', $response->headers->get('Location'));
    }

    public function test_download_apk_auto_fixes_mismatched_filename()
    {
        $_ENV['APK_DOWNLOAD_URL'] = 'https://github.com/DhruvBamania/AutoApply/releases/download/apk/AI-Looping.apk';
        $_SERVER['APK_DOWNLOAD_URL'] = 'https://github.com/DhruvBamania/AutoApply/releases/download/apk/AI-Looping.apk';

        $response = $this->get('/download-apk');
        $response->assertStatus(302);
        // Ensure AI-Looping.apk was replaced with app-release.apk
        $this->assertEquals(
            'https://github.com/DhruvBamania/AutoApply/releases/download/apk/app-release.apk',
            $response->headers->get('Location')
        );

        // Reset
        unset($_ENV['APK_DOWNLOAD_URL']);
        unset($_SERVER['APK_DOWNLOAD_URL']);
    }
}
