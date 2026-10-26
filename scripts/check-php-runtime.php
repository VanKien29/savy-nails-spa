<?php

use App\Support\PhpRuntime;

require __DIR__.'/../app/Support/PhpRuntime.php';

exit(PhpRuntime::isSupported() ? 0 : 1);
