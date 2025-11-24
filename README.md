# KS Fraser Event System

A PSR-14 compliant event system with extended listener management capabilities for PHP applications.

## Features

- **PSR-14 Compliance**: Full compatibility with PSR-14 Event Dispatcher standard
- **Extended Listener Management**: Additional methods for advanced listener control
- **Type-Safe**: Full PHP 8.1+ type declarations and strict typing
- **Singleton Pattern**: Convenient global access through EventManager
- **Plugin Support**: Built-in support for plugin lifecycle events

## Installation

```bash
composer require ksfraser/event-system
```

## Basic Usage

```php
use Ksfraser\EventSystem\EventManager;
use Ksfraser\EventSystem\MyCustomEvent;

// Dispatch an event
EventManager::dispatchEvent(new MyCustomEvent($data));

// Add a listener
EventManager::on('user.created', function($event) {
    // Handle user creation
});
```

## Creating Custom Events

```php
<?php
use Ksfraser\EventSystem\Event;

class UserCreatedEvent extends Event
{
    public function __construct(
        public readonly int $userId,
        public readonly string $email
    ) {}
}
```

## Advanced Listener Management

```php
use Ksfraser\EventSystem\EventManager;

// Add multiple listeners
EventManager::on('order.processed', [$orderService, 'sendConfirmation']);
EventManager::on('order.processed', 'sendOrderNotification');

// Get listener provider for advanced management
$listenerProvider = EventManager::getInstance()->getListenerProvider();
```

## Requirements

- PHP 8.1+
- PSR-14 Event Dispatcher interface

## Contributing

1. Fork the repository
2. Create a feature branch
3. Add tests for new functionality
4. Ensure all tests pass
5. Submit a pull request

## License

This project is licensed under the GPL v3 License.