<?php

declare(strict_types=1);

namespace Atlas\Laravel\Tests\Console;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Orchestra\Testbench\TestCase;

class SyncDocsCommandTest extends TestCase
{
    protected function getPackageProviders($app): array
    {
        return [\Atlas\Laravel\AtlasServiceProvider::class];
    }

    protected function setUp(): void
    {
        parent::setUp();

        File::deleteDirectory(base_path('docs/ui'));
        File::deleteDirectory(base_path('docs/atlas'));
        File::ensureDirectoryExists(base_path('docs/ui'));
        File::ensureDirectoryExists(base_path('docs/atlas'));
    }

    public function test_syncs_selected_paths(): void
    {
        Http::fake([
            'https://api.github.com/repos/foo/bar/git/trees/HEAD*' => Http::response([
                'tree' => [
                    ['path' => 'docs/components/button.md', 'type' => 'blob'],
                    ['path' => 'docs/components/ignore.md', 'type' => 'blob'],
                    ['path' => 'docs/README.md', 'type' => 'blob'],
                ],
            ], 200),
            'https://raw.githubusercontent.com/foo/bar/HEAD/docs/components/button.md' => Http::response('button', 200),
            'https://raw.githubusercontent.com/foo/bar/HEAD/docs/components/ignore.md' => Http::response('ignore', 200),
            'https://raw.githubusercontent.com/foo/bar/HEAD/docs/README.md' => Http::response('readme', 200),
            'https://api.github.com/repos/foo/baz/git/trees/HEAD*' => Http::response([
                'tree' => [
                    ['path' => 'docs/file.md', 'type' => 'blob'],
                ],
            ], 200),
            'https://raw.githubusercontent.com/foo/baz/HEAD/docs/file.md' => Http::response('file', 200),
        ]);

        config(['atlas_docs.repos' => [
            [
                'repo' => 'foo/bar',
                'ignore' => ['ignore.md'],
                'paths' => [
                    ['path' => 'docs/components', 'output' => 'docs/ui'],
                    ['path' => 'docs/README.md', 'output' => 'docs/ui'],
                ],
            ],
            [
                'repo' => 'foo/baz',
                'paths' => [
                    ['path' => 'docs', 'output' => 'docs/atlas/baz'],
                ],
            ],
        ]]);

        $this->artisan('atlas:sync-docs')->assertExitCode(0);

        $this->assertFileExists(base_path('docs/ui/components/button.md'));
        $this->assertFileDoesNotExist(base_path('docs/ui/components/ignore.md'));
        $this->assertFileExists(base_path('docs/ui/README.md'));

        $this->assertFileExists(base_path('docs/atlas/baz/docs/file.md'));
    }

    public function test_deletes_existing_output_by_default(): void
    {
        Http::fake([
            'https://api.github.com/repos/foo/bar/git/trees/HEAD*' => Http::response([
                'tree' => [
                    ['path' => 'docs/README.md', 'type' => 'blob'],
                ],
            ], 200),
            'https://raw.githubusercontent.com/foo/bar/HEAD/docs/README.md' => Http::response('readme', 200),
        ]);

        File::put(base_path('docs/ui/old.md'), 'old');

        config(['atlas_docs.repos' => [
            [
                'repo' => 'foo/bar',
                'paths' => [
                    ['path' => 'docs/README.md', 'output' => 'docs/ui'],
                ],
            ],
        ]]);

        $this->artisan('atlas:sync-docs')->assertExitCode(0);

        $this->assertFileDoesNotExist(base_path('docs/ui/old.md'));
        $this->assertFileExists(base_path('docs/ui/README.md'));
    }

    public function test_can_disable_delete_in_config(): void
    {
        Http::fake([
            'https://api.github.com/repos/foo/bar/git/trees/HEAD*' => Http::response([
                'tree' => [
                    ['path' => 'docs/README.md', 'type' => 'blob'],
                ],
            ], 200),
            'https://raw.githubusercontent.com/foo/bar/HEAD/docs/README.md' => Http::response('readme', 200),
        ]);

        File::put(base_path('docs/ui/old.md'), 'old');

        config(['atlas_docs.repos' => [
            [
                'repo' => 'foo/bar',
                'delete' => false,
                'paths' => [
                    ['path' => 'docs/README.md', 'output' => 'docs/ui'],
                ],
            ],
        ]]);

        $this->artisan('atlas:sync-docs')->assertExitCode(0);

        $this->assertFileExists(base_path('docs/ui/old.md'));
        $this->assertFileExists(base_path('docs/ui/README.md'));
    }

    public function test_can_disable_delete_globally(): void
    {
        Http::fake([
            'https://api.github.com/repos/foo/bar/git/trees/HEAD*' => Http::response([
                'tree' => [
                    ['path' => 'docs/README.md', 'type' => 'blob'],
                ],
            ], 200),
            'https://raw.githubusercontent.com/foo/bar/HEAD/docs/README.md' => Http::response('readme', 200),
        ]);

        File::put(base_path('docs/ui/old.md'), 'old');

        config([
            'atlas_docs.delete' => false,
            'atlas_docs.repos' => [
                [
                    'repo' => 'foo/bar',
                    'paths' => [
                        ['path' => 'docs/README.md', 'output' => 'docs/ui'],
                    ],
                ],
            ],
        ]);

        $this->artisan('atlas:sync-docs')->assertExitCode(0);

        $this->assertFileExists(base_path('docs/ui/old.md'));
        $this->assertFileExists(base_path('docs/ui/README.md'));
    }

    public function test_repo_can_enable_delete_when_global_disabled(): void
    {
        Http::fake([
            'https://api.github.com/repos/foo/bar/git/trees/HEAD*' => Http::response([
                'tree' => [
                    ['path' => 'docs/README.md', 'type' => 'blob'],
                ],
            ], 200),
            'https://raw.githubusercontent.com/foo/bar/HEAD/docs/README.md' => Http::response('readme', 200),
        ]);

        File::put(base_path('docs/ui/old.md'), 'old');

        config([
            'atlas_docs.delete' => false,
            'atlas_docs.repos' => [
                [
                    'repo' => 'foo/bar',
                    'delete' => true,
                    'paths' => [
                        ['path' => 'docs/README.md', 'output' => 'docs/ui'],
                    ],
                ],
            ],
        ]);

        $this->artisan('atlas:sync-docs')->assertExitCode(0);

        $this->assertFileDoesNotExist(base_path('docs/ui/old.md'));
        $this->assertFileExists(base_path('docs/ui/README.md'));
    }
}
