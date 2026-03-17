<?php

declare(strict_types=1);

namespace RcsCodes\SEOTools\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * php spark seotools:publish
 *
 * Copies the publishable config and view partials into the host application.
 */
class PublishCommand extends BaseCommand
{
    protected $group       = 'SEOTools';
    protected $name        = 'seotools:publish';
    protected $description = 'Publish the SEOTools config and view partials into your application.';
    protected $usage       = 'seotools:publish [--force]';
    protected $options     = [
        '--force' => 'Overwrite existing files without prompting.',
    ];

    public function run(array $params): void
    {
        $packageRoot = dirname(__DIR__, 2) . '/resources';
        $appRoot     = APPPATH;

        $files = [
            $packageRoot . '/Config/SEOTools.php'       => $appRoot . 'Config/SEOTools.php',
            $packageRoot . '/Views/seotools/meta.php'   => $appRoot . 'Views/seotools/meta.php',
            $packageRoot . '/Views/seotools/schema.php' => $appRoot . 'Views/seotools/schema.php',
        ];

        $force = CLI::getOption('force') || array_key_exists('force', $params);

        foreach ($files as $source => $dest) {
            if (! is_file($source)) {
                CLI::write(' skip   ' . basename($source) . ' (source not found)', 'yellow');
                continue;
            }

            if (is_file($dest) && ! $force) {
                CLI::write(' exists ' . str_replace(APPPATH, 'app/', $dest), 'yellow');
                CLI::write('        Use --force to overwrite.', 'dark_gray');
                continue;
            }

            $destDir = dirname($dest);
            if (! is_dir($destDir)) {
                mkdir($destDir, 0755, true);
            }

            if (copy($source, $dest)) {
                CLI::write(' copied ' . str_replace(APPPATH, 'app/', $dest), 'green');
            } else {
                CLI::write(' error  Failed to copy to ' . $dest, 'red');
            }
        }

        CLI::newLine();
        CLI::write('SEOTools published. Edit app/Config/SEOTools.php to configure defaults.', 'cyan');
    }
}
