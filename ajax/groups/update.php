<?php

/**
 * This file contains package_quiqqer_tax_ajax_groups_update
 */

/**
 * Update a tax group
 */
QUI::$Ajax->registerFunction(
    'package_quiqqer_tax_ajax_groups_update',
    function ($taxGroupId, $data) {
        $Handler = new QUI\ERP\Tax\Handler();
        $TaxGroup = $Handler->getTaxGroup($taxGroupId);

        $TaxGroup->setTaxTypes(json_decode($data, true));
        $TaxGroup->update();
    },
    ['taxGroupId', 'data'],
    'Permission::checkAdminUser'
);
