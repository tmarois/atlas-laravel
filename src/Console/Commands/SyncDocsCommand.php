<?php

declare(strict_types=1);

namespace Atlas\Laravel\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class SyncDocsCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'atlas:sync-docs';

    /**
     * The console command description.
     */
    protected $description = 'Sync documentation from Atlas repositories';

    public function handle(): int
    {
        $repos = config('atlas_docs.repos', []);
        $defaultDelete = config('atlas_docs.delete', true);
        $deletedOutputs = [];

        foreach ($repos as $repoConfig) {
            $repo = $repoConfig['repo'] ?? null;
            if (! $repo) {
                $this->warn('Repository not configured.');

                continue;
            }

            $paths = $repoConfig['paths'] ?? [];
            if ($paths === []) {
                $this->warn("No paths configured for {$repo}.");

                continue;
            }

            $ignore = $repoConfig['ignore'] ?? [];
            $includeAgents = $repoConfig['include_agents'] ?? true;
            $delete = $repoConfig['delete'] ?? $defaultDelete;

            $treeResponse = Http::withHeaders(['User-Agent' => 'atlas-docs-sync'])->get(
                "https://api.github.com/repos/{$repo}/git/trees/HEAD",
                ['recursive' => 1]
            );

            if ($treeResponse->failed()) {
                $this->error("Failed to fetch file list for {$repo}");

                continue;
            }

            $tree = $treeResponse->json('tree', []);

            $agentsContent = null;
            if ($includeAgents && collect($tree)->contains(fn ($item) => ($item['path'] ?? null) === 'AGENTS.md')) {
                $agentsContent = Http::withHeaders(['User-Agent' => 'atlas-docs-sync'])
                    ->get("https://raw.githubusercontent.com/{$repo}/HEAD/AGENTS.md")
                    ->body();
            }

            foreach ($paths as $pathConfig) {
                $source = trim($pathConfig['path'] ?? '', '/');
                $output = $pathConfig['output'] ?? null;

                if ($source === '' || $output === null) {
                    $this->warn("Invalid path configuration for {$repo}.");

                    continue;
                }

                $outputBase = base_path($output);

                if ($delete && ! in_array($outputBase, $deletedOutputs, true)) {
                    File::deleteDirectory($outputBase);
                    File::delete($outputBase);
                    $deletedOutputs[] = $outputBase;
                }

                File::ensureDirectoryExists($outputBase);

                foreach ($tree as $item) {
                    if (($item['type'] ?? '') !== 'blob') {
                        continue;
                    }

                    $itemPath = $item['path'];

                    if ($itemPath === $source) {
                        $relative = basename($source);

                        if ($this->shouldIgnore($relative, $ignore)) {
                            continue;
                        }

                        $url = "https://raw.githubusercontent.com/{$repo}/HEAD/{$itemPath}";
                        $contents = Http::withHeaders(['User-Agent' => 'atlas-docs-sync'])->get($url)->body();

                        $destination = $outputBase.'/'.$relative;
                        File::ensureDirectoryExists(dirname($destination));
                        File::put($destination, $contents);
                    } elseif (str_starts_with($itemPath, $source.'/')) {
                        $relative = substr($itemPath, strlen($source) + 1);

                        if ($this->shouldIgnore($relative, $ignore)) {
                            continue;
                        }

                        $url = "https://raw.githubusercontent.com/{$repo}/HEAD/{$itemPath}";
                        $contents = Http::withHeaders(['User-Agent' => 'atlas-docs-sync'])->get($url)->body();

                        $destination = $outputBase.'/'.basename($source).'/'.$relative;
                        File::ensureDirectoryExists(dirname($destination));
                        File::put($destination, $contents);
                    }
                }

                if ($agentsContent !== null) {
                    File::put($outputBase.'/AGENTS.md', $agentsContent);
                }
            }

            $this->info("Synced docs for {$repo}");
        }

        return self::SUCCESS;
    }

    protected function shouldIgnore(string $path, array $patterns): bool
    {
        foreach ($patterns as $pattern) {
            if (Str::is($pattern, $path)) {
                return true;
            }
        }

        return false;
    }
}
