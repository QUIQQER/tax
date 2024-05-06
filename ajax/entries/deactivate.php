<?php

/**
 * This file contains package_quiqqer_tax_ajax_entries_deactivate
 */

/**
 * Deactivate a tax entry
 *
 * @param integer $taxId - Tax Entry ID
 */

use QUI\ERP\Tax\TaxEntry;

QUI::$Ajax->registerFunction(
    'package_quiqqer_tax_ajax_entries_deactivate',
    function ($taxId) {
        $Handler = new QUI\ERP\Tax\Handler();
        $Tax = $Handler->getChild($taxId);

        /* @var $Tax TaxEntry */
        $Tax->setAttribute('active', 0);
        $Tax->update();

        return $Tax->isActive();
    },
    ['taxId'],
    'Permission::checkAdminUser'
);
