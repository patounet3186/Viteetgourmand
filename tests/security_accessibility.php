<?php

declare(strict_types=1);

$viewsDirectory = dirname(__DIR__) . '/app/Views';
$failures = [];

/** @return list<string> */
function viewFiles(string $directory): array
{
    $paths = [];
    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS)
    );

    foreach ($files as $file) {
        if ($file->isFile() && $file->getExtension() === 'php') {
            $paths[] = $file->getPathname();
        }
    }

    sort($paths);
    return $paths;
}

function lineNumber(string $content, int $offset): int
{
    return substr_count(substr($content, 0, $offset), "\n") + 1;
}

function hasAttribute(string $attributes, string $name): bool
{
    return preg_match('/\b' . preg_quote($name, '/') . '\s*=/i', $attributes) === 1;
}

foreach (viewFiles($viewsDirectory) as $path) {
    $content = file_get_contents($path);
    if ($content === false) {
        $failures[] = "Unreadable view: {$path}";
        continue;
    }

    $relativePath = str_replace(
        '\\',
        '/',
        substr($path, strlen(dirname(__DIR__)) + 1)
    );

    $markup = preg_replace_callback(
        '/<\?(?:php|=)?[\s\S]*?\?>/',
        static function (array $match): string {
            return 'PHP_VALUE' . str_repeat("\n", substr_count($match[0], "\n"));
        },
        $content
    ) ?? $content;

    preg_match_all(
        '/<form\b(?=[^>]*\bmethod\s*=\s*["\']post["\'])[^>]*>.*?<\/form>/is',
        $content,
        $postForms,
        PREG_OFFSET_CAPTURE
    );
    foreach ($postForms[0] as [$form, $offset]) {
        if (!str_contains($form, 'csrf_field(')) {
            $failures[] = sprintf(
                'POST form without CSRF field: %s:%d',
                $relativePath,
                lineNumber($content, $offset)
            );
        }
    }

    preg_match_all('/<label\b([^>]*)>/is', $markup, $labels, PREG_OFFSET_CAPTURE);
    foreach ($labels[1] as [$attributes, $offset]) {
        if (!hasAttribute($attributes, 'for')) {
            $failures[] = sprintf(
                'Label without for attribute: %s:%d',
                $relativePath,
                lineNumber($markup, $offset)
            );
        }
    }

    preg_match_all(
        '/<(input|select|textarea)\b([^>]*)>/is',
        $markup,
        $controls,
        PREG_SET_ORDER | PREG_OFFSET_CAPTURE
    );
    foreach ($controls as $control) {
        $tag = strtolower($control[1][0]);
        $attributes = $control[2][0];
        $offset = $control[0][1];
        $isHiddenInput = $tag === 'input'
            && preg_match('/\btype\s*=\s*["\']hidden["\']/i', $attributes) === 1;

        if (
            !$isHiddenInput
            && !hasAttribute($attributes, 'id')
            && !hasAttribute($attributes, 'aria-label')
            && !hasAttribute($attributes, 'aria-labelledby')
        ) {
            $failures[] = sprintf(
                'Form control without accessible name hook: %s:%d',
                $relativePath,
                lineNumber($markup, $offset)
            );
        }
    }

    preg_match_all('/<img\b([^>]*)>/is', $markup, $images, PREG_OFFSET_CAPTURE);
    foreach ($images[1] as [$attributes, $offset]) {
        if (!hasAttribute($attributes, 'alt')) {
            $failures[] = sprintf(
                'Image without alt attribute: %s:%d',
                $relativePath,
                lineNumber($markup, $offset)
            );
        }
    }
}

if ($failures !== []) {
    foreach ($failures as $failure) {
        fwrite(STDERR, $failure . PHP_EOL);
    }
    exit(1);
}

echo 'Security and accessibility checks passed.' . PHP_EOL;
