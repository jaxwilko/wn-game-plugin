#!/usr/bin/env php
<?php

// Load autoloader
require_once __DIR__ . '/../../../../../bootstrap/autoload.php';

// Get app
$app = require __DIR__ . '/../../../../../bootstrap/app.php';

// Trigger the kernel
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->handle(new \Symfony\Component\Console\Input\ArrayInput(['game:noop']));
