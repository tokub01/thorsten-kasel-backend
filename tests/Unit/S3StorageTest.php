<?php

namespace Tests\Unit;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class S3StorageTest extends TestCase
{
    #[Test]
    public function it_uploads_a_file_to_s3_and_returns_the_url()
    {
        // S3 Fake initialisieren
        Storage::fake('s3');

        // Dummy-Datei generieren
        $file = UploadedFile::fake()->image('testbild.jpg');

        // Datei auf den S3-Disk laden
        $path = $file->store('products', 's3');

        // URL generieren
        $url = Storage::disk('s3')->url($path);

        // Assertions
        Storage::disk('s3')->assertExists($path);
        $this->assertStringContainsString('products/', $path);
        $this->assertStringContainsString($file->hashName(), $path);
        $this->assertNotEmpty($url);
    }

    #[Test]
    public function it_deletes_a_file_from_s3()
    {
        Storage::fake('s3');

        $file = UploadedFile::fake()->image('delete-me.jpg');

        $path = $file->store('products', 's3');

        Storage::disk('s3')->assertExists($path);

        Storage::disk('s3')->delete($path);

        Storage::disk('s3')->assertMissing($path);
    }

    #[Test]
    public function it_checks_if_a_file_exists_on_s3()
    {
        Storage::fake('s3');

        $file = UploadedFile::fake()->image('exists-check.jpg');

        $path = $file->store('products', 's3');

        $this->assertTrue(Storage::disk('s3')->exists($path));
        Storage::disk('s3')->delete($path);
        $this->assertFalse(Storage::disk('s3')->exists($path));
    }
}
