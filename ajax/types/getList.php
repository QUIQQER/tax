<?php

/**
 * This file contains package_quiqqer_tax_ajax_types_getList
 */

/**
 * Return all tax types
 *
 * @return array
 */
QUI::$Ajax->registerFunction(
    'package_quiqqer_tax_ajax_types_getList',
    function ($ids) {
        $Handler = new QUI\ERP\Tax\Handler();
        $groups  = $Handler->getTaxTypes(\json_decode($ids, true));
        $result  = [];

        /* @var $TaxType \QUI\ERP\Tax\TaxType */
        foreach ($groups as $TaxType) {
            $result[] = $TaxType->toArray();
        }

        return $result;
    },
    ['ids'],
    'Permission::checkAdminUser'
);
