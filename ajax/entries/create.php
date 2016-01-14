<?php

/**
 * This file contains package_quiqqer_tax_ajax_entries_create
 */

/**
 * Create a tax entry
 *
 * @return integer
 */
QUI::$Ajax->registerFunction(
    'package_quiqqer_tax_ajax_entries_create',
    function ($taxTypeId) {
        $Handler = new QUI\ERP\Tax\Handler();
        $TaxType = $Handler->getTaxType($taxTypeId);

        $Tax = $Handler->createChild();

        $Tax->setAttribute('taxTypeId', $TaxType->getId());
        $Tax->setAttribute('taxGroupId', $TaxType->getGroup()->getId());
        $Tax->update();

        return $Tax->getId();
    },
    array('taxTypeId'),
    'Permission::checkAdminUser'
);
