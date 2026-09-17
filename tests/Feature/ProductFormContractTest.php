<?php

/**
 * The admin product forms and the controller must agree on field names.
 *
 * They did not. ProductForm.vue sent "stock" while the controller required
 * "initial_stock", and neither form sent the required "unit_cost", so creating
 * or editing a product from the admin always failed validation. These tests
 * compare the two sides directly so a rename on one side cannot silently break
 * the other again.
 */

/**
 * Fields the given controller method marks as required.
 *
 * @return array<int, string>
 */
function requiredProductFields(string $method): array
{
    $source = file_get_contents(app_path('Http/Controllers/productController.php'));

    $methodStart = strpos($source, "function {$method}(");

    expect($methodStart)->not->toBeFalse("productController::{$method} was not found.");

    $afterMethod = substr($source, $methodStart);

    // Only look inside the $request->validate([...]) call.
    $validateStart = strpos($afterMethod, 'validate([');

    expect($validateStart)->not->toBeFalse("No validate() call in productController::{$method}.");

    $validateBlock = substr($afterMethod, $validateStart);
    $validateEnd = strpos($validateBlock, ']);');

    expect($validateEnd)->not->toBeFalse("Could not read the validate() block in productController::{$method}.");

    preg_match_all(
        "/'(\w+)'\s*=>\s*'([^']*)'/",
        substr($validateBlock, 0, $validateEnd),
        $matches,
        PREG_SET_ORDER
    );

    $required = [];

    foreach ($matches as $match) {
        if (str_contains($match[2], 'required')) {
            $required[] = $match[1];
        }
    }

    expect($required)->not->toBeEmpty("No required rules found in productController::{$method}.");

    return $required;
}

/**
 * Field names declared in a Vue component's useForm({...}) call.
 *
 * @return array<int, string>
 */
function productFormFields(string $relativePath): array
{
    $source = file_get_contents(resource_path($relativePath));

    expect(preg_match('/useForm\(\s*\{(.*?)\n\}\)/s', $source, $matches))
        ->toBe(1, "Could not find a useForm({...}) block in {$relativePath}.");

    // Indentation varies between the components, so match any leading space.
    preg_match_all('/^[ \t]+(\w+)\s*:/m', $matches[1], $keys);

    expect($keys[1])->not->toBeEmpty("Could not read any fields from {$relativePath}.");

    return $keys[1];
}

test('the create form sends every field the store method requires', function () {
    $fields = productFormFields('js/Pages/product/Partials/ProductForm.vue');

    foreach (requiredProductFields('store') as $required) {
        if (! in_array($required, $fields, true)) {
            $this->fail("ProductForm.vue does not send the required field '{$required}', so saving a new product will fail validation.");
        }
    }

    expect($fields)->toContain(...requiredProductFields('store'));
});

test('the edit form sends every field the update method requires', function () {
    $fields = productFormFields('js/Pages/product/Edit.vue');

    foreach (requiredProductFields('update') as $required) {
        if (! in_array($required, $fields, true)) {
            $this->fail("Edit.vue does not send the required field '{$required}', so updating a product will fail validation.");
        }
    }

    expect($fields)->toContain(...requiredProductFields('update'));
});

test('the create form offers an image upload', function () {
    $source = file_get_contents(resource_path('js/Pages/product/Partials/ProductForm.vue'));

    expect($source)->toContain('type="file"')
        ->and($source)->toContain('image:');
});

test('the edit form offers an image upload', function () {
    $source = file_get_contents(resource_path('js/Pages/product/Edit.vue'));

    expect($source)->toContain('type="file"')
        ->and($source)->toContain('image:');
});

test('the forms post as multipart so the image can be uploaded', function () {
    $paths = [
        'js/Pages/product/Partials/ProductForm.vue',
        'js/Pages/product/Edit.vue',
    ];

    foreach ($paths as $path) {
        if (! str_contains(file_get_contents(resource_path($path)), 'forceFormData: true')) {
            $this->fail("{$path} must post as FormData or the image upload will be sent as a plain field.");
        }
    }

    expect(file_get_contents(resource_path($paths[0])))->toContain('forceFormData: true');
});
