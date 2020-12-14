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
            ->addArgument('--set-tax', 'Set the vat for a specific tax', false, false)
            ->addArgument('---vat', 'Vat as number', false, false)
            ->setDescription('Tax / Vat Utils');
    }

    /**
     * execute the tax utils
     */
    public function execute()
    {
        $taxId = $this->getArgument('--set-tax');
        $vat   = $this->getArgument('--vat');

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
