/**
 * @module package/quiqqer/tax/bin/controls/TaxTypesEdit
 * @author www.pcsg.de (Henning Leutz)
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
    'Mustache',
    'package/quiqqer/translator/bin/controls/Update',

    'text!package/quiqqer/tax/bin/controls/TaxTypesEdit.html'

], function (QUI, QUIControl, QUISelect,
             TaxGroups, TaxTypes, QUILocale, Mustache, Translation, template) {
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

            this.$Title  = null;
            this.$Select = null;

            this.addEvents({
                onInject: this.$onInject
            });
        },

        /**
         * event : on create
         */
        create: function () {
            var Elm = this.parent();

            Elm.set('html', Mustache.render(template, {
                header: QUILocale.get('quiqqer/tax', 'taxtype.edit.header'),
                title : QUILocale.get('quiqqer/system', 'title'),
                group : QUILocale.get('quiqqer/tax', 'taxtype.edit.taxGroup')
            }));

            return Elm;
        },

        /**
         * event : on inject
         */
        $onInject: function () {
            var Elm         = this.getElm(),
                id          = this.getAttribute('taxTypeId'),
                TitleParent = Elm.getElement('.quiqqer-taxtype-setting-table-title'),
                Groups      = Elm.getElement('.quiqqer-taxtype-setting-table-group');

            this.$Title = new Translation({
                'group'  : 'quiqqer/tax',
                'var'    : 'taxType.' + id + '.title',
                'package': 'quiqqer/tax'
            }).imports(TitleParent);

            this.$Select = new QUISelect({
                showIcons: false,
                styles   : {
                    border: 'none',
                    width : '100%'
                }
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

                if (type.groupId !== '') {
                    this.$Select.setValue(type.groupId);
                }

                TitleParent.destroy();

                this.fireEvent('loaded');
            }.bind(this));
        },

        /**
         * Update the tax type
         *
         * @returns {Promise}
         */
        update: function () {
            return TypesHandler.updateChild(this.getAttribute('taxTypeId'), {
                group: this.$Select.getValue()
            });
        }
    });
});
