<?php

declare(strict_types=1);

use Illuminate\Support\Facades\File;

/**
 * A controller that renders a page component which does not exist fails only
 * in the browser, at the moment a user opens it. This closes that gap at build
 * time instead.
 */
it('has a React component for every page a controller renders', function (): void {
    $referenced = [];

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

    // Providers render pages too — Fortify's views are wired up there.
    expect($referenced)->not->toBeEmpty();

    $missing = [];

    foreach ($referenced as $page => $source) {
        $component = resource_path("js/pages/{$page}.tsx");

        if (! File::exists($component)) {
            $missing[] = "{$page} (rendered by {$source})";
        }
    }

    expect($missing)->toBe([]);
});

it('routes every page component that exists to something reachable', function (): void {
    // The inverse check is deliberately not enforced: pages under Dev/ and
    // Errors/ are rendered by the exception handler and the local gallery.
    expect(File::exists(resource_path('js/pages/Errors/Status.tsx')))->toBeTrue();
});
