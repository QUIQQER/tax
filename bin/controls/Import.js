/**
 * Import window
 *
 * @module package/quiqqer/tax/bin/controls/Import
 * @author www.pcsg.de (Henning Leutz)
 *
 * @event onImportBegin
 * @event onImport
 */
define('package/quiqqer/tax/bin/controls/Import', [

    'qui/QUI',
    'qui/controls/windows/Confirm',
    'qui/controls/buttons/Select',
    'Locale',
    'Ajax',
    'controls/upload/Form',

    'text!package/quiqqer/tax/bin/controls/Import.html'

], function (QUI,
             QUIConfirm,
             QUISelect,
             QUILocale,
             QUIAjax,
             UploadForm,
             templateImport) {
    "use strict";

    return new Class({

        Extends: QUIConfirm,
        Type   : 'package/quiqqer/tax/bin/controls/Import',

        Binds: [
            '$onOpen',
            '$onSubmit'
        ],

        initialize: function (options) {

            this.setAttributes({
                maxHeight: 460,
                maxWidth : 690,
                title    : 'Noch keine Steuersätze vorhanden',
                icon     : 'fa fa-upload',
                autoclose: false
            });

            this.parent(options);

            this.$Select = null;
            this.$Upload = null;

            this.addEvents({
                onOpen  : this.$onOpen,
                onSubmit: this.$onSubmit
            });
        },

        /**
         * events: on open
         */
        $onOpen: function () {
            var self = this;

            this.Loader.show();

            this.getContent().set('html', templateImport);

            var Content   = this.getContent(),
                Available = Content.getElement('.available-imports'),
                Upload    = Content.getElement('.own-import');

            this.$Upload = new UploadForm({
                maxuploads: 1
            }).inject(Upload);

            this.$Upload.setParam(
                'onfinish',
                'package_quiqqer_tax_ajax_import_upload'
            );

            this.$Select = new QUISelect({
                events: {
                    onChange: function (value) {
                        if (value !== '') {
                            self.$Upload.disable();
                        } else {
                            self.$Upload.enable();
                        }
                    }
                }
            }).inject(Available);


            QUIAjax.get(
                'package_quiqqer_tax_ajax_import_available',
                function (result) {
                    self.$Select.appendChild('&nbsp;', '');

                    for (var i = 0, len = result.length; i < len; i++) {
                        self.$Select.appendChild(
                            QUILocale.get(
                                result[i].locale[0],
                                result[i].locale[1]
                            ),
                            result[i].file
                        );
                    }

                    self.Loader.hide();
                }, {
                    'package': 'quiqqer/tax'
                });
        },

        /**
         * event : on submit
         */
        $onSubmit: function () {
            this.Loader.show();

            var self        = this,
                selectValue = this.$Select.getValue();

            this.fireEvent('importBegin');

            if (!selectValue || selectValue === '') {

                this.$Upload.addEvent('onComplete', function () {
                    self.Loader.hide();
                    self.close();
                    self.fireEvent('import');
                });

                this.$Upload.submit();
                return;
            }

            QUIAjax.get('package_quiqqer_tax_ajax_import_preconfigure', function () {

                require([
                    'package/quiqqer/translator/bin/classes/Translator'
                ], function (Translator) {
                    var Trans = new Translator();

                    Trans.publish().then(function () {
                        return Trans.refreshLocale();
                    }).then(function () {
                        self.close();
                        self.fireEvent('import');
                    });
                });

            }, {
                'package' : 'quiqqer/tax',
                importName: selectValue
            });
        }
    });
});
