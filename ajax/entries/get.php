<?php

/**
 * This file contains package_quiqqer_tax_ajax_entries_get
 */

/**
 * Return a tax entry
 *
 * @param integer $taxId - Tax Entry ID
 * @return array
 */
QUI::$Ajax->registerFunction(
    'package_quiqqer_tax_ajax_entries_get',
    function ($taxId) {
        $Handler = new QUI\ERP\Tax\Handler();
        return $Handler->getChild($taxId)->getAttributes();
    },
    array('taxId'),
    'Permission::checkAdminUser'
);
