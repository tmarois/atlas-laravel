<?php

namespace Atlas\Laravel\Tests\Console;

use Illuminate\Support\Facades\File;
use Orchestra\Testbench\TestCase;

class ExportEnumsCommandTest extends TestCase
{
    protected string $enumDir;

    protected string $outputDir;

    protected function getPackageProviders($app): array
    {
        return [\Atlas\Laravel\AtlasServiceProvider::class];
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->enumDir = sys_get_temp_dir().'/atlas-enums';
        $this->outputDir = sys_get_temp_dir().'/atlas-enums-output';

        File::deleteDirectory($this->enumDir);
        File::deleteDirectory($this->outputDir);

        File::ensureDirectoryExists($this->enumDir.'/Billing');

        File::put($this->enumDir.'/Status.php', <<<'PHP'
<?php

namespace App\Enums;

enum Status: string
{
    case Draft = 'draft';
    case Published = 'published';
}
PHP);

        File::put($this->enumDir.'/Billing/InvoiceStatus.php', <<<'PHP'
<?php

namespace App\Enums\Billing;

enum InvoiceStatus: int
{
    case Paid = 1;
    case Unpaid = 0;
}
PHP);

        config()->set('atlas_enums.enum_paths', [$this->enumDir]);
        config()->set('atlas_enums.output_path', $this->outputDir);
        config()->set('atlas_enums.format', 'ts');
        config()->set('atlas_enums.banner', '// test');
    }

    public function test_exports_enums(): void
    {
        $this->artisan('atlas:export-enums')->assertExitCode(0);

        $statusFile = $this->outputDir.'/Status.ts';
        $invoiceFile = $this->outputDir.'/Billing/InvoiceStatus.ts';
        $indexFile = $this->outputDir.'/index.ts';

        $this->assertFileExists($statusFile);
        $this->assertFileExists($invoiceFile);
        $this->assertFileExists($indexFile);

        $this->assertStringContainsString('export enum Status', File::get($statusFile));
        $this->assertStringContainsString('export enum InvoiceStatus', File::get($invoiceFile));

        $indexContent = File::get($indexFile);
        $expectedIndex = implode(PHP_EOL, [
            '// test',
            "export { InvoiceStatus } from './Billing/InvoiceStatus';",
            "export { Status } from './Status';",
            '',
        ]);
        $this->assertSame($expectedIndex, $indexContent);
    }

    public function test_handles_duplicate_enum_names(): void
    {
        File::ensureDirectoryExists($this->enumDir.'/Action/Worker');

        File::put($this->enumDir.'/Action/ActionStatus.php', <<<'PHP'
<?php

namespace App\Enums\Action;

enum ActionStatus: string
{
    case Pending = 'pending';
}
PHP);

        File::put($this->enumDir.'/Action/Worker/ActionStatus.php', <<<'PHP'
<?php

namespace App\Enums\Action\Worker;

enum ActionStatus: string
{
    case Working = 'working';
}
PHP);

        $this->artisan('atlas:export-enums')->assertExitCode(0);

        $indexFile = $this->outputDir.'/index.ts';
        $this->assertFileExists($indexFile);

        $indexContent = File::get($indexFile);

        $this->assertStringContainsString("export { ActionStatus } from './Action/ActionStatus';", $indexContent);
        $this->assertStringContainsString("export { ActionStatus as ActionWorkerStatus } from './Action/Worker/ActionStatus';", $indexContent);
    }

    public function test_clears_output_directory_before_export(): void
    {
        $this->artisan('atlas:export-enums')->assertExitCode(0);

        $invoiceFile = $this->outputDir.'/Billing/InvoiceStatus.ts';
        $this->assertFileExists($invoiceFile);

        File::delete($this->enumDir.'/Billing/InvoiceStatus.php');

        $this->artisan('atlas:export-enums')->assertExitCode(0);

        $this->assertFileDoesNotExist($invoiceFile);
    }

    public function test_ignores_non_enum_files(): void
    {
        File::put($this->enumDir.'/note.txt', 'just a file');
        File::put($this->enumDir.'/Helper.php', <<<'PHP'
<?php

namespace App\Enums;

class Helper
{
}
PHP);

        $this->artisan('atlas:export-enums')->assertExitCode(0);

        $this->assertFileDoesNotExist($this->outputDir.'/note.ts');
        $this->assertFileDoesNotExist($this->outputDir.'/Helper.ts');

        $indexFile = $this->outputDir.'/index.ts';
        $this->assertFileExists($indexFile);

        $indexContent = File::get($indexFile);
        $this->assertStringNotContainsString('note', $indexContent);
        $this->assertStringNotContainsString('Helper', $indexContent);
    }
}
