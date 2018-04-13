<?php

/**
 * This file contains package_quiqqer_tax_ajax_entries_delete
 */

/**
 * Delete a tax group
 *
 * @param integer $taxId - Tax-ID
 */
QUI::$Ajax->registerFunction(
    'package_quiqqer_tax_ajax_entries_delete',
    function ($taxId) {
        $Handler = new QUI\ERP\Tax\Handler();
        $Tax     = $Handler->getChild($taxId);
        $Tax->delete();
    },
    ['taxId'],
    'Permission::checkAdminUser'
);
