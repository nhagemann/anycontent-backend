Installation
============

Make sure Composer is installed globally, as explained in the
[installation chapter](https://getcomposer.org/doc/00-intro.md)
of the Composer documentation.


### Step 1: Install via Composer

Require the package in the composer json:

```console
composer require nhagemann/anycontent-backend
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
### Step 3: Install assets

Install assets manually if necessary, another composer update should already do the trick. If a bundles/anycontentbackend folder appears in your public folder, you're good.

```console
    assets:install public
```


### Step 4: Configure the routes

config/routes/anycontent.yaml

```yaml
anycontent_backend:
    resource: '@AnyContentBackendBundle/Resources/config/routes.yaml'
    prefix: /anycontent
```

You might need to clear the Symfony cache after that. Browse /anycontent to see a security warning, as you are not logged in.

### Step 5: Configure Users

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
    firewalls:
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

```yaml
    # some in memory users
    providers:
        users_in_memory:
                memory:
                    users: # get new password hash via php -r "echo password_hash('****', PASSWORD_BCRYPT, ['cost' => 13]) . PHP_EOL;"
                        yourusername: { password: 'puthashhere', roles: [ 'ROLE_ANYCONTENT' ] }

```

### Step 6: Configure Repositories

Add section any_content_backend: to your config, here some examples for different connection types:

```yaml
any_content_backend:
  connections:
    - { name: 'recordsfile1', type: 'recordsfile', cmdl_file: '%kernel.project_dir%/../_repositories/recordsfile1/test1.cmdl', content_file: '%kernel.project_dir%/../_repositories/recordsfile1/test1.json', files_path: '%kernel.project_dir%/../_repositories/_files' }
    - { name: 'recordsfile1', type: 'recordsfile', cmdl_file: '%kernel.project_dir%/../_repositories/recordsfile1/test2.cmdl', content_file: '%kernel.project_dir%/../_repositories/recordsfile1/test2.json'}
    - { name: 'recordsfile1', type: 'recordsfile', cmdl_file: '%kernel.project_dir%/../_repositories/recordsfile1/config/test.cmdl', config_file: '%kernel.project_dir%/../_repositories/recordsfile1/config/test.json'}
    - { name: 'recordfiles1', type: 'recordfiles', cmdl_file: '%kernel.project_dir%/../_repositories/recordfiles1/test.cmdl', content_path: '%kernel.project_dir%/../_repositories/recordfiles1', files_path: '%kernel.project_dir%/../_repositories/_files' }
    - { name: 'recordfiles1', type: 'recordfiles', cmdl_file: '%kernel.project_dir%/../_repositories/recordfiles1/config/test.cmdl', config_file: '%kernel.project_dir%/../_repositories/recordfiles1/config/test.json', files_path: '%kernel.project_dir%/../_repositories/_files' }
    - { name: 'archive1', type: 'contentarchive', data_path: '%kernel.project_dir%/../_repositories/archive1', files_path: '%kernel.project_dir%/../_repositories/_files' }
    - { name: 'mysql', type: 'mysql', db_host: 'anycontent-backend-mysql', db_name: 'anycontent', db_user: 'user', db_password: 'password', cmdl_path: '%kernel.project_dir%/../_repositories/mysql', files_path: '%kernel.project_dir%/../_repositories/_files'}
  ```
