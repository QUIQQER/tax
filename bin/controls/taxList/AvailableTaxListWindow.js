/**
 * @module package/quiqqer/tax/bin/controls/taxList/AvailableTaxListWindow
 * @author www.pcsg.de (Henning Leutz)
 */
define('package/quiqqer/tax/bin/controls/taxList/AvailableTaxListWindow', [

    'qui/QUI',
    'qui/controls/windows/Confirm',
    'Locale'

], function (QUI, QUIConfirm, QUILocale) {
    "use strict";

    return new Class({

        Extends: QUIConfirm,
        Type   : 'package/quiqqer/tax/bin/controls/taxList/AvailableTaxListWindow',

        Binds: [
            '$onOpen'
        ],

        options: {
            maxHeight: 600,
            maxWidth : 400,
            icon     : 'fa fa-percent'
        },

        initialize: function (options) {
            this.setAttribute('title', QUILocale.get('quiqqer/tax', 'window.availableTaxList.title'));

            this.parent(options);

            this.$List = null;

            this.addEvents({
                onOpen: this.$onOpen
            });
        },

        /**
         * event: on inject
         */
        $onOpen: function () {
            var self = this;

            this.Loader.show();
            this.getContent().set('html', '');

            require(['package/quiqqer/tax/bin/controls/taxList/AvailableTaxList'], function (List) {
                self.$List = new List({
                    events: {
                        onLoad: function () {
                            self.Loader.hide();
                        }
                    }
                }).inject(self.getContent());
            });
        },

        /**
         * execute the submission
         */
        submit: function () {
            if (!this.$List) {
                return;
            }

            var value = this.$List.getValue();

            var isNumeric = function (value) {
                return !isNaN(parseFloat(value)) && isFinite(value)
            };

            if (!isNumeric(value)) {
                return;
            }

            this.fireEvent('submit', [this, value]);

            if (this.getAttribute('autoclose')) {
                this.close();
            }
        }
    });
});
