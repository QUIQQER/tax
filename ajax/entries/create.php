<?php

/**
 * This file contains package_quiqqer_tax_ajax_entries_create
 */

/**
 * Create a tax entry
 *
 * @param integer $taxTypeId - Type-ID
 * @param integer $areaId - Area-ID
 * @return integer
 */
QUI::$Ajax->registerFunction(
    'package_quiqqer_tax_ajax_entries_create',
    function ($taxTypeId, $areaId) {
        $Handler = new QUI\ERP\Tax\Handler();
        $TaxType = $Handler->getTaxType($taxTypeId);

        $Tax    = $Handler->createChild();
        $areaId = (int)$areaId;

        $Tax->setAttribute('areaId', $areaId);
        $Tax->setAttribute('taxTypeId', $TaxType->getId());
        $Tax->setAttribute('taxGroupId', $TaxType->getGroup()->getId());
        $Tax->update();

        return $Tax->getId();
    },
    ['taxTypeId', 'areaId'],
    'Permission::checkAdminUser'
);
