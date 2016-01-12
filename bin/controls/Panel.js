/**
 *
 * @package package/quiqqer/
 * @author www.pcsg.de (Henning Leutz)
 *
 * @require qui/QUI
 * @require qui/classes/DOM
 */
define('package/quiqqer/tax/bin/controls/Panel', [

    'qui/QUI',
    'qui/controls/desktop/Panel',
    'Ajax',
    'Locale'

], function (QUI, QUIPanel, QUIAjax, QUILocale) {
    "use strict";

    var lg = 'quiqqer/tax';

    return new Class({
        Extends: QUIPanel,
        Type   : 'package/quiqqer/tax/bin/controls/Panel',

        Binds: [
            'openTaxGroups',
            '$onCreate'
        ],

        initialize: function (options) {
            this.setAttributes({
                title: QUILocale.get(lg, 'panel.title')
            });

            this.parent(options);

            this.addEvents({
                onCreate: this.$onCreate
            });
        },

        /**
         * event : on create
         */
        $onCreate: function () {

            this.addCategory({
                text: 'Steuersatz'
            });

            this.addCategory({
                text: 'Steuerarten'
            });

            this.addCategory({
                text  : 'Steuergruppen',
                events: {
                    click: this.openTaxGroups
                }
            });

        },

        /**
         * open the tax groups
         */
        openTaxGroups: function () {

            var self    = this,
                Content = this.getContent();

            this.Loader.show();

            require([
                'package/quiqqer/tax/bin/controls/TaxGroups'
            ], function (TaxGroups) {
                new TaxGroups({
                    events: {
                        onLoaded: function () {
                            self.Loader.hide();
                        }
                    }
                }).inject(Content);
            });
        }
    });
});
