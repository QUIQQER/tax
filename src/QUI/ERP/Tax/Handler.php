<?php

/**
 * This file contains QUI\ERP\Tax\Handler
 */
namespace QUI\ERP\Tax;

use QUI;
use QUI\Rights\Permission;

/**
 * Class Handler
 * Create and handles tax
 *
 * @package QUI\ERP\Tax
 */
class Handler extends QUI\CRUD\Factory
{
    /**
     * List of tax groups
     *
     * @var array
     */
    protected $taxGroups = array();

    /**
     * List of tax types
     *
     * @var array
     */
    protected $taxTypes = array();

    /**
     * Handler constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->Events->addEvent('onCreateBegin', function () {
            Permission::checkPermission('quiqqer.tax.create');
        });

        // create new translation var for the area
        $this->Events->addEvent('onCreateEnd', function ($NewArea) {

        });
    }

    /**
     * return the area db table name
     *
     * @return string
     */
    public function getDataBaseTableName()
    {
        return QUI::getDBTableName('tax');
    }

    /**
     * Return the name of the child crud class
     *
     * @return string
     */
    public function getChildClass()
    {
        return 'QUI\ERP\Tax\TaxEntry';
    }

    /**
     * Return the crud attributes for the children class
     *
     * @return array
     */
    public function getChildAttributes()
    {
        return array(
            'taxTypeId',
            'taxGroupId',
            'tax',
            'areaId',
            'active',
            'euvat'
        );
    }

    /**
     * Tax groups
     */

    /**
     * Create a new tax group
     *
     * @return TaxGroup
     */
    public function createTaxGroup()
    {
        Permission::checkPermission('quiqqer.tax.create');

        $Tax    = QUI::getPackage('quiqqer/tax');
        $Config = $Tax->getConfig();

        $groups = $Config->getSection('taxgroups');

        if (is_array($groups)) {
            $newId = max(array_keys($groups)) + 1;
        } else {
            $newId = 0;
        }

        $groups[$newId] = 'taxGroup.' . $newId . '.title';

        $Config->setSection('taxgroups', $groups);
        $Config->save();

        return $this->getTaxGroup($newId);
    }

    /**
     * Return all tax groups
     *
     * @return array
     */
    public function getTaxGroups()
    {
        $Config = $this->getConfig();
        $groups = $Config->getSection('taxgroups');
        $result = array();

        foreach ($groups as $key => $var) {
            $result[] = $this->getTaxGroup($key);
        }

        return $result;
    }

    /**
     * Return a tax group
     *
     * @param integer $taxGroupId - Tax Group ID
     * @return TaxGroup
     */
    public function getTaxGroup($taxGroupId)
    {
        if (!isset($this->taxGroups[$taxGroupId])) {
            $this->taxGroups[$taxGroupId] = new TaxGroup($taxGroupId);
        }

        return $this->taxGroups[$taxGroupId];
    }

    /**
     * Delete a tax group
     *
     * @param integer $taxGroupId - Tax Group ID
     */
    public function deleteTaxGroup($taxGroupId)
    {
        if (isset($this->taxGroups[$taxGroupId])) {
            unset($this->taxGroups[$taxGroupId]);
        }

        $Config = $this->getConfig();
        $groups = $Config->getSection('taxgroups');

        if (isset($groups[$taxGroupId])) {
            unset($groups[$taxGroupId]);
        }

        $Config->setSection($groups);
        $Config->save();
    }


    /**
     * Tax types
     */

    /**
     * Create a new tax type
     *
     * @return TaxType
     */
    public function createTaxType()
    {
        Permission::checkPermission('quiqqer.tax.create');

        $Config = $this->getConfig();
        $types  = $Config->getSection('taxtypes');

        if (is_array($types)) {
            $newId = max(array_keys($types)) + 1;
        } else {
            $newId = 0;
        }

        $types[$newId] = 'taxType.' . $newId . '.title';

        $Config->setSection('taxtypes', $types);
        $Config->save();

        return $this->getTaxType($newId);
    }

    /**
     * Return a tax type
     *
     * @param integer $taxTypeId - Tax Type ID
     * @return TaxType
     */
    public function getTaxType($taxTypeId)
    {
        if (!isset($this->taxTypes[$taxTypeId])) {
            $this->taxTypes[$taxTypeId] = new TaxType($taxTypeId);
        }

        return $this->taxTypes[$taxTypeId];
    }

    /**
     * Delete a tax type
     *
     * @param integer $taxTypeId - Tax Type ID
     */
    public function deleteTaxType($taxTypeId)
    {
        if (isset($this->taxTypes[$taxTypeId])) {
            unset($this->taxTypes[$taxTypeId]);
        }

        $Config = $this->getConfig();
        $types  = $Config->getSection('taxtypes');

        if (isset($types[$taxTypeId])) {
            unset($types[$taxTypeId]);
        }

        $Config->setSection($types);
        $Config->save();
    }


    /**
     * Helper
     */

    /**
     * Return tax config
     * @return bool|QUI\Config
     */
    protected function getConfig()
    {
        $Tax = QUI::getPackage('quiqqer/tax');
        return $Tax->getConfig();
    }
}
