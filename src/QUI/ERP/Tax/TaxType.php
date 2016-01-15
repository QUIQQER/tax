<?php

/**
 * This file contains QUI\ERP\Tax\TaxType
 */
namespace QUI\ERP\Tax;

use QUI;

/**
 * Class TaxType
 * - Steuerart
 *
 * Is not realy editable, it makes no sence to edit a type
 * A type has only a title, the title is stored in the translator
 *
 * @package QUI\ERP\Tax
 */
class TaxType
{
    /**
     * @var integer
     */
    protected $id;

    /**
     * tax Handler
     * @var QUI\ERP\Tax\Handler
     */
    protected $Handler;

    /**
     * TaxGroup constructor.
     *
     * @param integer $taxTypeId
     * @throws QUI\Exception
     */
    public function __construct($taxTypeId)
    {
        $Handler = new QUI\ERP\Tax\Handler();
        $Config  = $Handler->getConfig();

        if (!$Config->get('taxtypes', $taxTypeId)) {
            throw new QUI\Exception(array(
                'quiqqer/tax',
                'exception.taxtype.not.found'
            ));
        };

        $this->id      = (int)$taxTypeId;
        $this->Handler = $Handler;
    }

    /**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Return the tax type title
     *
     * @return array|string
     */
    public function getTitle()
    {
        return QUI::getLocale()->get(
            'quiqqer/tax',
            'taxType.' . $this->getId() . '.title'
        );
    }

    /**
     * Return the task group
     *
     * @return TaxGroup|boolean
     */
    public function getGroup()
    {
        $groups = $this->Handler->getTaxGroups();

        /* @var $Group TaxGroup */
        foreach ($groups as $Group) {
            if ($Group->isTaxTypeInGroup($this)) {
                return $Group;
            }
        }

        return false;
    }

    /**
     * Return the tax type as array
     * @return array
     */
    public function toArray()
    {
        $groupId    = '';
        $groupTitle = '';

        if ($this->getGroup()) {
            $groupId    = $this->getGroup()->getId();
            $groupTitle = $this->getGroup()->getTitle();
        }

        return array(
            'id' => $this->getId(),
            'title' => $this->getTitle(),
            'groupId' => $groupId,
            'groupTitle' => $groupTitle
        );
    }

    /**
     * Delete the tax type
     */
    public function delete()
    {
        $this->Handler->deleteTaxType($this->getId());
    }
}
