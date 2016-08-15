<?php

$quiqqerPackageDir = dirname(dirname(__FILE__));
$packageDir        = dirname(dirname($quiqqerPackageDir));

// include quiqqer bootstrap for tests
require $packageDir . '/quiqqer/quiqqer/tests/bootstrap.php';

QUI\Autoloader::$ComposerLoader->add(
    'QUITests\\ERP\\Tax',
    dirname(__FILE__) . '/QUITests/ERP/Tax'
);
