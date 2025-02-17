<?php

/**
 * This file contains QUI\ERP\Tax\TaxGroup
 */

namespace QUI\ERP\Tax;

use QUI;

use function explode;
use function implode;
use function is_array;

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
    protected int $id;

    /**
     * @var array
     */
    protected array $taxTypes = [];

    /**
     * tax Handler
     * @var QUI\ERP\Tax\Handler
     */
    protected Handler $Handler;

    /**
     * TaxGroup constructor.
     *
     * @param integer|string $taxGroupId
     * @throws QUI\Exception
     */
    public function __construct(int | string $taxGroupId)
    {
        $Handler = new QUI\ERP\Tax\Handler();
        $Config = $Handler->getConfig();

        if ($Config->get('taxgroups', $taxGroupId) === false) {
            throw new QUI\Exception([
                'quiqqer/tax',
                'exception.taxgroup.not.found'
            ]);
        }

        $this->id = (int)$taxGroupId;
        $this->Handler = $Handler;

        $taxTypes = $Config->get('taxgroups', $taxGroupId);
        $taxTypes = explode(',', $taxTypes);

        foreach ($taxTypes as $taxTypeId) {
            try {
                $this->taxTypes[] = $this->Handler->getTaxType($taxTypeId);
            } catch (QUI\Exception) {
            }
        }
    }

    /**
     * @return integer
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return QUI::getLocale()->get(
            'quiqqer/tax',
            'taxGroup.' . $this->getId() . '.title'
        );
    }

    /**
     * Return the tax types from the group
     *
     * @return TaxType[]
     */
    public function getTaxTypes(): array
    {
        return $this->taxTypes;
    }

    /**
     * Is the TaxType in the Groups
     *
     * @param TaxType $TaxType
     * @return boolean
     */
    public function isTaxTypeInGroup(TaxType $TaxType): bool
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
    public function setTaxTypes(array $types = []): void
    {
        if (!is_array($types)) {
            return;
        }

        $list = [];

        foreach ($types as $taxTypeId) {
            try {
                $list[] = $this->Handler->getTaxType($taxTypeId);
            } catch (QUI\Exception) {
            }
        }

        $this->taxTypes = $list;
    }

    /**
     * Add a tax type to the tax group
     *
     * @param TaxType $TaxType
     */
    public function addTaxType(TaxType $TaxType): void
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
    public function toArray(): array
    {
        $types = [];

        /* @var $TaxType TaxType */
        foreach ($this->taxTypes as $TaxType) {
            $types[] = $TaxType->getId();
        }

        return [
            'id' => $this->getId(),
            'title' => $this->getTitle(),
            'taxtypes' => implode(',', $types)
        ];
    }

    /**
     * Saves / Update the tax group
     *
     * @throws QUI\Exception
     */
    public function update(): void
    {
        $data = $this->toArray();
        $Config = $this->Handler->getConfig();

        $Config->set('taxgroups', (string)$this->getId(), $data['taxtypes']);
        $Config->save();
    }

    /**
     * Delete the tax group
     *
     * @throws QUI\Exception
     */
    public function delete(): void
    {
        $this->Handler->deleteTaxGroup($this->getId());
    }
}
