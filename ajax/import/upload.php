<?php

/**
 * This file contains package_quiqqer_tax_ajax_import_upload
 */

/**
 * Returns the available imports
 *
 * @param \QUI\QDOM $File - Area-ID
 *
 * @return array
 */
QUI::$Ajax->registerFunction(
    'package_quiqqer_tax_ajax_import_upload',
    function ($File) {
        /* @var $File \QUI\QDOM */
        QUI\ERP\Tax\Import::import(
            $File->getAttribute('filepath')
        );
    },
    ['File'],
    'Permission::checkAdminUser'
);
