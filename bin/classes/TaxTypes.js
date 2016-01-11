/**
 *
 * @package package/quiqqer/
 * @author www.pcsg.de (Henning Leutz)
 *
 * @require qui/QUI
 * @require qui/classes/DOM
 */
define('package/quiqqer/tax/bin/classes/TaxTypes', [

    'qui/QUI',
    'qui/classes/DOM',
    'Ajax'

], function (QUI, QDOM, QUIAjax) {
    "use strict";

    return new Class({
        Extends: QDOM,
        Type   : 'package/quiqqer/tax/bin/classes/TaxTypes',

        initialize: function (options) {
            this.parent(options);
        }
    });
});
