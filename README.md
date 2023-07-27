Installation
============

Make sure Composer is installed globally, as explained in the
[installation chapter](https://getcomposer.org/doc/00-intro.md)
of the Composer documentation.


### Step 1: Download the Bundle

Require the package in the composer json:

```json
"nhagemann/anycontent-backend": "dev-main"
```

Download the sourcecode and tell where to find it with:

```json
    "repositories": [
        {"type": "path", "url":  "path/to/bundle"}
    ]
```

Later on it will be a normal composer require


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
                        nils: { password: '$2y$13$DSTS4mBmIIBzzgi/tXB0mOrNy4vX/k6hcCl2oLijJaM24tEkzMose', roles: [ 'ROLE_ANYCONTENT' ] }
                        tim: { password: '$2y$13$DSTS4mBmIIBzzgi/tXB0mOrNy4vX/k6hcCl2oLijJaM24tEkzMose', roles: [ 'ROLE_ANYCONTENT' ] }
```

### Step 6: Configure Repositories



todo:
- limit mysql configuration to lowercase, allow title 
- remove revisions command
- phpstan
- admin routes
- additional connection types

ignored:
- import/export/archive commands Core/Edit/Exchange + Admin/Excelbackup
- events
- formelement geolocation, content list view map
- formelement sourcecode

idea:
- https://symfony.com/doc/current/service_container/tags.html
- https://symfony.com/doc/current/security/voters.html
- repository: read / admin
- record: create / read / update / delete / sort
- $this->denyAccessUnlessGranted(PostVoter::VIEW, $post);
- webp
- help

bugs:
- no save message when saving config with sequence
- add when editing record with sequence broken
