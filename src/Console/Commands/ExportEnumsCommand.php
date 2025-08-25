<?php

namespace Atlas\Laravel\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use ReflectionEnum;

class ExportEnumsCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'atlas:export-enums';

    /**
     * The console command description.
     */
    protected $description = 'Export PHP enums to frontend';

    public function handle(): int
    {
        $config = config('atlas_enums');

        $enumPaths = $config['enum_paths'] ?? [];
        $outputPath = $config['output_path'] ?? resource_path('js/enums');
        $format = $config['format'] ?? 'ts';
        $banner = $config['banner'] ?? '';

        File::deleteDirectory($outputPath);
        File::ensureDirectoryExists($outputPath);

        $exported = [];

        foreach ($enumPaths as $path) {
            if (! is_dir($path)) {
                continue;
            }

            $files = File::allFiles($path);

            foreach ($files as $file) {
                if ($file->getExtension() !== 'php') {
                    continue;
                }

                $relativePath = trim(str_replace($path, '', $file->getPathname()), DIRECTORY_SEPARATOR);
                $relativeDir = dirname($relativePath);
                $enumName = pathinfo($file->getFilename(), PATHINFO_FILENAME);

                $namespace = $this->getNamespace($file->getPathname());
                $class = $namespace ? $namespace.'\\'.$enumName : $enumName;

                if (! class_exists($class)) {
                    require_once $file->getPathname();
                }

                if (! enum_exists($class)) {
                    continue;
                }

                $reflection = new ReflectionEnum($class);
                $cases = $reflection->getCases();

                $content = $banner ? $banner."\n" : '';

                if ($format === 'ts') {
                    $content .= "export enum {$enumName} {\n";
                    foreach ($cases as $case) {
                        $value = $reflection->isBacked()
                            ? var_export($case->getBackingValue(), true)
                            : var_export($case->getName(), true);
                        $content .= "    {$case->getName()} = {$value},\n";
                    }
                    $content .= "}\n";
                } else {
                    $content .= "export const {$enumName} = {\n";
                    foreach ($cases as $case) {
                        $value = $reflection->isBacked()
                            ? var_export($case->getBackingValue(), true)
                            : var_export($case->getName(), true);
                        $content .= "    {$case->getName()}: {$value},\n";
                    }
                    $content .= "};\n";
                }

                $targetDir = $outputPath.($relativeDir !== '.' ? DIRECTORY_SEPARATOR.$relativeDir : '');
                File::ensureDirectoryExists($targetDir);
                $targetFile = $targetDir.DIRECTORY_SEPARATOR.$enumName.'.'.$format;
                File::put($targetFile, $content);

                $exported[] = [
                    'name' => $enumName,
                    'path' => str_replace('\\', '/', ($relativeDir !== '.' ? $relativeDir.'/' : '').$enumName),
                ];
            }
        }

        $nameCounts = [];
        foreach ($exported as $enum) {
            $nameCounts[$enum['name']] = ($nameCounts[$enum['name']] ?? 0) + 1;
        }

        foreach ($exported as &$enum) {
            if (($nameCounts[$enum['name']] ?? 0) > 1) {
                $segments = explode('/', $enum['path']);
                $name = array_pop($segments);
                $suffix = $name;
                $aliasSegments = [];

                foreach ($segments as $segment) {
                    $clean = preg_replace('/[^A-Za-z0-9]/', '', $segment);
                    if (str_starts_with($suffix, $clean)) {
                        $suffix = substr($suffix, strlen($clean));
                    }
                    $aliasSegments[] = $clean;
                }

                $alias = implode('', $aliasSegments).$suffix;

                if ($alias !== $enum['name']) {
                    $enum['alias'] = $alias;
                }
            }
        }
        unset($enum);

        usort($exported, fn (array $a, array $b) => strcmp($a['path'], $b['path']));

        File::ensureDirectoryExists($outputPath);
        $indexFile = $outputPath.DIRECTORY_SEPARATOR.'index.'.$format;
        $index = $banner ? $banner."\n" : '';
        foreach ($exported as $enum) {
            $alias = $enum['alias'] ?? $enum['name'];
            if ($alias === $enum['name']) {
                $index .= "export { {$enum['name']} } from './{$enum['path']}';\n";
            } else {
                $index .= "export { {$enum['name']} as {$alias} } from './{$enum['path']}';\n";
            }
        }
        File::put($indexFile, $index);

        $this->info('Enums exported: '.count($exported));

        return self::SUCCESS;
    }

    protected function getNamespace(string $path): ?string
    {
        $content = File::get($path);
        if (preg_match('/^namespace\s+([^;]+);/m', $content, $matches)) {
            return trim($matches[1]);
        }

        return null;
    }
}
