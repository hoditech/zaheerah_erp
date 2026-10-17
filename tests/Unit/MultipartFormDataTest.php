<?php

use App\Support\MultipartFormData;
use Illuminate\Http\UploadedFile;

/**
 * PHP never parses a multipart PUT/PATCH body, so the ERP parses it itself to
 * keep the already-deployed product form saving until the browser bundle is
 * rebuilt.
 */
function multipartBody(string $boundary, array $fields, array $files = []): string
{
    $body = '';

    foreach ($fields as $name => $value) {
        $body .= "--{$boundary}\r\n";
        $body .= "Content-Disposition: form-data; name=\"{$name}\"\r\n\r\n";
        $body .= $value . "\r\n";
    }

    foreach ($files as $name => [$filename, $mime, $content]) {
        $body .= "--{$boundary}\r\n";
        $body .= "Content-Disposition: form-data; name=\"{$name}\"; filename=\"{$filename}\"\r\n";
        $body .= "Content-Type: {$mime}\r\n\r\n";
        $body .= $content . "\r\n";
    }

    return $body . "--{$boundary}--\r\n";
}

test('it parses plain multipart fields', function () {
    $boundary = '----test-boundary';
    $body = multipartBody($boundary, [
        'name' => 'Hibiscus - Zobo (gari)',
        'price' => '18000',
        'unit_cost' => '',
    ]);

    $parsed = MultipartFormData::parse("multipart/form-data; boundary={$boundary}", $body);

    expect($parsed['fields'])->toBe([
        'name' => 'Hibiscus - Zobo (gari)',
        'price' => '18000',
        'unit_cost' => '',
    ])->and($parsed['files'])->toBe([]);
});

test('it parses an uploaded file from a multipart put body', function () {
    $boundary = '----test-boundary';
    $body = multipartBody(
        $boundary,
        ['name' => 'Rice'],
        ['image' => ['rice.jpg', 'image/jpeg', 'JPEG-CONTENT']]
    );

    $parsed = MultipartFormData::parse("multipart/form-data; boundary={$boundary}", $body);

    expect($parsed['fields'])->toBe(['name' => 'Rice'])
        ->and($parsed['files'])->toHaveKey('image');

    $file = $parsed['files']['image'];

    expect($file)->toBeInstanceOf(UploadedFile::class)
        ->and($file->getClientOriginalName())->toBe('rice.jpg')
        ->and($file->getClientMimeType())->toBe('image/jpeg')
        ->and(file_get_contents($file->getRealPath()))->toBe('JPEG-CONTENT');
});

test('it ignores a body that is not multipart or has no boundary', function () {
    expect(MultipartFormData::parse('application/json', '{"name":"Rice"}'))
        ->toBe(['fields' => [], 'files' => []])
        ->and(MultipartFormData::parse('multipart/form-data', ''))
        ->toBe(['fields' => [], 'files' => []]);
});
