<?php

/**
 * This file contains package_quiqqer_tax_ajax_entries_update
 */

/**
 * Update a tax group
 */
QUI::$Ajax->registerFunction(
    'package_quiqqer_tax_ajax_entries_update',
    function ($taxId, $data) {
        $Handler = new QUI\ERP\Tax\Handler();
        $data    = json_decode($data, true);

        if (!isset($data['taxTypeId'])) {
            throw new QUI\Exception(array(
                'quiqqer/tax',
                'exception.taxtype.not.found'
            ));
        }

        $Tax     = $Handler->getChild($taxId);
        $TaxType = $Handler->getTaxType($data['taxTypeId']);

        $Tax->setAttribute('taxTypeId', $TaxType->getId());
        $Tax->setAttribute('taxGroupId', $TaxType->getGroup()->getId());

        foreach ($data as $key => $value) {
            switch ($key) {
                case 'areaId':
                case 'active':
                case 'euvat':
                case 'vat':
                    $Tax->setAttribute($key, $value);
                    break;
            }
        }

        $Tax->update();
    },
    array('taxId', 'data'),
    'Permission::checkAdminUser'
);
