<?php

declare(strict_types=1);

use App\Http\Controllers\ResourceController;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

/**
 * A controller that renders a page component which does not exist fails only in
 * the browser, at the moment a user opens it. This closes that gap at build
 * time — for explicit renders and for the ones the shared CRUD base derives
 * from its $pages property.
 */

/**
 * @return array<string, string> page name => where it is rendered from
 */
function referencedPages(): array
{
    $referenced = [];

    // 1. Explicit Inertia::render('Some/Page') calls.
    foreach (File::allFiles(app_path()) as $file) {
        if ($file->getExtension() !== 'php') {
            continue;
        }

        preg_match_all(
            "/Inertia::render\(\s*'([^']+)'/",
            (string) file_get_contents($file->getPathname()),
            $matches,
        );

        foreach ($matches[1] as $page) {
            $referenced[$page] = str_replace('\\', '/', $file->getRelativePathname());
        }
    }

    // 2. The pages the shared CRUD base renders from $pages — index, create,
    //    show, edit and archive — which no string search would find.
    foreach (File::allFiles(app_path('Http/Controllers')) as $file) {
        $class = 'App\\Http\\Controllers\\'.str_replace(
            ['/', '.php'],
            ['\\', ''],
            Str::after(str_replace('\\', '/', $file->getPathname()), 'Http/Controllers/'),
        );

        if (! class_exists($class) || ! is_subclass_of($class, ResourceController::class)) {
            continue;
        }

        $reflection = new ReflectionClass($class);

        if ($reflection->isAbstract()) {
            continue;
        }

        $property = $reflection->getProperty('pages');
        $directory = $property->getDefaultValue();

        if (! is_string($directory) || $directory === '') {
            continue;
        }

        foreach (['Index', 'Create', 'Show', 'Edit', 'Archive'] as $page) {
            // Only assert the ones the controller actually exposes.
            $method = strtolower($page);

            if (! $reflection->hasMethod($method === 'index' ? 'index' : $method)) {
                continue;
            }

            $referenced["{$directory}/{$page}"] = $reflection->getShortName();
        }
    }

    return $referenced;
}

it('has a React component for every page a controller can render', function (): void {
    $referenced = referencedPages();

    expect($referenced)->not->toBeEmpty();

    $missing = [];

    foreach ($referenced as $page => $source) {
        if (! File::exists(resource_path("js/pages/{$page}.tsx"))) {
            $missing[] = "{$page} (rendered by {$source})";
        }
    }

    expect($missing)->toBe([]);
});

it('keeps an error page for the statuses the exception handler renders', function (): void {
    expect(File::exists(resource_path('js/pages/Errors/Status.tsx')))->toBeTrue();
});
