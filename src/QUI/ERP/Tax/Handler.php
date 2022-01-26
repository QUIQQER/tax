<?php

/**
 * This file contains QUI\ERP\Tax\Handler
 */

namespace QUI\ERP\Tax;

use QUI;
use QUI\Permissions\Permission;

use function array_keys;
use function count;
use function in_array;
use function is_array;
use function max;

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
     * @var Handler|null
     */
    protected static ?Handler $Instance = null;

    /**
     * List of tax groups
     *
     * @var array
     */
    protected array $taxGroups = [];

    /**
     * List of tax types
     *
     * @var array
     */
    protected array $taxTypes = [];

    /**
     * Handler constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->Events->addEvent('onCreateBegin', function (&$childData) {
            Permission::checkPermission('quiqqer.tax.create');

            if (empty($childData['taxGroupId'])) {
                $childData['taxGroupId'] = 0;
            }

            if (empty($childData['taxGroupId'])) {
                $childData['vat'] = 0;
            }

            if (empty($childData['areaId'])) {
                $childData['areaId'] = 0;
            }

            if (empty($childData['active'])) {
                $childData['active'] = 0;
            }

            if (empty($childData['euvat'])) {
                $childData['euvat'] = 0;
            }
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
    public static function getInstance(): Handler
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
    public function getDataBaseTableName(): string
    {
        return QUI::getDBTableName('tax');
    }

    /**
     * Return the name of the child crud class
     *
     * @return string
     */
    public function getChildClass(): string
    {
        return 'QUI\ERP\Tax\TaxEntry';
    }

    /**
     * Return the crud attributes for the children class
     *
     * @return array
     */
    public function getChildAttributes(): array
    {
        return [
            'taxTypeId',
            'taxGroupId',
            'vat',
            'areaId',
            'active',
            'euvat'
        ];
    }


    /**
     * Tax groups
     */

    /**
     * Create a new tax group
     *
     * @return TaxGroup
     *
     * @throws QUI\Exception
     */
    public function createTaxGroup(): TaxGroup
    {
        Permission::checkPermission('quiqqer.tax.create');

        $Config = $this->getConfig();
        $groups = $Config->getSection('taxgroups');

        if (is_array($groups) && count($groups)) {
            $newId = max(array_keys($groups)) + 1;
        } else {
            $groups = [];
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
                [
                    $current   => '',
                    'datatype' => 'php,js',
                    'package'  => 'quiqqer/tax'
                ]
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
     *
     * @throws QUI\Exception
     */
    public function getTaxGroups($ids = false): array
    {
        $Config = $this->getConfig();
        $groups = $Config->getSection('taxgroups');
        $result = [];

        if (!is_array($ids)) {
            $ids = false;
        }

        if (!$groups || !is_array($groups)) {
            $groups = [];
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
     * @param integer|string $taxGroupId - Tax Group ID
     * @return TaxGroup
     *
     * @throws QUI\Exception
     */
    public function getTaxGroup($taxGroupId): TaxGroup
    {
        if (!isset($this->taxGroups[$taxGroupId])) {
            $this->taxGroups[$taxGroupId] = new TaxGroup($taxGroupId);
        }

        return $this->taxGroups[$taxGroupId];
    }

    /**
     * Delete a tax group
     *
     * @param integer|string $taxGroupId - Tax Group ID
     * @throws QUI\Exception
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
        QUI::getDataBase()->delete($this->getDataBaseTableName(), [
            'taxGroupId' => $taxGroupId
        ]);
    }

    /**
     * Update a group
     *
     * @param integer|string $taxGroupId - Group-ID
     * @param array $types - array of types eq: - [typeId, typeId, typeId]
     *
     * @throws QUI\Exception
     */
    public function updateTaxGroup($taxGroupId, array $types = [])
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
     *
     * @throws QUI\Exception
     * @throws QUI\Permissions\Exception
     */
    public function createTaxType(): TaxType
    {
        Permission::checkPermission('quiqqer.tax.create');

        $Config = $this->getConfig();
        $types  = $Config->getSection('taxtypes');

        if (is_array($types) && count($types)) {
            $newId = max(array_keys($types)) + 1;
        } else {
            $types = [];
            $newId = 0;
        }

        $types[$newId] = 'taxType.' . $newId . '.title';

        $Config->setSection('taxtypes', $types);
        $Config->save();

        $current = QUI::getLocale()->getCurrent();

        // create locale
        try {
            QUI\Translator::addUserVar('quiqqer/tax', $types[$newId], [
                $current   => '',
                'datatype' => 'php,js',
                'package'  => 'quiqqer/tax'
            ]);
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
     *
     * @throws QUI\Exception
     */
    public function getTaxTypes($ids = false): array
    {
        $Config = $this->getConfig();
        $types  = $Config->getSection('taxtypes');
        $result = [];

        if (!is_array($ids)) {
            $ids = false;
        }

        if (!$types) {
            $types = [];
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
     * @param integer|string $taxTypeId - Tax Type ID
     * @return TaxType
     *
     * @throws QUI\Exception
     */
    public function getTaxType($taxTypeId): TaxType
    {
        if (!isset($this->taxTypes[$taxTypeId])) {
            $this->taxTypes[$taxTypeId] = new TaxType($taxTypeId);
        }

        return $this->taxTypes[$taxTypeId];
    }

    /**
     * Delete a tax type
     *
     * @param integer|string $taxTypeId - Tax Type ID
     *
     * @throws QUI\Exception
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
        QUI::getDataBase()->delete($this->getDataBaseTableName(), [
            'taxTypeId' => $taxTypeId
        ]);
    }


    /**
     * Helper
     */

    /**
     * Return tax config
     *
     * @return QUI\Config
     *
     * @throws QUI\Exception
     */
    public function getConfig(): QUI\Config
    {
        return QUI::getPackage('quiqqer/tax')->getConfig();
    }
}
