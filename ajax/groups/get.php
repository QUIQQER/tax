<?php

/**
 * This file contains package_quiqqer_tax_ajax_groups_get
 */

/**
 * Return a tax group
 *
 * @param integer $taxGroupId - Tax Group ID
 * @return array
 */
QUI::$Ajax->registerFunction(
    'package_quiqqer_tax_ajax_groups_get',
    function ($taxGroupId) {
        $Handler = new QUI\ERP\Tax\Handler();
        return $Handler->getTaxGroup($taxGroupId)->toArray();
    },
    array('taxGroupId'),
    'Permission::checkAdminUser'
);
