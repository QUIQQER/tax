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
    protected $taxtypes = array();

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

        if (!$Config->get('taxgroups', $taxGroupId)) {
            throw new QUI\Exception(array(
                'quiqqer/tax',
                'exception.taxgroup.not.found'
            ));
        };

        $this->id      = (int)$taxGroupId;
        $this->Handler = $Handler;

        $taxtypes = $Config->get('taxgroups', $taxGroupId);
        $taxtypes = explode(',', $taxtypes);

        foreach ($taxtypes as $taxTypeId) {
            try {
                $this->taxtypes[] = $this->Handler->getTaxType($taxTypeId);
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
            'taxGroup.' . $this->getId() . '.title'
        );
    }

    /**
     * Return the taxtypes from the group
     *
     * @return array - [TaxType,TaxType,TaxType]
     */
    public function getTaxTypes()
    {
        return $this->taxtypes;
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
        foreach ($this->taxtypes as $Tax) {
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
    public function setTaxTypes($types = array())
    {
        if (!is_array($types)) {
            return;
        }

        $list = array();

        foreach ($types as $taxTypeId) {
            try {
                $list[] = $this->Handler->getTaxType($taxTypeId);
            } catch (QUI\Exception $Exception) {
            }
        }

        $this->taxtypes = $list;
    }

    /**
     * Add a tax type to the tax group
     *
     * @param TaxType $TaxType
     */
    public function addTaxType(TaxType $TaxType)
    {
        if (!$this->isTaxTypeInGroup($TaxType)) {
            $this->taxtypes[] = $TaxType;
        }
    }

    /**
     * Return the tax group as array
     *
     * @return array
     */
    public function toArray()
    {
        $types = array();

        /* @var $TaxType TaxType */
        foreach ($this->taxtypes as $TaxType) {
            $types[] = $TaxType->getId();
        }

        return array(
            'id' => $this->getId(),
            'title' => $this->getTitle(),
            'taxtypes' => implode(',', $types)
        );
    }

    /**
     * Saves / Update the tax group
     */
    public function update()
    {
        $data   = $this->toArray();
        $Config = $this->Handler->getConfig();

        $Config->set('taxgroups', $this->getId(), $data['taxtypes']);
        $Config->save();
    }
}
