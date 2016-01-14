/**
 * Tax Entry
 * Manage all taxes
 *
 * @package package/quiqqer/tax/bin/controls/TaxEntries
 * @author www.pcsg.de (Henning Leutz)
 *
 * @require qui/QUI
 * @require qui/controls/Control
 * @require qui/controls/windows/Confirm
 * @require controls/grid/Grid
 * @require package/quiqqer/tax/bin/classes/TaxEntries
 * @require Locale
 *
 * @event onLoaded
 */
define('package/quiqqer/tax/bin/controls/TaxEntries', [

    'qui/QUI',
    'qui/controls/Control',
    'qui/controls/windows/Confirm',
    'qui/controls/buttons/Select',
    'controls/grid/Grid',
    'package/quiqqer/tax/bin/classes/TaxEntries',
    'package/quiqqer/tax/bin/classes/TaxGroups',
    'package/quiqqer/tax/bin/classes/TaxTypes',
    'package/quiqqer/areas/bin/classes/Handler',
    'Locale',

    'text!package/quiqqer/tax/bin/controls/TaxEntriesCreate.html'

], function (QUI, QUIControl, QUIConfirm, QUISelect,
             Grid, TaxEntries, TaxGroups, TaxTypes, Areas,
             QUILocale, createTemplate) {
    "use strict";

    var lg          = 'quiqqer/tax',
        Handler     = new TaxEntries(),
        TypeHandler = new TaxTypes(),
        AreaHandler = new Areas();

    return new Class({
        Extends: QUIControl,
        Type   : 'package/quiqqer/tax/bin/controls/TaxEntries',

        Binds: [
            'Panel',
            'createChild',
            'updateChild',
            'deleteChild',
            'childWindow',
            '$onInject',
            '$onCreate',
            '$onResize'
        ],

        initialize: function (options) {
            this.parent(options);

            this.$Select  = null;
            this.$current = false;

            this.addEvents({
                onInject: this.$onInject,
                onResize: this.$onResize
            });
        },

        /**
         * event : on create
         */
        create: function () {
            var self = this,
                Elm  = this.parent();

            Elm.setStyles({
                height: '100%',
                width : '100%'
            });


            var SelectContainer = new Element('div').inject(Elm);

            this.$Select = new QUISelect({
                showIcons: false,
                events   : {
                    onChange: function (value) {
                        self.loadTaxByTaxType(value);
                    }
                }
            }).inject(SelectContainer);


            var Container = new Element('div', {
                styles: {
                    paddingTop: 20,
                    width     : '100%'
                }
            }).inject(Elm);

            this.$Grid = new Grid(Container, {
                multipleSelection: true,
                columnModel      : [{
                    header   : QUILocale.get(lg, 'tax.grid.taxentries.active.title'),
                    dataIndex: 'active',
                    dataType : 'button',
                    width    : 60
                }, {
                    header   : QUILocale.get('quiqqer/system', 'id'),
                    dataIndex: 'id',
                    dataType : 'number',
                    width    : 60
                }, {
                    header   : QUILocale.get(lg, 'tax.grid.taxentries.area.title'),
                    dataIndex: 'area',
                    dataType : 'string',
                    width    : 300
                }, {
                    header   : QUILocale.get(lg, 'tax.grid.taxentries.vat.title'),
                    dataIndex: 'vat',
                    dataType : 'string',
                    width    : 100
                }, {
                    header   : QUILocale.get(lg, 'tax.grid.taxentries.euvat.title'),
                    dataIndex: 'euvat',
                    dataType : 'node',
                    width    : 300
                }],
                buttons          : [{
                    name     : 'add',
                    text     : QUILocale.get('quiqqer/system', 'add'),
                    textimage: 'icon-plus fa fa-plus',
                    events   : {
                        click: this.createChild
                    }
                }, {
                    name     : 'edit',
                    text     : QUILocale.get('quiqqer/system', 'edit'),
                    textimage: 'icon-edit fa fa-edit',
                    disabled : true,
                    events   : {
                        click: this.updateChild
                    }
                }, {
                    type: 'seperator'
                }, {
                    name     : 'delete',
                    text     : QUILocale.get('quiqqer/system', 'delete'),
                    textimage: 'icon-trash fa fa-trash',
                    disabled : true,
                    events   : {
                        click: function () {
                            this.deleteChild();
                        }.bind(this)
                    }
                }]
            });

            this.$Grid.addEvents({
                click: function () {
                    var selecteData = self.$Grid.getSelectedData(),
                        buttons     = self.$Grid.getButtons();

                    var Edit = buttons.find(function (Button) {
                        if (Button.getAttribute('name') == 'edit') {
                            return Button;
                        }
                        return false;
                    });

                    var Delete = buttons.find(function (Button) {
                        if (Button.getAttribute('name') == 'delete') {
                            return Button;
                        }
                        return false;
                    });


                    if (!selecteData.length) {
                        Edit.disable();
                        Delete.disable();
                        return;
                    }

                    Delete.enable();

                    if (selecteData.length == 1) {
                        Edit.enable();
                    } else {
                        Edit.disable();
                    }
                },

                dblclick: function () {

                }
            });

            return Elm;
        },

        /**
         * event : on inject
         */
        $onInject: function () {
            var self = this;

            TypeHandler.getList().then(function (list) {
                for (var i = 0, len = list.length; i < len; i++) {
                    self.$Select.appendChild(
                        list[i].title,
                        list[i].id
                    );
                }

                self.$current = list[0].id;

                return list[0].id;

            }).then(function (id) {
                return self.loadTaxByTaxType(id);

            }).then(function () {
                self.$Select.setValue(self.$current);
                self.resize();
                self.fireEvent('loaded');

            }).catch(function () {
                self.fireEvent('loaded');
            });
        },

        /**
         * event : on resize
         */
        $onResize: function () {
            this.$Grid.setHeight(this.getElm().getSize().y - 40);
            this.$Grid.resize();
        },

        /**
         * Refresh the data - read
         *
         * @return {Promise}
         */
        refresh: function () {
            return new Promise(function (resolve, reject) {

                var value = this.$Select.getValue();

                if (!value || value === '' || this.$current == value) {
                    resolve();
                    return;
                }

                console.log(this.$Select.getValue());


            }.bind(this));
            //
            //
            //return Handler.getList().then(function (result) {
            //    if (!result.length) {
            //        return;
            //    }
            //
            //    this.$Grid.setData({
            //        data: result
            //    });
            //}.bind(this));
        },

        /**
         * Load the data to the grid
         * @returns {Promise}
         */
        loadTaxByTaxType: function (taxTypeId) {
            return new Promise(function (resolve, reject) {
                Handler.getTaxByType(taxTypeId).then(function (result) {
                    if (!result.length) {
                        resolve();
                        return;
                    }

                    var i, len, entry;

                    for (i = 0, len = result.length; i < len; i++) {

                        entry = result[i];

                        if (entry.active) {
                            result[i].active = {
                                icon : 'icon-ok'
                            };
                        } else {
                            result[i].active = {
                                icon : 'icon-remove'
                            };
                        }

                        if (entry.euvat) {
                            result[i].euvat = new Element('span', {
                                'class' : 'icon-ok'
                            });
                        } else {
                            result[i].euvat = new Element('span', {
                                'class' : 'icon-remove'
                            });
                        }
                    }

                    this.$Grid.setData({
                        data: result
                    });

                    resolve();

                }.bind(this), reject);
            }.bind(this));
        },

        /**
         * opens the add dialog - create
         */
        createChild: function () {
            this.childWindow();
        },

        /**
         *
         * @param {number} taxEntryId - update
         */
        updateChild: function (taxEntryId) {
            this.childWindow(taxEntryId);
        },

        /**
         * Opens the child window
         *
         * @param {number} [taxEntryId] - update
         */
        childWindow: function (taxEntryId) {
            var self  = this,
                title = QUILocale.get('', '');

            if (typeof taxEntryId !== 'undefined') {
                title = QUILocale.get('', '');
            }

            new QUIConfirm({
                title    : 'Neuen Steuersatz anlegen',
                icon     : 'icon-plus fa fa-plus',
                maxHeight: 600,
                maxWidth : 800,
                autoclose: false,
                events   : {
                    onOpen: function (Win) {

                        var Content = Win.getContent();

                        Win.Loader.show();

                        Content.set('html', createTemplate);

                        var Select = Content.getElement('[name="area"]'),
                            Data   = Promise.resolve({});

                        if (typeof taxEntryId !== 'undefined') {
                            Data = Handler.get(taxEntryId);
                        }

                        Promise.all([
                            Handler.getTaxByType(self.$Select.getValue()),
                            AreaHandler.getList(),
                            Data
                        ]).then(function (data) {
                            var allEntries = data[0],
                                areas      = data[1],
                                taxData    = data[2];

                            for (var i = 0, len = areas.length; i < len; i++) {
                                new Element('option', {
                                    value: areas[i].id,
                                    html : QUILocale.get(
                                        areas[i].title[0],
                                        areas[i].title[1]
                                    )
                                }).inject(Select);
                            }

                            Win.Loader.hide();
                        });
                    },

                    onSubmit: function (Win) {
                        // fields
                        var Content = Win.getContent(),
                            Vat     = Content.getElement('[name="vat"]'),
                            Area    = Content.getElement('[name="area"]'),
                            EUvat   = Content.getElement('[name="eu_vat"]');

                        var data = {
                            Vat      : Vat.value,
                            areaId   : Area.value,
                            euvat    : EUvat.checked ? 1 : 0,
                            taxTypeId: self.$Select.getValue()
                        };

                        if (typeof taxEntryId === 'undefined') {

                            Handler.createChild(
                                self.$Select.getValue()
                            ).then(function (newId) {
                                return Handler.updateChild(newId, data);

                            }).then(function () {
                                return self.refresh();

                            }).then(function () {
                                Win.close();
                            });

                            return;
                        }


                        Handler.updateChild(taxEntryId, data).then(function () {
                            return self.refresh();

                        }).then(function () {
                            Win.close();
                        });
                    }
                }
            }).open();
        },

        /**
         * Opens the delete dialog - delete
         *
         * @param {number} taxEntryId
         */
        deleteChild: function (taxEntryId) {
            var self = this;

            new QUIConfirm({
                title      : 'Steuergruppe löschen',
                text       : 'Möchten Sie die wirklich Steuergruppe löschen?',
                information: 'Die Steuergruppe ist nicht wieder herstellbar und alle Beziehungen gehen verloren.',
                icon       : 'icon-trash fa fa-trash',
                textimage  : 'icon-trash fa fa-trash',
                maxHeight  : 300,
                maxWidth   : 450,
                autoclose  : false,
                events     : {
                    onSubmit: function (Win) {
                        Win.Loader.show();
                        Handler.deleteChild(taxEntryId).then(function () {
                            return self.refresh();
                        }).then(function () {
                            Win.hide();
                        });
                    }
                }
            });
        }
    });
});
