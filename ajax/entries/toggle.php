<?php

/**
 * This file contains package_quiqqer_tax_ajax_entries_toggle
 */

/**
 * Toggle the status from a tax entry
 *
 * @param integer $taxId - Tax Entry ID
 */

use QUI\ERP\Tax\TaxEntry;

QUI::$Ajax->registerFunction(
    'package_quiqqer_tax_ajax_entries_toggle',
    function ($taxId) {
        $Handler = new QUI\ERP\Tax\Handler();
        $Tax = $Handler->getChild($taxId);

        /* @var $Tax TaxEntry */
        if ($Tax->isActive()) {
            $Tax->setAttribute('active', 0);
        } else {
            $Tax->setAttribute('active', 1);
        }

        $Tax->update();

        return $Tax->isActive();
    },
    ['taxId'],
    'Permission::checkAdminUser'
);
