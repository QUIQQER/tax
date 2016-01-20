/**
 *
 * @package package/quiqqer/
 * @author www.pcsg.de (Henning Leutz)
 *
 * @require qui/QUI
 * @require qui/classes/DOM
 * @require package/quiqqer/tax/bin/classes/TaxGroups
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
    'package/quiqqer/translator/bin/controls/VariableTranslation',

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

            this.$Grid    = null;
            this.$Handler = new Handler();


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

            new Translation({
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
                columnModel: [{
                    header   : QUILocale.get('quiqqer/tax', ''),
                    dataIndex: 'type',
                    dataType : 'string',
                    width    : 600
                }],
                buttons    : [{
                    name  : 'up',
                    text  : QUILocale.get('quiqqer/system', 'up'),
                    events: {
                        onClick: function () {
                            self.$Grid.up();
                        }
                    }
                }, {
                    name  : 'down',
                    text  : QUILocale.get('quiqqer/system', 'down'),
                    events: {
                        onClick: function () {
                            self.$Grid.down();
                        }
                    }
                }]
            });

            this.$Grid.setHeight(200);
            this.$Grid.setWidth(this.getElm().getSize().x - 20);
            this.$Grid.resize();

            this.$Handler.get(
                this.getAttribute('taxGroupId')
            ).then(function (data) {
                if (data.taxtypes === '') {
                    return;
                }

                var types = data.taxtypes.split(',');

                return new Promise(function (resolve, reject) {
                    require([
                        'package/quiqqer/tax/bin/classes/TaxTypes'
                    ], function (TaxTypes) {
                        new TaxTypes().getList(types).then(function () {

                            // @todo insert list to grids

                            resolve();
                        }, reject);
                    });
                });

            }).then(function () {
                self.fireEvent('loaded');
            });
        },

        /**
         * Save the TaxGroup
         *
         * @returns {Promise}
         */
        update: function () {
            return new Promise(function (resolve, reject) {
                resolve();
            });
        }
    });
});
