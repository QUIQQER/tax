<?php

/**
 * This file contains package_quiqqer_tax_ajax_groups_delete
 */

/**
 * Delete a tax group
 */
QUI::$Ajax->registerFunction(
    'package_quiqqer_tax_ajax_entries_delete',
    function ($taxGroupId) {
        $Handler = new QUI\ERP\Tax\Handler();
//        $Handler->delete($taxGroupId);
    },
    array('taxGroupId'),
    'Permission::checkAdminUser'
);
