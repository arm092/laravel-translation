# Changelog

All notable changes to this project are documented in this file.

## [4.1.7] - 2026-09-26

### Documentation

- Added a GitHub Sponsors badge near the top of the README and configured the repository's native Sponsor button for Arm092.

## [4.1.6] - 2026-09-23

### Fixed

- Render Laravel's trusted localized pagination labels without escaping their arrow entities or adding duplicate arrows.

### Changed

- Reduced the default translation-manager page size from 50 to 25 and added 10 to the existing 25/50/100 selector.
- Added visible, localized labels above search, language, group, and page-size controls in both Livewire and fallback modes.

## [4.1.5] - 2026-09-23

### Changed

- Replaced the minimal previous/current/next translation paginator with accessible numbered pagination, active and disabled states, pointer cursors, Apricode hover/focus styles, and a compact mobile layout in both Livewire and fallback modes.

## [4.1.4] - 2026-09-23

### Fixed

- Livewire 4 now owns translation search, group filtering, page-size selection, results, and pagination in one component, so search updates no longer navigate or replace the page body.
- The standalone Fetch search asset is now loaded only by the non-Livewire fallback frontend.

## [4.1.3] - 2026-09-23

### Fixed

- Debounced translation-key search now updates results without a full page reload, preventing page flashes and lost input focus in both fallback and Livewire 4 modes.

## [4.1.2] - 2026-09-14

### Fixed

- Translation language, group, and page-size filters now apply immediately when their selection changes.
- Translation search now applies automatically after a short debounce, and clearing the search restores the complete filtered list without requiring Enter.
- File-driver tests now use isolated temporary language directories, preventing concurrent or interrupted test runs from modifying shared fixtures.

### Changed

- Removed redundant markup, service-provider, and duplicate Livewire assertions that did not protect observable package behavior.
- Corrected the route middleware string test so configuration is applied before package routes are registered.
- Updated the Vitest development dependency to 4.1.11 to resolve its path-traversal advisory.
- Added the explicit Eloquent relation type required by current Larastan releases.

## [4.1.1] - 2026-08-11

### Changed

- Re-published the 4.1 quality toolkit under a new immutable version after Packagist retained metadata for an earlier deleted `4.1.0` tag.

There are no code or compatibility changes from the intended 4.1.0 release.

## [4.1.0] - 2026-08-11

### Added

- PHP-Parser 5 AST scanner with occurrence locations, Blade and Laravel call-form support, dynamic/ambiguous warnings, ignored keys, excluded paths, safe fingerprint caching, and CI-oriented scan/missing/unused commands.
- CSV v1 import/export, dry-run import planning, conflict policies, reversible spreadsheet-formula protection, deterministic formatting, public exporter/importer/formatter/batch-writer contracts, and replaceable container bindings.
- Read-only translation quality dashboard with summaries, filters, pagination, and CSV export behind the existing middleware and authorization gate.
- Protected locales with explicit CLI override, driver-neutral 25/50/100 pagination, database key hashes, deduplication migration, chunked upserts, and Windows/database CI coverage.
- Larastan/PHPStan and Pint checks without a baseline.

### Changed

- Database `allTranslations()` eagerly loads languages and translations instead of issuing one query per locale.
- `translation:sync-translations` now supports `--dry-run` and `--conflict=overwrite|skip|fail`; `overwrite` remains the default.
- Translation writes continue to invalidate package and Laravel Translator caches after successful batch work.

### Security

- Scanner paths use realpath containment and never follow symlinks.
- CSV cells that spreadsheet software could interpret as formulas are escaped on export.
- Unused keys and dynamic expressions are advisory only and are never deleted or created automatically.

There are no breaking changes from 4.0.x.

## [4.0.2] - 2026-08-11

### Added

- Stable `source_locale` configuration for applications that change the runtime locale in middleware.
- Support for Laravel's `route_group_config.as` option across package routes, redirects, views, and inline-save endpoints.
- Packagist version and download badges in the README.

### Changed

