<?php

namespace Arm092\Translation\Tests;

use Arm092\Translation\ContractDatabaseLoader;
use Arm092\Translation\Drivers\Translation;
use Illuminate\Contracts\Translation\Loader;
use PHPUnit\Framework\TestCase;

class ContractDatabaseLoaderTest extends TestCase
{
    public function test_database_values_override_file_values_and_nested_groups_are_restored(): void
    {
        $driver = new LoaderTranslationFake(
            group: collect([
                'validation' => collect([
                    'required' => 'Database required',
                    'attributes.email' => 'Database email',
                ]),
            ]),
        );
        $fallback = new FileLoaderFake([
            'required' => 'File required',
            'email' => 'File email',
            'file_only' => 'File only',
        ]);

        $loaded = (new ContractDatabaseLoader($driver, $fallback))->load('en', 'validation');

        $this->assertSame('Database required', $loaded['required']);
        $this->assertSame('Database email', $loaded['attributes']['email']);
        $this->assertSame('File only', $loaded['file_only']);
    }

    public function test_json_values_are_merged_without_expanding_dotted_keys(): void
    {
        $driver = new LoaderTranslationFake(
            single: collect(['single' => collect(['Hello.world' => 'Database'])]),
        );
        $fallback = new FileLoaderFake(['Hello.world' => 'File', 'File only' => 'File only']);

        $loaded = (new ContractDatabaseLoader($driver, $fallback))->load('en', '*', '*');

        $this->assertSame([
            'Hello.world' => 'Database',
            'File only' => 'File only',
        ], $loaded);
    }

    public function test_missing_database_values_return_an_array_and_loader_registrations_are_delegated(): void
    {
        $fallback = new FileLoaderFake([]);
        $loader = new ContractDatabaseLoader(new LoaderTranslationFake, $fallback);

        $this->assertSame([], $loader->load('missing', 'messages'));

        $loader->addNamespace('package', '/translations');
        $loader->addJsonPath('/json');

        $this->assertSame(['package' => '/translations'], $loader->namespaces());
        $this->assertSame(['/json'], $fallback->jsonPaths);
    }
}

final class LoaderTranslationFake extends Translation
{
    public function __construct(
        private $single = null,
        private $group = null,
    ) {
        $this->single ??= collect();
        $this->group ??= collect();
    }

    public function getSingleTranslationsFor($locale)
    {
        return $this->single;
    }

    public function getGroupTranslationsFor($locale)
    {
        return $this->group;
    }
}

final class FileLoaderFake implements Loader
{
    public array $namespaces = [];

    public array $jsonPaths = [];

    public function __construct(private array $values) {}

    public function load($locale, $group, $namespace = null)
    {
        return $this->values;
    }

    public function addNamespace($namespace, $hint)
    {
        $this->namespaces[$namespace] = $hint;
    }

    public function addJsonPath($path)
    {
        $this->jsonPaths[] = $path;
    }

    public function namespaces()
    {
        return $this->namespaces;
    }
}
