<?php

namespace JupyterPhpKernel;

require __DIR__ . '/../vendor/autoload.php';

$connection_details = (new ConnectionDetails())->read();
$kernel = new Kernel($connection_details);

$kernel->run();
