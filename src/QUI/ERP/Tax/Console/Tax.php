<?php

namespace QUI\ERP\Tax\Console;

use QUI;
use QUI\System\Console\Tool;

/**
 * Class Tax
 */
class Tax extends Tool
{
    public function __construct()
    {
        $this->systemTool = true;

        $this->setName('quiqqer:tax')
            ->addArgument('--tax-id', 'Id of the tax')
            ->addArgument('---vat', 'Vat as number')
            ->setDescription('Tax / Vat Utils - Set the vat for specific tax');
    }

    /**
     * execute the tax utils
     */
    public function execute(): void
    {
        $taxId = $this->getArgument('--tax-id');
        $vat = $this->getArgument('--vat');

        if (empty($taxId)) {
            return;
        }

        try {
            $Tax = QUI\ERP\Tax\Handler::getInstance()->getChild($taxId);
            $Tax->setAttribute('vat', (int)$vat);
            $Tax->update();
        } catch (QUI\Exception $Exception) {
            $this->writeLn($Exception->getMessage(), 'red');
        }
    }
}
