<?php

$autoload = __DIR__ . '/../vendor/autoload.php';
if (file_exists($autoload)) {
    require_once $autoload;
    return;
}

// Minimal PSR-14 stubs for environments where dependencies aren't installed yet.
// These are only defined when the real interfaces are absent.
if (!interface_exists('Psr\\EventDispatcher\\StoppableEventInterface')) {
    require_once __DIR__ . '/stubs/psr-event-dispatcher.php';
}

spl_autoload_register(static function (string $class): void {
    $prefixes = [
        'Ksfraser\\EventSystem\\' => __DIR__ . '/../src/EventSystem/',
        'Ksfraser\\EventSystem\\Contracts\\' => __DIR__ . '/../src/Contracts/',
    ];

    if ($class === 'Ksfraser\\EventSystem\\EventManager') {
        $path = __DIR__ . '/../src/EventManager.php';
        if (file_exists($path)) {
            require_once $path;
        }
        return;
    }

    foreach ($prefixes as $prefix => $baseDir) {
        if (strncmp($class, $prefix, strlen($prefix)) !== 0) {
            continue;
        }

        $relative = substr($class, strlen($prefix));
        $file = $baseDir . str_replace('\\', '/', $relative) . '.php';

        if (file_exists($file)) {
            require_once $file;
        }

        return;
    }
});
