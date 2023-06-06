Installation
============

Make sure Composer is installed globally, as explained in the
[installation chapter](https://getcomposer.org/doc/00-intro.md)
of the Composer documentation.

Applications that use Symfony Flex
----------------------------------

Open a command console, enter your project directory and execute:

```console
$ composer require <package-name>
```

Applications that don't use Symfony Flex
----------------------------------------

### Step 1: Download the Bundle

Open a command console, enter your project directory and execute the
following command to download the latest stable version of this bundle:

```console
$ composer require <package-name>
```

### Step 2: Enable the Bundle

Then, enable the bundle by adding it to the list of registered bundles
in the `config/bundles.php` file of your project:

// config/bundles.php

```php

return [
    // ...
    AnyContent\Backend\AnyContentBackendBundle::class => ['all' => true],
];
```
_This should have happened automatically._

### Step 3: Configure the routes

config/routes/anycontent.yaml

```yaml
anycontent_backend:
    resource: '@AnyContentBackendBundle/Resources/config/routes.yaml'
    prefix: /anycontent
```


### Step 4: Configure Users

config/packages/security.yaml

```yaml
    # simple http authentication
    firewalls:
      anycontent:
        pattern: ^/anycontent
        lazy: true
        provider: users_in_memory
        http_basic:
          realm: Secured Area
```

```yaml
    # form based login
    anycontent:
      pattern: ^/anycontent
      lazy: false
      provider: users_in_memory
      form_login:
        login_path: anycontent_login
        check_path: anycontent_login
      logout:
        path: anycontent_logout
        target: anycontent_start
```


