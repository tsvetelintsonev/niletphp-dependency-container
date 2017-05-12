# PHP dependency injection container

Dependency container which provides an easy way for managing dependencies in your PHP projects.

### Requirements
`PHP >= 7.0`

### Install

Composer

```javascript
{
    "require": {
        "niletphp/dependency-container": ">=v1.0"
    }
}
```

### Examples

```php
$dc = new Nilet\Components\Container\DependencyContainer();
```

Resolve dependencies for a given concrete and return a new instance of it

```php
$dc->create("Nilet\Foo");
```

Bind a concrete to interface. 
Resolving such binding will always return new concrete instance

```php
$dc->bind("Nilet\FooInterface", "Nilet\Foo");
$dc->bind("Nilet\FooInterface", function ($dc) {
    return new Nilet\Foo();
});
$dc->create("Nilet\FooInterface");
```

Share a concrete (singleton). 

```php
$dc->share("Nilet\Foo");
$dc->share("Nilet\Foo", function ($dc) {
    return new Nilet\Foo();
});
```

Binding a concrete (singleton) to an Interface

```php
$dc->bindShared("Nilet\FooInterface", "Nilet\Foo");
$dc->bindShared("Nilet\FooInterface", function ($dc) {
    return new Nilet\Foo();
});
```

Register a concrete instance (singleton). 

```php
$foo = new Nilet\Foo();
$dc->instance("Nilet\Foo", $foo);
```

Retrieve a concrete. 

```php
$dc->get("Nilet\Foo");

$dc->bindShared("Nilet\FooInterface", "Nilet\Foo");
$dc->get("Nilet\FooInterface");
```

Determine if a given concrete (singleton) has been resolved.

```php
$dc->bindShared("Nilet\FooInterface", "Nilet\Foo");
$dc->get("Nilet\FooInterface");
if ($dc->isResolved("Nilet\FooInterface")) { // evaluates to true
    // do something
} else {
    // do something else
}
```

Determine if a given concrete is shared.

```php
$dc->share("Nilet\Foo");
if ($dc->isShared("Nilet\Foo")) { // evaluates to true
    // do something
} else {
    // do something else
}
```

Determine if a given interface is bound.

```php
$dc->bind("Nilet\FooInterface", "Nilet\Foo");
if ($dc->isBound("Nilet\FooInterface")) { //evaluates to true
    // do something
} else {
    // do something else
}
```

Determine if a given interface is bound/shared.

```php
$dc->bindShared("Nilet\FooInterface", "Nilet\Foo");
if ($dc->isBoundShared("Nilet\FooInterface")) { //evaluates to true
    // do something
} else {
    // do something else
}
```
