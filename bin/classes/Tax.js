/**
 *
 * @package package/quiqqer/tax/bin/classes/Tax
 * @author www.pcsg.de (Henning Leutz)
 *
 * @require qui/QUI
 * @require qui/classes/DOM
 */
define('package/quiqqer/tax/bin/classes/Tax', [

    'qui/QUI',
    'qui/classes/DOM',
    'Ajax'

], function (QUI, QDOM, QUIAjax) {
    "use strict";

    return new Class({
        Extends: QDOM,
        Type   : 'package/quiqqer/tax/bin/classes/Tax',

        initialize: function (options) {
            this.parent(options);
        }
    });
});
