<?php

/**
 * This file contains package_quiqqer_tax_ajax_groups_getList
 */

/**
 * Return all tax groups
 *
 * @return array
 */
QUI::$Ajax->registerFunction(
    'package_quiqqer_tax_ajax_groups_getList',
    function () {
        $Handler = new QUI\ERP\Tax\Handler();
        $groups  = $Handler->getTaxGroups();
        $result  = array();

        foreach ($groups as $TaxGroup) {
            $result[] = $TaxGroup->toArray();
        }

        return $result;
    },
    array(),
    'Permission::checkAdminUser'
);
