<?php

namespace Arm092\Translation\Tests;

use Arm092\Translation\Actions\WriteTranslation;
use Arm092\Translation\Contracts\TranslationBatchWriter;
use Arm092\Translation\Contracts\TranslationExporter;
use Arm092\Translation\Contracts\TranslationImporter;
use Arm092\Translation\Drivers\Translation;
use Arm092\Translation\Exceptions\ProtectedLocaleException;
use Arm092\Translation\TranslationBindingsServiceProvider;
use Arm092\Translation\TranslationServiceProvider;
use Orchestra\Testbench\TestCase;

class ToolkitTest extends TestCase
{
    private string $csv;

    protected function setUp(): void
    {
        parent::setUp();
        app()['path.lang'] = __DIR__.'/fixtures/lang';
        $this->csv = sys_get_temp_dir().DIRECTORY_SEPARATOR.'translations-'.bin2hex(random_bytes(5)).'.csv';
    }

    protected function tearDown(): void
    {
        if (isset($this->csv) && file_exists($this->csv)) {
            unlink($this->csv);
        }
        parent::tearDown();
    }

    protected function getPackageProviders($app): array
    {
        return [TranslationServiceProvider::class, TranslationBindingsServiceProvider::class];
    }

    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('translation.driver', 'file');
    }

    public function test_csv_v1_round_trip_plan_preserves_values(): void
    {
        $count = app(TranslationExporter::class)->export($this->csv, ['en', 'es']);
        $contents = file_get_contents($this->csv);
        $plan = app(TranslationImporter::class)->import($this->csv);

        $this->assertGreaterThan(0, $count);
        $this->assertStringStartsWith('type,namespace,group,key,en,es', $contents);
        $this->assertFalse($plan['committed']);
        $this->assertSame(0, $plan['written']);
    }

    public function test_manager_write_rejects_a_protected_locale(): void
    {
        config()->set('translation.protected_locales', ['es']);

        $this->expectException(ProtectedLocaleException::class);
        app(WriteTranslation::class)->handle(app(Translation::class), 'es', null, 'test', 'hello', 'Hola', true);
    }

    public function test_csv_formula_escaping_is_reversible(): void
    {
        file_put_contents($this->csv, "type,namespace,group,key,en\ngroup,,messages,danger,'=HYPERLINK(\"\"https://example.test\"\")\n");
        $capture = new class implements TranslationBatchWriter
        {
            public array $rows = [];

            public function write(array $rows, string $conflict = 'overwrite', bool $forceProtected = false): int
            {
                $this->rows = $rows;

                return count($rows);
            }
        };
        $this->app->instance(TranslationBatchWriter::class, $capture);
        $result = app(TranslationImporter::class)->import($this->csv, 'overwrite', true);

        $this->assertSame(1, $result['written']);
        $this->assertStringStartsWith('=HYPERLINK', $capture->rows[0]['value']);
    }
}
