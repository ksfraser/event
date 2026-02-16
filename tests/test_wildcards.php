<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Ksfraser\Event\EventManager;
use Ksfraser\Event\Contracts\EventInterface;

class TestEvent implements EventInterface {
    public function getName(): string { return 'TEST_EVENT'; }
    public function isPropagationStopped(): bool { return false; }
    public function stopPropagation(): void {}
}

$em = EventManager::getInstance();

$output = [];

// Specific Listener (Priority 10)
$em->addListener('TEST_EVENT', function($e) use (&$output) {
    $output[] = "Specific (10)";
}, 10);

// Wildcard * (Priority 5)
$em->addListener('*', function($e) use (&$output) {
    $output[] = "Wildcard * (5)";
}, 5);

// Wildcard ** (Priority 20)
$em->addListener('**', function($e) use (&$output) {
    $output[] = "Wildcard ** (20)";
}, 20);

$em->dispatch(new TestEvent());

echo implode("\n", $output);
