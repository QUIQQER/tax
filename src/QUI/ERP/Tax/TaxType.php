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
 * Is not really editable, it makes no sense to edit a type
 * - A type has only a title, the title is stored in the translator
 *
 * @package QUI\ERP\Tax
 */
class TaxType
{
    /**
     * @var integer
     */
    protected int $id;

    /**
     * tax Handler
     * @var QUI\ERP\Tax\Handler
     */
    protected Handler $Handler;

    /**
     * TaxGroup constructor.
     *
     * @param integer|string $taxTypeId
     * @throws QUI\Exception
     */
    public function __construct(int|string $taxTypeId)
    {
        $Handler = new QUI\ERP\Tax\Handler();
        $Config = $Handler->getConfig();

        if (!$Config->getValue('taxtypes', $taxTypeId)) {
            throw new QUI\Exception([
                'quiqqer/tax',
                'exception.taxtype.not.found'
            ]);
        }

        $this->id = (int)$taxTypeId;
        $this->Handler = $Handler;
    }

    /**
     * @return integer
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Return the tax type title
     *
     * @return string
     */
    public function getTitle(): string
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
    public function getGroup(): TaxGroup|bool
    {
        try {
            $groups = $this->Handler->getTaxGroups();
        } catch (QUI\Exception $Exception) {
            QUI\System\Log::writeException($Exception);

            return false;
        }

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
    public function toArray(): array
    {
        $groupId = '';
        $groupTitle = '';

        if ($this->getGroup()) {
            $groupId = $this->getGroup()->getId();
            $groupTitle = $this->getGroup()->getTitle();
        }

        return [
            'id' => $this->getId(),
            'title' => $this->getTitle(),
            'groupId' => $groupId,
            'groupTitle' => $groupTitle
        ];
    }

    /**
     * Delete the tax type
     *
     * @throws QUI\Exception
     */
    public function delete(): void
    {
        $this->Handler->deleteTaxType($this->getId());
    }

    /**
     * @return bool
     */
    public function isVisible(): bool
    {
        return true;
    }
}
