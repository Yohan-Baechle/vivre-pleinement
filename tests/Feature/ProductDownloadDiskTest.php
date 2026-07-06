<?php

use App\Models\Product;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(LazilyRefreshDatabase::class);

it('stocke le fichier vendu sur le disque privé', function () {
    Storage::fake('local');

    $product = Product::factory()->create();

    $media = $product
        ->addMedia(UploadedFile::fake()->create('livre.pdf', 100, 'application/pdf'))
        ->toMediaCollection('download');

    expect($media->disk)->toBe('local');
});

it('garde la couverture sur le disque public', function () {
    Storage::fake('public');

    $product = Product::factory()->create();

    $media = $product
        ->addMedia(UploadedFile::fake()->image('couverture.jpg'))
        ->toMediaCollection('cover');

    expect($media->disk)->toBe('public');
});
