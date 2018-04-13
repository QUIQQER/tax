<?php

/**
 * This file contains package_quiqqer_tax_ajax_import_available
 */

/**
 * Returns the available imports
 *
 * @return array
 */
QUI::$Ajax->registerFunction(
    'package_quiqqer_tax_ajax_import_available',
    function () {
        return QUI\ERP\Tax\Import::getAvailableImports();
    },
    [],
    'Permission::checkAdminUser'
);
