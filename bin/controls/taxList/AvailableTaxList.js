/**
 * @module package/quiqqer/tax/bin/controls/taxList/AvailableTaxList
 * @author www.pcsg.de (Henning Leutz)
 *
 * @event onLoad [self]
 */
define('package/quiqqer/tax/bin/controls/taxList/AvailableTaxList', [

    'qui/QUI',
    'qui/controls/Control',
    'Ajax',
    'Mustache',

    'text!package/quiqqer/tax/bin/controls/taxList/AvailableTaxList.html',
    'css!package/quiqqer/tax/bin/controls/taxList/AvailableTaxList.css'

], function (QUI, QUIControl, QUIAjax, Mustache, template) {
    "use strict";

    return new Class({

        Extends: QUIControl,
        Type   : 'package/quiqqer/tax/bin/controls/taxList/AvailableTaxList',

        Binds: [
            '$onInject'
        ],

        initialize: function (options) {
            this.parent(options);

            this.addEvents({
                onInject: this.$onInject
            });
        },

        /**
         * Create the DOMNode Element
         *
         * @return {HTMLDivElement}
         */
        create: function () {
            this.$Elm = this.parent();

            this.$Elm.addClass('qui-tax-availableTaxList');
            this.$Elm.set('html', '');

            return this.$Elm;
        },

        /**
         * event: on inject
         */
        $onInject: function () {
            var self = this;

            QUIAjax.get('package_quiqqer_tax_ajax_getAvailableTax', function (result) {
                self.getElm().set('html', Mustache.render(template, {
                    entries: result
                }));

                var entries = self.getElm().getElements('.quiqqer-tax-availableTax-list-entry');

                entries.addEvents({
                    click: function (event) {
                        var Target = event.target;

                        entries.removeClass('quiqqer-tax-availableTax-list-entry-selected');

                        if (!Target.hasClass('quiqqer-tax-availableTax-list-entry')) {
                            Target = Target.getParent('.quiqqer-tax-availableTax-list-entry');
                        }

                        Target.addClass('quiqqer-tax-availableTax-list-entry-selected');
                    }
                });


                self.fireEvent('load', [self]);
            }, {
                'package': 'quiqqer/tax'
            });
        },

        /**
         * Return the current selected vat / tax value
         *
         * @return {number|boolean}
         */
        getValue: function () {
            var Selected = this.getElm().getElement('.quiqqer-tax-availableTax-list-entry-selected');

            if (Selected) {
                return parseInt(Selected.get('data-value'));
            }

            return false;
        }
    });
});
