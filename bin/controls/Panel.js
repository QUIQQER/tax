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
            'openTaxEntries',
            'openTaxTypes',
            'openTaxGroups',
            '$onCreate',
            '$onInject'
        ],

        initialize: function (options) {
            this.setAttributes({
                title: QUILocale.get(lg, 'panel.title')
            });

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

            this.addCategory({
                name  : 'taxentries',
                text  : 'Steuersatz',
                events: {
                    click: this.openTaxEntries
                }
            });

            this.addCategory({
                name  : 'taxtypes',
                text  : 'Steuerarten',
                events: {
                    click: this.openTaxTypes
                }
            });

            this.addCategory({
                name  : 'taxgroups',
                text  : 'Steuergruppen',
                events: {
                    click: this.openTaxGroups
                }
            });
        },

        /**
         * event : on create
         */
        $onInject: function () {
            (function () {
                this.getCategory('taxentries').click();
            }).delay(750, this);
        },

        /**
         * open the tax groups
         */
        openTaxEntries: function () {

            var self    = this,
                Content = this.getContent();

            this.Loader.show();

            Content.set('html', '');

            require([
                'package/quiqqer/tax/bin/controls/TaxEntries'
            ], function (TaxEntries) {
                new TaxEntries({
                    Panel : self,
                    events: {
                        onLoaded: function () {
                            self.Loader.hide();
                        }
                    }
                }).inject(Content);
            });
        },

        /**
         * open the tax groups
         */
        openTaxTypes: function () {

            var self    = this,
                Content = this.getContent();

            this.Loader.show();

            Content.set('html', '');

            require([
                'package/quiqqer/tax/bin/controls/TaxTypes'
            ], function (TaxTypes) {
                new TaxTypes({
                    Panel : self,
                    events: {
                        onLoaded: function () {
                            self.Loader.hide();
                        }
                    }
                }).inject(Content);
            });
        },

        /**
         * open the tax groups
         */
        openTaxGroups: function () {

            var self    = this,
                Content = this.getContent();

            this.Loader.show();

            Content.set('html', '');

            require([
                'package/quiqqer/tax/bin/controls/TaxGroups'
            ], function (TaxGroups) {
                new TaxGroups({
                    Panel : self,
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
