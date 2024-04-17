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
    function ($ids) {
        $Handler = new QUI\ERP\Tax\Handler();
        $groups = $Handler->getTaxGroups(json_decode($ids, true));
        $result = [];

        foreach ($groups as $TaxGroup) {
            $attributes = $TaxGroup->toArray();
            $taxTypes = $TaxGroup->getTaxTypes();
            $taxTypeNames = [];

            foreach ($taxTypes as $TaxType) {
                $taxTypeNames[] = $TaxType->getTitle();
            }

            $attributes['taxTypeNames'] = implode(', ', $taxTypeNames);

            $result[] = $attributes;
        }

        return $result;
    },
    ['ids'],
    'Permission::checkAdminUser'
);
