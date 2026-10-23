<?php

use Symfony\Component\Process\Process;

require __DIR__.'/../vendor/autoload.php';
$roots = ['app', 'bootstrap', 'config', 'database', 'routes', 'tests', 'scripts'];
$failed = false;
foreach ($roots as $root) {
    foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator(__DIR__.'/../'.$root, FilesystemIterator::SKIP_DOTS)) as $file) {
        if ($file->getExtension() !== 'php' || str_contains($file->getPathname(), DIRECTORY_SEPARATOR.'cache'.DIRECTORY_SEPARATOR)) {
            continue;
        }
        $process = new Process([PHP_BINARY, '-l', $file->getPathname()]);
        $process->run();
        if (! $process->isSuccessful()) {
            echo $process->getErrorOutput().$process->getOutput();
            $failed = true;
        }
    }
}
echo $failed ? "PHP syntax failed\n" : "PHP syntax passed\n";
exit($failed ? 1 : 0);
