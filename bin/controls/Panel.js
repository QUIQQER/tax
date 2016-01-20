/**
 *
 * @package package/quiqqer/
 * @author www.pcsg.de (Henning Leutz)
 *
 * @require qui/QUI
 * @require qui/classes/DOM
 * @require Ajax
 * @require Locale
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
                text  : QUILocale.get(lg, 'panel.category.taxentries.text'),
                events: {
                    click: this.openTaxEntries
                }
            });

            this.addCategory({
                name  : 'taxtypes',
                text  : QUILocale.get(lg, 'panel.category.taxtypes.text'),
                events: {
                    click: this.openTaxTypes
                }
            });

            this.addCategory({
                name  : 'taxgroups',
                text  : QUILocale.get(lg, 'panel.category.taxgroups.text'),
                events: {
                    click: this.openTaxGroups
                }
            });
        },

        /**
         * event : on create
         */
        $onInject: function () {
            this.Loader.show();

            this.checkImport().then(function () {
                this.getCategory('taxentries').click();
                this.Loader.show();

            }.bind(this));
        },

        /**
         * Checks, if we can run an import
         *
         * @returns {Promise}
         */
        checkImport: function () {

            var self = this;

            return new Promise(function (resolve, reject) {
                require([
                    'package/quiqqer/tax/bin/classes/TaxEntries',
                    'package/quiqqer/tax/bin/classes/TaxGroups',
                    'package/quiqqer/tax/bin/classes/TaxTypes'
                ], function (TaxEntries, TaxGroups, TaxTypes) {

                    Promise.all([
                        new TaxEntries().getList(),
                        new TaxGroups().getList(),
                        new TaxTypes().getList()
                    ]).then(function (result) {

                        var taxEntries = result[0],
                            taxGroups  = result[1],
                            taxTypes   = result[2];

                        if (!taxEntries.length && !taxGroups.length && !taxTypes.length) {
                            self.openImport();
                            resolve();
                            return;
                        }

                        if (!taxEntries.length) {
                            QUI.getMessageHandler().then(function (MH) {
                                MH.addInformation(
                                    QUILocale.get(
                                        'quiqqer/tax',
                                        'message.tax.create.taxentries'
                                    )
                                );
                            });
                        }

                        if (!taxGroups.length) {
                            QUI.getMessageHandler().then(function (MH) {
                                MH.addInformation(
                                    QUILocale.get(
                                        'quiqqer/tax',
                                        'message.tax.create.taxgroups'
                                    )
                                );
                            });
                        }

                        if (!taxTypes.length) {
                            QUI.getMessageHandler().then(function (MH) {
                                MH.addInformation(
                                    QUILocale.get(
                                        'quiqqer/tax',
                                        'message.tax.create.taxtypes'
                                    )
                                );
                            });
                        }

                        resolve();

                    }, reject);
                }, reject);
            });
        },

        /**
         * Opens the import
         */
        openImport: function () {
            require([
                'package/quiqqer/tax/bin/controls/Import'
            ], function (Import) {
                new Import().open();
            });
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
