<?php

/**
 * This file contains package_quiqqer_tax_ajax_types_delete
 */

/**
 * Delete a tax group
 */
QUI::$Ajax->registerFunction(
    'package_quiqqer_tax_ajax_types_delete',
    function ($taxTypeId) {
        $Handler = new QUI\ERP\Tax\Handler();
        $Handler->deleteTaxType($taxTypeId);
    },
    ['taxTypeId'],
    'Permission::checkAdminUser'
);
