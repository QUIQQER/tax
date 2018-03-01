<?php

/**
 * This file contains QUI\ERP\Tax\EventHandler
 */

namespace QUI\ERP\Tax;

use QUI;
use Quiqqer\Engine\Collector;

/**
 * Class EventHandler
 *
 * @package QUI\ERP\Tax
 */
class EventHandler
{
    /**
     * @param \Quiqqer\Engine\Collector $Collector
     * @param $User
     * @param $Address
     */
    public static function onFrontendUserDataMiddle(Collector $Collector, $User, $Address)
    {
        try {
            $Engine = QUI::getTemplateManager()->getEngine();
        } catch (QUI\Exception $Exception) {
            QUI\System\Log::writeException($Exception);

            return;
        }

        $Engine->assign([
            'User'    => $User,
            'Address' => $Address
        ]);

        try {
            $Collector->append(
                $Engine->fetch(dirname(__FILE__).'/FrontendUsers/profileData.html')
            );
        } catch (\Exception $Exception) {
            QUI\System\Log::writeException($Exception);
        }
    }
}
