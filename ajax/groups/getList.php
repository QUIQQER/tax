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
        $groupIds = json_decode($ids, true);

        if (!is_array($groupIds)) {
            $groupIds = [];
        }

        $groups = $Handler->getTaxGroups($groupIds);
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
