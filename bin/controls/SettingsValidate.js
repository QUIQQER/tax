/**
 * @module package/quiqqer/tax/bin/controls/SettingsValidate
 * @author www.pcsg.de (Henning Leutz)
 */
define('package/quiqqer/tax/bin/controls/SettingsValidate', [

    'qui/QUI',
    'qui/controls/Control',
    'Ajax',
    'Locale'

], function (QUI, QUIControl, QUIAjax, QUILocale) {
    "use strict";

    return new Class({

        Extends: QUIControl,
        Type   : 'package/quiqqer/tax/bin/controls/SettingsValidate',

        Binds: [
            '$onImport',
            '$onChange'
        ],

        /**
         * initialize
         * @param options
         */
        initialize: function (options) {
            this.parent(options);

            this.$Input = null;

            this.addEvents({
                onImport: this.$onImport
            });
        },

        /**
         * event: on import
         */
        $onImport: function () {
            if (this.getElm().nodeName !== 'INPUT') {
                return;
            }

            this.$Input = this.getElm();
            this.$Input.addEvent('change', this.$onChange);

            if (this.$Input.checked) {
                this.$onChange();
            }
        },

        /**
         * execute the tax check checking
         */
        $onChange: function () {
            if (!this.$Input) {
                return;
            }

            if (this.$Input.checked === false) {
                return;
            }

            var self = this;

            QUIAjax.get('package_quiqqer_tax_ajax_isCheckAvailable', function (result) {
                if (result) {
                    // all is fine
                    return;
                }

                self.$Input.checked = false;

                QUI.getMessageHandler().then(function (MH) {
                    MH.addError(
                        QUILocale.get('quiqqer/tax', 'exception.no.soap.client')
                    );
                });
            }, {
                'package': 'quiqqer/tax'
            });
        }
    });
});
