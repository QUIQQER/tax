<?php

/**
 * This file contains package_quiqqer_tax_ajax_groups_getTaxTypes
 */

/**
 * Return all tax types from a tax group
 *
 * @return array
 */
QUI::$Ajax->registerFunction(
    'package_quiqqer_tax_ajax_groups_getTaxTypes',
    function ($taxGroupdId) {
        $Handler = new QUI\ERP\Tax\Handler();
        $Group   = $Handler->getTaxGroup($taxGroupdId);
        $types   = $Group->getTaxTypes();
        $result  = array();

        /* @var $TaxType \QUI\ERP\Tax\TaxType */
        foreach ($types as $TaxType) {
            $result[] = $TaxType->toArray();
        }

        return $result;
    },
    array('taxGroupdId'),
    'Permission::checkAdminUser'
);