- Database mode now composes with Laravel's file loader. Database values override matching values while application JSON files, package namespaces, and vendor translations remain available as fallback values.
- Database group translations are restored to nested arrays before they are returned through Laravel's loader contract.

### Fixed

- Missing database tables, empty databases, missing locales, and missing groups now return safe fallback arrays during application and migration bootstrap.
- Namespace and JSON-path registrations are delegated to Laravel's native loader in database mode.
- Writes invalidate both driver caches and Laravel Translator's loaded-group cache for long-running processes.
- Invalid PHP translation files now report the relative file and returned type instead of producing an indirect iteration error.
- Regression coverage now protects template-like and HTML payload escaping in the fallback editor.

## [4.0.1] - 2026-08-10

### Added

- Laravel 13 support and a Laravel 10–13 Testbench/PHPUnit CI matrix.
- Configurable web-manager authorization through `authorization_gate` and string-or-array route middleware.
- Automatic optional Livewire 4 inline editing when `livewire/livewire:^4.0` is installed and active.
- A namespaced Livewire translation-input component with locked identifiers, server-side validation, and idle/loading/saved/error states.
- Dedicated Livewire 4 compatibility jobs for every Laravel 10–13 matrix line and a Livewire 3 fallback job.
- Path traversal defenses for locale, namespace, and group identifiers.
- JavaScript tests, palette validation, dependency audits, syntax checks, and committed-asset verification.
- Explicit, visible non-blocking advisory reports for Laravel 10/11 and the intentionally lowest-dependency Laravel 13 job; normal Laravel 12 and latest Laravel 13 audits remain release-blocking.
- Detailed configuration, Livewire, authorization, assets, troubleshooting, and 3.x-to-4.x upgrade documentation.

### Changed

- Minimum versions are now PHP 8.1 and Laravel 10.
- The PHP namespace is now `Arm092\Translation`; the Composer package name remains `arm092/laravel-translation`.
- Service providers, controller routes, migrations, validation rules, and language publishing use current Laravel APIs.
- The database translator loader is registered only for the database driver.
- Frontend tooling now uses Vite 8, Tailwind CSS 4, Alpine.js 3, Blade, and vanilla Fetch.
- The default manager UI now uses a wide, responsive top-navigation layout with clearer tables, forms, filters, focus states, and the eight semantic Apricode palette tokens.
- README branding now uses the Apricode logo and an up-to-date screenshot of the sidebar-free manager UI.
- Select controls suppress the browser-native indicator and render one consistent package caret.
- HTTP and Livewire writes share one translation-write action and continue to dispatch `TranslationAdded`.
- Manager routes and Livewire mutations share one authorizer; the configured gate is rechecked before every Livewire save.
- Livewire mode uses Livewire's Alpine/runtime assets and omits the package Alpine bundle. Fallback behavior and public asset URLs remain unchanged.

### Fixed

- Cross-language leakage caused by an ungrouped `orWhereNull` database condition.
- Legacy translation updates now remain scoped to the requested language.
- Translation caches are invalidated after writes.
- Validation and session messages are escaped in package views.
- Inline-save endpoints are generated by Laravel named routes.

### Security

- Livewire component locale, group, and translation-key properties are locked against client mutation.
- Livewire payloads use the same locale, namespace, group, key, and value validation rules as the HTTP endpoint.

### Removed

- Laravel 8/9 and PHP 8.0 support.
- Legacy factories, internal global helpers, old PHPUnit annotations/XML, Vue, Axios, Laravel Mix, and the legacy Tailwind/PostCSS chain.
- The library `composer.lock`; each CI line resolves and audits its own dependency graph.

### Compatibility

- Livewire remains optional and is not a runtime Composer requirement.
- Livewire 3, an inactive Livewire provider, no Livewire installation, and unsupported future majors use the Blade/Alpine/Fetch frontend.
- Laravel 10–13, PHP 8.1+, and both file/database drivers are supported.

## [3.0.1]

- Added Laravel 12 support.

## [3.0.0]

- Added Laravel 10/11 support and established the 3.x package line.
