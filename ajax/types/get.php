<?php

/**
 * This file contains package_quiqqer_tax_ajax_types_get
 */

/**
 * Return a tax type
 *
 * @param integer $taxTypeId - Tax Type ID
 * @return array
 */
QUI::$Ajax->registerFunction(
    'package_quiqqer_tax_ajax_types_get',
    function ($taxTypeId) {
        $Handler = new QUI\ERP\Tax\Handler();
        return $Handler->getTaxType($taxTypeId)->toArray();
    },
    ['taxTypeId'],
    'Permission::checkAdminUser'
);
