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
define('package/quiqqer/tax/bin/controls/TaxGroups', [

    'qui/QUI',
    'qui/controls/Control',
    'controls/grid/Grid',
    'package/quiqqer/tax/bin/classes/TaxGroups',
    'Locale'

], function (QUI, QUIControl, Grid, TaxGroups, QUILocale) {
    "use strict";

    var lg      = 'quiqqer/tax',
        Handler = new TaxGroups();

    return new Class({
        Extends: QUIControl,
        Type   : 'package/quiqqer/tax/bin/controls/TaxGroup',

        Binds: [
            'Panel',
            '$onInject',
            '$onCreate',
            '$onResize'
        ],

        initialize: function (options) {
            this.parent(options);

            this.addEvents({
                onInject: this.$onInject,
                onResize: this.$onResize
            });
        },

        /**
         * event : on create
         */
        create: function () {
            var Elm = this.parent();

            var Container = new Element('div', {
                styles: {
                    height: '100%',
                    width : '100%'
                }
            }).inject(Elm);

            Elm.setStyles({
                height: '100%',
                width : '100%'
            });

            this.$Grid = new Grid(Container, {
                multipleSelection: true,
                columnModel      : [{
                    header   : QUILocale.get('quiqqer/system', 'id'),
                    dataIndex: 'id',
                    dataType : 'number',
                    width    : 60
                }, {
                    header   : QUILocale.get(lg, 'tax.grid.taxgroup.title'),
                    dataIndex: 'title',
                    dataType : 'string',
                    width    : 300
                }],
                buttons          : [{
                    text: QUILocale.get('quiqqer/system', 'add'),
                    textimage: 'icon-plus fa fa-plus'
                }, {
                    text: QUILocale.get('quiqqer/system', 'delete'),
                    textimage: 'icon-trash fa fa-trash'
                }]
            });

            return Elm;
        },

        /**
         *
         */
        $onInject: function () {
            Handler.getList().then(function (result) {
                console.log(result);

                this.resize();

                if (!result.length) {


                    this.fireEvent('loaded');
                    return;
                }


                this.fireEvent('loaded');
            }.bind(this), function () {
                this.fireEvent('loaded');
            }.bind(this));
        },

        /**
         *
         */
        $onResize: function () {
            this.$Grid.setHeight(this.getElm().getSize().y);
            this.$Grid.resize();
        }
    });
});
