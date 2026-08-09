# Upgrade guide: 3.x to 4.x

Laravel Translation 4.0 is a major release. Follow this checklist in a test environment before deploying it.

## 1. Confirm platform support

Version 4 requires PHP 8.1+ and Laravel 10–13. Laravel 8/9 and PHP 8.0 are no longer supported.

Update the package constraint and resolve dependencies without reusing a library lock file:

```shell
composer require arm092/laravel-translation:^4.0 --with-all-dependencies
composer audit
```

## 2. Publish and review configuration

Back up application customizations, then publish the v4 configuration:

```shell
php artisan vendor:publish --provider="JoeDixon\Translation\TranslationServiceProvider" --tag=config --force
```

Review and restore intentional settings. The middleware value now accepts a string or array, and `authorization_gate` is new:

```php
'route_group_config' => [
    'middleware' => ['web', 'auth'],
],
'authorization_gate' => 'manage-translations',
```

Leaving the gate `null` preserves 3.x access behavior. Production applications should define an application-owned gate and use it with `auth`; see the README for a complete example and security rationale.

## 3. Republish frontend assets

Version 4 replaces Vue/Axios/Mix with Blade, Alpine, Fetch, Tailwind CSS 4, and Vite 8. Republish the compiled package assets:

```shell
php artisan vendor:publish --provider="JoeDixon\Translation\TranslationServiceProvider" --tag=assets --force
```

The public URLs remain `/vendor/translation/css/main.css` and `/vendor/translation/js/app.js`. Remove application overrides that rely on the old Vue component or Mix manifest. If you maintain customized published views, compare them with v4 before deployment.

Package contributors who rebuild assets need Node `^20.19` or `>=22.12`. Host applications that only publish the compiled files do not need Node for this package.

## 4. Review application integrations

- Legacy package factories and global helper functions were removed. Replace direct dependencies on those internal implementation details.
- Routes now use controller-class actions; named route behavior and manager URLs remain available.
- Package translations publish through Laravel's `langPath()`. Move unusual custom paths to Laravel's supported language-path configuration.
- File identifiers are stricter. Supported locales include `en_US` and `pt-BR`; vendor namespaces and dotted keys remain supported. Traversal paths and directory separators are rejected.
- The custom translator loader is registered only when the database driver is active.
- Artisan commands are not protected by the new web authorization gate.

## 5. Database-driver checks

No destructive migration is required. Confirm the configured connection and table names, run normal migrations, and exercise synchronization in a copy of production data:

```shell
php artisan migrate
php artisan translation:sync-translations
```

Version 4 fixes cross-language selection of legacy null-group rows, scopes legacy updates to the requested language, and invalidates cached group translations after writes.

## 6. Clear caches and verify

```shell
php artisan optimize:clear
```

Verify the following as an allowed user and a denied user:

1. language list and creation forms;
2. translation filters and creation forms;
3. inline save loading, success, and failure states;
4. keyboard focus visibility on desktop and mobile;
5. file or database persistence in the selected locale;
6. no browser console or network errors.

Keep a rollback path to the previous application release and package constraint until these checks pass in the target environment.
