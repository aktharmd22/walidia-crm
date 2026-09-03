<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| The spacing scale is closed
|--------------------------------------------------------------------------
|
| tailwind.config.js REPLACES Tailwind's spacing scale with the project's own
| tokens rather than extending it. That is deliberate — it is what keeps the
| rhythm honest — but it has a sharp edge: an off-scale step like `w-11` is not
| an error, it simply compiles to nothing, and the element silently falls back
| to its intrinsic size.
|
| That is not a hypothetical. It shipped a 250px-tall logo on the sign-in
| screen and an eye button flush against the border of the password field,
| both of which read as sloppy work rather than as a missing class.
|
| So the scale is enforced here. Use a token (`gap-4`, `h-field`) or an
| arbitrary value (`w-[36px]`), never a step the config does not define.
|
*/

/** The steps tailwind.config.js actually declares. */
const SPACING_SCALE = [
    '0', 'px', '1', '2', '3', '4', '5', '6', '8', '10', '12',
    'row', 'row-rich', 'field', 'topbar', 'sidebar', 'rail',
];

/** Utilities whose values are drawn from `spacing`. */
const SPACING_UTILITIES = [
    'p', 'px', 'py', 'pt', 'pr', 'pb', 'pl', 'ps', 'pe',
    'm', 'mx', 'my', 'mt', 'mr', 'mb', 'ml', 'ms', 'me',
    'w', 'h', 'size', 'gap', 'gap-x', 'gap-y',
    'top', 'right', 'bottom', 'left', 'inset', 'inset-x', 'inset-y',
    'start', 'end', 'space-x', 'space-y',
    'min-w', 'min-h', 'max-w', 'max-h',
];

/**
 * The other closed scales.
 *
 * `borderRadius` and `boxShadow` are replaced the same way spacing is, so
 * `shadow-sm` and `rounded-lg` are not errors — they are nothing. Both had
 * already shipped by the time this was written.
 */
it('never uses a radius or shadow the Tailwind config does not define', function (): void {
    $scales = [
        'rounded' => ['none', 'pill', 'card', 'shell', 'full'],
        'shadow' => ['none', 'card', 'pop', 'modal', 'toast'],
    ];

    $offenders = [];

    /** @var SplFileInfo $file */
    foreach (new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator(resource_path('js'), FilesystemIterator::SKIP_DOTS),
    ) as $file) {
        if (! in_array($file->getExtension(), ['ts', 'tsx'], true)) {
            continue;
        }

        foreach (file($file->getPathname()) as $number => $line) {
            foreach ($scales as $utility => $allowed) {
                preg_match_all('/(?<![\w-])'.$utility.'-([a-z0-9\[\]#().,%\/-]+)/', $line, $matches);

                foreach ($matches[1] as $value) {
                    // Arbitrary values always compile; so do the directional
                    // forms (rounded-e-card), which carry the scale after them.
                    if (str_starts_with($value, '[')) {
                        continue;
                    }

                    $step = $value;

                    foreach (['t-', 'b-', 's-', 'e-', 'l-', 'r-', 'tl-', 'tr-', 'bl-', 'br-', 'ss-', 'se-', 'es-', 'ee-'] as $side) {
                        if (str_starts_with($value, $side)) {
                            $step = substr($value, strlen($side));
                            break;
                        }
                    }

                    if (in_array($step, $allowed, true)) {
                        continue;
                    }

                    $relative = str_replace(base_path().DIRECTORY_SEPARATOR, '', $file->getPathname());
                    $offenders[] = sprintf('%s:%d  %s-%s', $relative, $number + 1, $utility, $value);
                }
            }
        }
    }

    expect($offenders)->toBe([], 'These compile to nothing:
'.implode('
', $offenders));
});

it('never uses a spacing step the Tailwind config does not define', function (): void {
    $utilities = SPACING_UTILITIES;
    usort($utilities, fn (string $a, string $b): int => strlen($b) <=> strlen($a));

    $pattern = '/(?<![\w-])-?('.implode('|', array_map('preg_quote', $utilities)).')-([a-z0-9.\/\[\]]+)/';

    $offenders = [];

    /** @var SplFileInfo $file */
    foreach (new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator(resource_path('js'), FilesystemIterator::SKIP_DOTS),
    ) as $file) {
        if (! in_array($file->getExtension(), ['ts', 'tsx'], true)) {
            continue;
        }

        foreach (file($file->getPathname()) as $number => $line) {
            preg_match_all($pattern, $line, $matches, PREG_SET_ORDER);

            foreach ($matches as [$_, $utility, $value]) {
                // Arbitrary values and fractions come from elsewhere and always compile.
                if (str_starts_with($value, '[') || str_contains($value, '/')) {
                    continue;
                }

                // A named value (auto, full, prose…) belongs to the utility's own scale.
                if (preg_match('/^[0-9]+(\.[0-9]+)?$/', $value) !== 1) {
                    continue;
                }

                if (in_array($value, SPACING_SCALE, true)) {
                    continue;
                }

                $relative = str_replace(base_path().DIRECTORY_SEPARATOR, '', $file->getPathname());
                $offenders[] = sprintf('%s:%d  %s-%s', $relative, $number + 1, $utility, $value);
            }
        }
    }

    expect($offenders)->toBe([], "These compile to nothing. Use a token or an arbitrary value:\n".implode("\n", $offenders));
});
