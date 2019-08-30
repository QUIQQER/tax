<?php

/**
 * This file contains QUI\ERP\Tax\TaxGroup
 */

namespace QUI\ERP\Tax;

use QUI;

/**
 * Class TaxGroup
 * Steuergruppe
 *
 * @package QUI\ERP\Tax
 */
class TaxGroup
{
    /**
     * @var integer
     */
    protected $id;

    /**
     * @var array
     */
    protected $taxTypes = [];

    /**
     * tax Handler
     * @var QUI\ERP\Tax\Handler
     */
    protected $Handler;

    /**
     * TaxGroup constructor.
     *
     * @param integer $taxGroupId
     * @throws QUI\Exception
     */
    public function __construct($taxGroupId)
    {
        $Handler = new QUI\ERP\Tax\Handler();
        $Config  = $Handler->getConfig();

        if ($Config->get('taxgroups', $taxGroupId) === false) {
            throw new QUI\Exception([
                'quiqqer/tax',
                'exception.taxgroup.not.found'
            ]);
        };

        $this->id      = (int)$taxGroupId;
        $this->Handler = $Handler;

        $taxTypes = $Config->get('taxgroups', $taxGroupId);
        $taxTypes = \explode(',', $taxTypes);

        foreach ($taxTypes as $taxTypeId) {
            try {
                $this->taxTypes[] = $this->Handler->getTaxType($taxTypeId);
            } catch (QUI\Exception $Exception) {
            }
        }
    }

    /**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return array|string
     */
    public function getTitle()
    {
        return QUI::getLocale()->get(
            'quiqqer/tax',
            'taxGroup.'.$this->getId().'.title'
        );
    }

    /**
     * Return the taxtypes from the group
     *
     * @return array - [TaxType,TaxType,TaxType]
     */
    public function getTaxTypes()
    {
        return $this->taxTypes;
    }

    /**
     * Is the TaxType in the Groups
     *
     * @param TaxType $TaxType
     * @return boolean
     */
    public function isTaxTypeInGroup(TaxType $TaxType)
    {
        /* @var $Tax TaxType */
        foreach ($this->taxTypes as $Tax) {
            if ($Tax->getId() == $TaxType->getId()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Set the tax types list to the tax group
     * the current list would be overwritten
     *
     * @param array $types - [typeId, typeId, typeId]
     */
    public function setTaxTypes($types = [])
    {
        if (!\is_array($types)) {
            return;
        }

        $list = [];

        foreach ($types as $taxTypeId) {
            try {
                $list[] = $this->Handler->getTaxType($taxTypeId);
            } catch (QUI\Exception $Exception) {
            }
        }

        $this->taxTypes = $list;
    }

    /**
     * Add a tax type to the tax group
     *
     * @param TaxType $TaxType
     */
    public function addTaxType(TaxType $TaxType)
    {
        if (!$this->isTaxTypeInGroup($TaxType)) {
            $this->taxTypes[] = $TaxType;
        }
    }

    /**
     * Return the tax group as array
     *
     * @return array
     */
    public function toArray()
    {
        $types = [];

        /* @var $TaxType TaxType */
        foreach ($this->taxTypes as $TaxType) {
            $types[] = $TaxType->getId();
        }

        return [
            'id'       => $this->getId(),
            'title'    => $this->getTitle(),
            'taxtypes' => \implode(',', $types)
        ];
    }

    /**
     * Saves / Update the tax group
     *
     * @throws QUI\Exception
     */
    public function update()
    {
        $data   = $this->toArray();
        $Config = $this->Handler->getConfig();

        $Config->set('taxgroups', $this->getId(), $data['taxtypes']);
        $Config->save();
    }

    /**
     * Delete the tax group
     *
     * @throws QUI\Exception
     */
    public function delete()
    {
        $this->Handler->deleteTaxGroup($this->getId());
    }
}
