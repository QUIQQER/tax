/**
 * @module package/quiqqer/tax/bin/controls/TaxGroupsEdit
 * @author www.pcsg.de (Henning Leutz)
 *
 * @event onLoaded
 */
define('package/quiqqer/tax/bin/controls/TaxGroupsEdit', [

    'qui/QUI',
    'qui/controls/Control',
    'Locale',
    'Ajax',
    'controls/grid/Grid',
    'package/quiqqer/tax/bin/classes/TaxGroups',
    'package/quiqqer/translator/bin/controls/Update',

    'text!package/quiqqer/tax/bin/controls/TaxGroupsEdit.html',
    'css!package/quiqqer/tax/bin/controls/TaxGroupsEdit.css'

], function (QUI, QUIControl, QUILocale, QUIAjax,
             Grid, Handler, Translation, template) {
    "use strict";

    return new Class({
        Extends: QUIControl,
        Type   : 'package/quiqqer/tax/bin/controls/TaxGroupsEdit',

        Binds: [
            '$onInject'
        ],

        options: {
            taxGroupId: false
        },

        initialize: function (options) {
            this.parent(options);

            this.$Grid       = null;
            this.$Translator = null;
            this.$Handler    = new Handler();


            this.addEvents({
                onInject: this.$onInject
            });
        },

        /**
         * resize control
         */
        resize: function () {
            this.$Grid.setWidth(this.getElm().getSize().x - 20);
            this.$Grid.resize();
        },

        /**
         * Create the DOMNode Element
         *
         * @return {HTMLDivElement}
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
            var self = this;

            this.$Translator = new Translation({
                'group': 'quiqqer/tax',
                'var'  : 'taxGroup.' + this.getAttribute('taxGroupId') + '.title'
            }).inject(
                this.getElm().getElement('.quiqqer-taxgroup-setting-table-title')
            );


            var Container = new Element('div', {
                styles: {
                    'float' : 'left',
                    overflow: 'hidden',
                    width   : '100%'
                }
            }).inject(
                this.getElm().getElement(
                    '.quiqqer-taxgroup-setting-table-taxtypes'
                )
            );

            // sort grid
            var GridContainer = new Element('div').inject(Container);

            this.$Grid = new Grid(GridContainer, {
                width      : 100,
                serverSort : true,
                sortHeader : false,
                columnModel: [{
                    header   : QUILocale.get('quiqqer/system', 'id'),
                    dataIndex: 'id',
                    dataType : 'numeric',
                    width    : 60
                }, {
                    header   : QUILocale.get('quiqqer/tax', 'panel.category.taxtypes.text'),
                    dataIndex: 'title',
                    dataType : 'string',
                    width    : 600
                }],
                buttons    : [{
                    name  : 'up',
                    text  : QUILocale.get('quiqqer/system', 'up'),
                    events: {
                        onClick: function () {
                            self.$Grid.moveup();
                        }
                    }
                }, {
                    name  : 'down',
                    text  : QUILocale.get('quiqqer/system', 'down'),
                    events: {
                        onClick: function () {
                            self.$Grid.movedown();
                        }
                    }
                }]
            });

            this.$Grid.setHeight(200);
            this.$Grid.setWidth(this.getElm().getSize().x - 20);
            this.$Grid.resize();

            this.$Handler.getTaxTypesFromGroup(this.getAttribute('taxGroupId')).then(function (result) {

                self.$Grid.setData({
                    data: result
                });

            }).then(function () {
                self.fireEvent('loaded');

            }).catch(function (error) {
                console.error(error);
                self.fireEvent('loaded');
            });
        },

        /**
         * Save the TaxGroup
         *
         * @returns {Promise}
         */
        update: function () {
            var taxtTypeIds = this.$Grid.getData().map(function (entry) {
                return entry.id;
            });

            return this.$Translator.save().then(function () {
                return this.$Handler.updateChild(
                    this.getAttribute('taxGroupId'),
                    taxtTypeIds
                );
            }.bind(this));
        }
    });
});
