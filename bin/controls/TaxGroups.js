/**
 *
 * @package package/quiqqer/
 * @author www.pcsg.de (Henning Leutz)
 *
 * @require qui/QUI
 * @require qui/classes/DOM
 * @require package/quiqqer/tax/bin/classes/TaxGroups
 */
define('package/quiqqer/tax/bin/controls/TaxGroups', [

    'qui/QUI',
    'qui/controls/Control',
    'package/quiqqer/tax/bin/classes/TaxGroups'

], function (QUI, QUIControl, TaxGroups) {
    "use strict";

    var Handler = new TaxGroups();

    return new Class({
        Extends: QUIControl,
        Type   : 'package/quiqqer/tax/bin/controls/TaxGroup',

        Binds: [
            '$onInject',
            '$onCreate'
        ],

        initialize: function (options) {
            this.parent(options);

            this.addEvents({
                onCreate: this.$onCreate,
                onInject: this.$onInject
            });
        },

        /**
         * event : on create
         */
        $onCreate: function () {


        },

        /**
         *
         */
        $onInject: function () {
            Handler.getList().then(function (result) {
                console.log(result);

                if (!result.length) {

                }

            });
        }
    });
});
