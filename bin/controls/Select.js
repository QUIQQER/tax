define('package/quiqqer/tax/bin/controls/Select', [

    'qui/QUI',
    'qui/controls/Control',
    'qui/controls/buttons/Select',
    'package/quiqqer/tax/bin/classes/TaxTypes'

], function (QUI, QUIControl, QUISelect, TaxHandler) {
    "use strict";

    const Tax = new TaxHandler();

    return new Class({

        Extends: QUIControl,
        Type   : 'package/quiqqer/tax/bin/controls/Select',

        Binds: [
            '$onImport'
        ],

        initialize: function (options) {
            this.parent(options);

            this.$Input = null;
            this.$Select = null;

            this.addEvents({
                onImport: this.$onImport
            });
        },

        $onImport: function () {
            this.$Input = this.getElm();
            this.$Input.type = 'hidden';

            this.$Elm = new Element('div').wraps(this.$Input);

            this.$Select = new QUISelect({
                styles: {
                    width: '100%'
                }
            }).inject(this.$Elm);

            if (this.$Input.hasClass('field-container-field')) {
                this.$Input.removeClass('field-container-field');
                this.$Elm.addClass('field-container-field');
                this.$Elm.addClass('field-container-field-no-padding');
                this.$Select.getElm().setStyle('border', 0);
            }

            this.$Select.clear();

            Tax.getList().then((list) => {
                let i, len, html, value;
                let selectValue = '';

                if (this.$Input.value !== '') {
                    selectValue = this.$Input.value;
                }

                for (i = 0, len = list.length; i < len; i++) {
                    html = list[i].groupTitle + ' : ' + list[i].title;
                    value = list[i].groupId + ':' + list[i].id;

                    if (list[i].id === this.$Input.value) {
                        selectValue = value;
                    }

                    this.$Select.appendChild(
                        html,
                        value,
                        'fa fa-percent'
                    );
                }

                if (selectValue !== '') {
                    this.$Select.setValue(selectValue);
                }
            });
        }
    });
});
