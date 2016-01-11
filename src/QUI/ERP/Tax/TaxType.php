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
 * @package QUI\ERP\Tax
 */
class TaxType
{
    /**
     * @var integer
     */
    protected $id;

    /**
     * TaxGroup constructor.
     *
     * @param integer $taxTypeId
     * @throws QUI\Exception
     */
    public function __construct($taxTypeId)
    {
        $Tax    = QUI::getPackage('quiqqer/tax');
        $Config = $Tax->getConfig();

        if (!$Config->get('taxtypes', $taxTypeId)) {
            throw new QUI\Exception(array(
                'quiqqer/tax',
                'exception.taxtype.not.found'
            ));
        };

        $this->id = (int)$taxTypeId;
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
            'taxtype.' . $this->getId() . '.title'
        );
    }

    /**
     *
     * @return array
     */
    public function getTax()
    {

        return array();
    }
}
