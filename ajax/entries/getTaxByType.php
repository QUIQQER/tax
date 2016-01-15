<?php

/**
 * This file contains package_quiqqer_tax_ajax_entries_getTaxByType
 */

/**
 * Return a tax entry
 *
 * @param integer $taxId - Tax Entry ID
 * @return array
 */
QUI::$Ajax->registerFunction(
    'package_quiqqer_tax_ajax_entries_getTaxByType',
    function ($taxTypeId) {
        $Handler = new QUI\ERP\Tax\Handler();
        $Areas   = new QUI\ERP\Areas\Handler();

        $result = $Handler->getChildrenData(array(
            'where' => array(
                'taxTypeId' => $taxTypeId
            )
        ));

        foreach ($result as $key => $entry) {
            try {
                $result[$key]['area'] = $Areas->getChild($entry['areaId'])->getTitle();
            } catch (QUI\Exception $Exception) {
                $result[$key]['area'] = '';
            }
        }

        return $result;
    },
    array('taxTypeId'),
    'Permission::checkAdminUser'
);
