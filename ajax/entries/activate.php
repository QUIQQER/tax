<?php

/**
 * This file contains package_quiqqer_tax_ajax_entries_activate
 */

/**
 * Activate a tax entry
 *
 * @param integer $taxId - Tax Entry ID
 */
QUI::$Ajax->registerFunction(
    'package_quiqqer_tax_ajax_entries_activate',
    function ($taxId) {
        $Handler = new QUI\ERP\Tax\Handler();
        $Tax     = $Handler->getChild($taxId);

        /* @var $Tax \QUI\ERP\Tax\TaxEntry */
        $Tax->setAttribute('active', 1);
        $Tax->update();

        return $Tax->isActive();
    },
    ['taxId'],
    'Permission::checkAdminUser'
);
