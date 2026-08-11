<?php

namespace Arm092\Translation\Tests;

use Arm092\Translation\Scanner;
use Illuminate\Filesystem\Filesystem;
use PHPUnit\Framework\TestCase;

class ScannerAdvancedTest extends TestCase
{
    private string $directory;

    protected function setUp(): void
    {
        $this->directory = sys_get_temp_dir().DIRECTORY_SEPARATOR.'translation-scanner-'.bin2hex(random_bytes(5));
        mkdir($this->directory);
    }

    protected function tearDown(): void
    {
        (new Filesystem)->deleteDirectory($this->directory);
    }

    public function test_ast_scanner_reports_supported_calls_and_dynamic_usage(): void
    {
        file_put_contents($this->directory.'/Calls.php', <<<'PHP'
<?php
__('messages.saved');
trans_choice('cart.items', 2);
Lang::get('auth.failed');
app('translator')->get('json key');
__($dynamicKey);
PHP);
        file_put_contents($this->directory.'/view.blade.php', "@lang('nav.home')");
        $scanner = new Scanner(new Filesystem, [$this->directory], ['__', 'trans'], [], [], $this->directory.'/cache.json');

        $result = $scanner->scan(true);

        $this->assertEqualsCanonicalizing(['messages.saved', 'cart.items', 'auth.failed', 'json key', 'nav.home'], $result->keys());
        $this->assertCount(1, $result->dynamic);
        $this->assertTrue($result->dynamic[0]->ambiguous);
        $this->assertSame($result->toArray(), $scanner->scan()->toArray());
    }

    public function test_ignored_keys_and_excluded_paths_are_not_returned(): void
    {
        mkdir($this->directory.'/excluded');
        file_put_contents($this->directory.'/included.php', "<?php __('keep.me'); __('ignore.me');");
        file_put_contents($this->directory.'/excluded/hidden.php', "<?php __('hidden.key');");
        $scanner = new Scanner(new Filesystem, [$this->directory], ['__'], [$this->directory.'/excluded'], ['ignore.me']);

        $this->assertSame(['keep.me'], $scanner->scan(true)->keys());
    }
}
