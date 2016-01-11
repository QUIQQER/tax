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
     * TaxGroup constructor.
     *
     * @param integer $taxGroupId
     * @throws QUI\Exception
     */
    public function __construct($taxGroupId)
    {
        $Tax    = QUI::getPackage('quiqqer/tax');
        $Config = $Tax->getConfig();

        if (!$Config->get('taxgroups', $taxGroupId)) {
            throw new QUI\Exception(array(
                'quiqqer/tax',
                'exception.taxgroup.not.found'
            ));
        };

        $this->id       = (int)$taxGroupId;
        $this->taxtypes = $Config->get('taxgroups', $taxGroupId);
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
            'quiqqer/areas',
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
        $result = array();
        $types  = explode(',', $this->taxtypes);

        foreach ($types as $taxTypeId) {
            try {
                $result[] = new TaxType($taxTypeId);
            } catch (QUI\Exception $Exception) {
            }
        }

        return $result;
    }
}
