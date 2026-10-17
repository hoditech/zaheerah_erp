<?php

namespace App\Http\Middleware;

use App\Support\MultipartFormData;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Restores the fields of a multipart PUT/PATCH request.
 *
 * The admin product form used to upload its image as multipart/form-data on a
 * real PUT. PHP does not parse that body, so validation saw no name, price or
 * category and every save failed. Until every browser is on the rebuilt form,
 * this middleware makes the old request work by parsing the raw body itself.
 *
 * Runs as global middleware, before TrimStrings / ConvertEmptyStringsToNull, so
 * the parsed fields are cleaned exactly like a normal POST.
 */
class ParseMultipartFormData
{
    public function handle(Request $request, Closure $next): Response
    {
        $contentType = strtolower((string) $request->headers->get('Content-Type', ''));

        $shouldParse = in_array($request->getMethod(), ['PUT', 'PATCH'], true)
            && str_starts_with($contentType, 'multipart/form-data')
            && $request->request->count() === 0;

        if ($shouldParse) {
            $parsed = MultipartFormData::parse(
                (string) $request->headers->get('Content-Type', ''),
                $request->getContent()
            );

            foreach ($parsed['fields'] as $key => $value) {
                $request->request->set($key, $value);
            }

            foreach ($parsed['files'] as $key => $file) {
                $request->files->set($key, $file);
            }
        }

        return $next($request);
    }
}
