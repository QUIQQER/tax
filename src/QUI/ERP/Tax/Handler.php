<?php

/**
 * This file contains QUI\ERP\Tax\Handler
 */
namespace QUI\ERP\Tax;

use QUI;
use QUI\Permissions\Permission;

/**
 * Class Handler
 * Create and handles tax
 *
 * @package QUI\ERP\Tax
 */
class Handler extends QUI\CRUD\Factory
{
    /**
     * Handler instance
     *
     * @var null
     */
    protected static $Instance = null;

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

        // create new translation var for the tax
        $this->Events->addEvent('onCreateEnd', function ($NewArea) {
        });
    }

    /**
     * Return the global tax Handler
     *
     * @return Handler
     */
    public static function getInstance()
    {
        if (self::$Instance === null) {
            self::$Instance = new self();
        }

        return self::$Instance;
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
            'vat',
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

        $Config = $this->getConfig();
        $groups = $Config->getSection('taxgroups');

        if (is_array($groups) && count($groups)) {
            $newId = max(array_keys($groups)) + 1;
        } else {
            $groups = array();
            $newId  = 0;
        }

        $groups[$newId] = '';

        $Config->setSection('taxgroups', $groups);
        $Config->save();

        $current = QUI::getLocale()->getCurrent();

        // create locale
        try {
            QUI\Translator::addUserVar(
                'quiqqer/tax',
                'taxGroup.' . $newId . '.title',
                array(
                    $current   => '',
                    'datatype' => 'php,js'
                )
            );
        } catch (QUI\Exception $Exception) {
            QUI::getMessagesHandler()->addAttention(
                $Exception->getMessage()
            );
        }

        return $this->getTaxGroup($newId);
    }

    /**
     * Return all tax groups
     *
     * @param array|boolean $ids - optional, list of wanted ids, default = all
     * @return array
     */
    public function getTaxGroups($ids = false)
    {
        $Config = $this->getConfig();
        $groups = $Config->getSection('taxgroups');
        $result = array();

        if (!is_array($ids)) {
            $ids = false;
        }

        if (!$groups || !is_array($groups)) {
            $groups = array();
        }

        foreach ($groups as $key => $var) {
            if ($ids && !in_array($key, $ids)) {
                continue;
            }

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

        $Config->setSection('taxgroups', $groups);
        $Config->save();

        // translation is no longer used
        QUI\Translator::delete(
            'quiqqer/tax',
            'taxGroup.' . $taxGroupId . '.title'
        );


        // delete all tax entries
        QUI::getDataBase()->delete($this->getDataBaseTableName(), array(
            'taxGroupId' => $taxGroupId
        ));
    }

    /**
     * Update a group
     *
     * @param integer $taxGroupId - Group-ID
     * @param array $types - array of types eq: - [typeId, typeId, typeId]
     */
    public function updateTaxGroup($taxGroupId, $types)
    {
        $TaxGroup = $this->getTaxGroup($taxGroupId);
        $TaxGroup->setTaxTypes($types);
        $TaxGroup->update();
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

        if (is_array($types) && count($types)) {
            $newId = max(array_keys($types)) + 1;
        } else {
            $types = array();
            $newId = 0;
        }

        $types[$newId] = 'taxType.' . $newId . '.title';

        $Config->setSection('taxtypes', $types);
        $Config->save();

        $current = QUI::getLocale()->getCurrent();

        // create locale
        try {
            QUI\Translator::addUserVar('quiqqer/tax', $types[$newId], array(
                $current   => '',
                'datatype' => 'php,js'
            ));
        } catch (QUI\Exception $Exception) {
            QUI::getMessagesHandler()->addAttention(
                $Exception->getMessage()
            );
        }

        return $this->getTaxType($newId);
    }

    /**
     * Return all tax types
     *
     * @param array|boolean $ids - optional, list of wanted ids, default = all
     * @return array
     */
    public function getTaxTypes($ids = false)
    {
        $Config = $this->getConfig();
        $types  = $Config->getSection('taxtypes');
        $result = array();

        if (!is_array($ids)) {
            $ids = false;
        }

        if (!$types) {
            $types = array();
        }

        foreach ($types as $key => $var) {
            if ($ids && !in_array($key, $ids)) {
                continue;
            }

            $result[] = $this->getTaxType($key);
        }

        return $result;
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

        $Config->setSection('taxtypes', $types);
        $Config->save();

        // translation is no longer used
        QUI\Translator::delete(
            'quiqqer/tax',
            'taxType.' . $taxTypeId . '.title'
        );

        // delete all tax entries
        QUI::getDataBase()->delete($this->getDataBaseTableName(), array(
            'taxTypeId' => $taxTypeId
        ));
    }


    /**
     * Helper
     */

    /**
     * Return tax config
     *
     * @return QUI\Config
     */
    public function getConfig()
    {
        return QUI::getPackage('quiqqer/tax')->getConfig();
    }
}
