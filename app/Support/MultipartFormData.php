<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use RuntimeException;

/**
 * Parses a multipart/form-data request body.
 *
 * PHP only populates $_POST / $_FILES for POST requests. A PUT or PATCH sent
 * as multipart/form-data therefore reaches Laravel with an empty request bag
 * and no files, so every field looks missing to validation. This parser reads
 * the raw body so such requests behave like their POST counterparts.
 *
 * It is deliberately small: flat field names (no "name[0]" nesting) are all the
 * admin forms use.
 */
class MultipartFormData
{
    /**
     * @return array{fields: array<string, string>, files: array<string, UploadedFile>}
     */
    public static function parse(string $contentType, string $body): array
    {
        $fields = [];
        $files = [];

        if ($body === '' || ! preg_match('/boundary=(?:"([^"]+)"|([^;]+))/i', $contentType, $match)) {
            return ['fields' => $fields, 'files' => $files];
        }

        $boundary = trim($match[1] !== '' ? $match[1] : $match[2]);

        if ($boundary === '') {
            return ['fields' => $fields, 'files' => $files];
        }

        // Parts are serialised as "--<boundary>" followed by headers, a blank
        // line, the content, and a CRLF. The first chunk is the preamble and a
        // chunk beginning with "--" is the closing boundary.
        foreach (array_slice(explode('--' . $boundary, $body), 1) as $part) {
            if (str_starts_with($part, '--')) {
                break;
            }

            $part = ltrim($part, "\r\n");

            if ($part === '') {
                continue;
            }

            $separator = strpos($part, "\r\n\r\n");

            if ($separator === false) {
                continue;
            }

            $headers = self::parseHeaders(substr($part, 0, $separator));
            $content = substr($part, $separator + 4);
            $disposition = $headers['content-disposition'] ?? '';

            // Strip the CRLF that belongs to the boundary delimiter.
            if (str_ends_with($content, "\r\n")) {
                $content = substr($content, 0, -2);
            }

            if (! preg_match('/name="([^"]*)"/i', $disposition, $nameMatch)) {
                continue;
            }

            $name = $nameMatch[1];

            if (preg_match('/filename="([^"]*)"/i', $disposition, $filenameMatch)) {
                $files[$name] = self::uploadedFile(
                    $content,
                    $filenameMatch[1],
                    $headers['content-type'] ?? null
                );

                continue;
            }

            $fields[$name] = $content;
        }

        return ['fields' => $fields, 'files' => $files];
    }

    /**
     * @return array<string, string>
     */
    private static function parseHeaders(string $rawHeaders): array
    {
        $headers = [];

        foreach (preg_split('/\r\n/', $rawHeaders) ?: [] as $line) {
            $position = strpos($line, ':');

            if ($position === false) {
                continue;
            }

            $headers[strtolower(trim(substr($line, 0, $position)))] = trim(substr($line, $position + 1));
        }

        return $headers;
    }

    private static function uploadedFile(string $content, string $originalName, ?string $mimeType): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'dsh-upload-');

        if ($path === false) {
            throw new RuntimeException('Could not create a temporary file for an uploaded file.');
        }

        file_put_contents($path, $content);

        return new UploadedFile(
            $path,
            $originalName !== '' ? $originalName : 'upload',
            $mimeType ?: null,
            UPLOAD_ERR_OK,
            true // Treat as a test upload so is_uploaded_file() checks pass.
        );
    }
}
