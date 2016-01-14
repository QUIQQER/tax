/**
 *
 * @package package/quiqqer/
 * @author www.pcsg.de (Henning Leutz)
 *
 * @require qui/QUI
 * @require qui/classes/DOM
 * @require package/quiqqer/tax/bin/classes/TaxTypesEdit
 *
 * @event onLoaded
 */
define('package/quiqqer/tax/bin/controls/TaxTypesEdit', [

    'qui/QUI',
    'qui/controls/Control',
    'qui/controls/buttons/Select',
    'package/quiqqer/tax/bin/classes/TaxGroups',
    'package/quiqqer/tax/bin/classes/TaxTypes',
    'Locale',
    'package/quiqqer/translator/bin/controls/VariableTranslation',

    'text!package/quiqqer/tax/bin/controls/TaxTypesEdit.html'

], function (QUI, QUIControl, QUISelect,
             TaxGroups, TaxTypes, QUILocale, Translation, template) {
    "use strict";

    var GroupHandler = new TaxGroups();
    var TypesHandler = new TaxTypes();

    return new Class({
        Extends: QUIControl,
        Type   : 'package/quiqqer/tax/bin/controls/TaxTypesEdit',

        Binds: [
            '$onInject'
        ],

        options: {
            taxTypeId: false
        },

        initialize: function (options) {
            this.parent(options);

            this.addEvents({
                onInject: this.$onInject
            });
        },

        /**
         * event : on create
         */
        create: function () {
            var Elm = this.parent();

            Elm.set('html', template);

            return Elm;
        },

        /**
         * event : on inject
         */
        $onInject: function () {
            var Elm    = this.getElm(),
                id     = this.getAttribute('taxTypeId'),
                Groups = Elm.getElement(
                    '.quiqqer-taxtype-setting-table-group'
                );

            new Translation({
                'group': 'quiqqer/tax',
                'var'  : 'taxType.' + id + '.title'
            }).inject(
                Elm.getElement('.quiqqer-taxtype-setting-table-title')
            );

            this.$Select = new QUISelect({
                showIcons: false
            }).inject(Groups);

            Promise.all([
                TypesHandler.get(id),
                GroupHandler.getList()
            ]).then(function (data) {
                var type   = data[0],
                    groups = data[1];

                for (var i = 0, len = groups.length; i < len; i++) {
                    this.$Select.appendChild(
                        groups[i].title,
                        groups[i].id
                    );
                }

                if (type.group !== '') {
                    this.$Select.setValue(type.group);
                }

                this.fireEvent('loaded');

            }.bind(this));
        },

        /**
         * Update the tax type
         *
         * @returns {Promise}
         */
        update: function () {
            return TypesHandler.updateChild(
                this.getAttribute('taxTypeId'),
                {
                    group: this.$Select.getValue()
                }
            );
        }
    });
});
