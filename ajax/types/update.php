<?php

/**
 * This file contains package_quiqqer_tax_ajax_types_update
 */

/**
 * Update a tax group
 */
QUI::$Ajax->registerFunction(
    'package_quiqqer_tax_ajax_types_update',
    function ($taxTypeId, $data) {
        $Handler = new QUI\ERP\Tax\Handler();
        $TaxType = $Handler->getTaxType($taxTypeId);
        $data    = json_decode($data, true);

        if (isset($data['group'])) {
            $TaxGroup = $Handler->getTaxGroup($data['group']);
            $TaxGroup->addTaxType($TaxType);
            $TaxGroup->update();
        }
    },
    ['taxTypeId', 'data'],
    'Permission::checkAdminUser'
);
