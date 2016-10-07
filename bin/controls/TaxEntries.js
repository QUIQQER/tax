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
 * @require qui/controls/buttons/Select
 * @require controls/grid/Grid
 * @require package/quiqqer/tax/bin/classes/TaxEntries
 * @require package/quiqqer/tax/bin/classes/TaxGroups
 * @require package/quiqqer/tax/bin/classes/TaxTypes
 * @require package/quiqqer/areas/bin/classes/Handler
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

    var lg = 'quiqqer/tax',
        Handler = new TaxEntries(),
        TypeHandler = new TaxTypes(),
        AreaHandler = new Areas();

    return new Class({
        Extends: QUIControl,
        Type: 'package/quiqqer/tax/bin/controls/TaxEntries',

        Binds: [
            'Panel',
            'createChild',
            'updateChild',
            'deleteChild',
            'childWindow',
            'resize',
            'toggleTaxEntryStatus',
            'activatetaxEntry',
            'deactivatetaxEntry',
            '$onInject',
            '$onCreate',
            '$onResize'
        ],

        initialize: function (options) {
            this.parent(options);

            this.$Select = null;
            this.$current = false;

            this.addEvents({
                onInject: this.$onInject,
                onResize: this.$onResize,
                onDestroy: function () {
                    if (this.$Grid) {
                        this.$Grid.destroy();
                    }
                }.bind(this)
            });
        },

        /**
         * event : on create
         */
        create: function () {
            var self = this,
                Elm = this.parent();

            Elm.setStyles({
                height: '100%',
                opacity: 0,
                width: '100%'
            });


            var SelectContainer = new Element('div', {
                styles: {
                    'float': 'left',
                    paddingBottom: 20,
                    width: '100%'
                }
            }).inject(Elm);

            this.$Select = new QUISelect({
                showIcons: false,
                events: {
                    onChange: function (value) {
                        self.loadTaxByTaxType(value);
                    }
                }
            }).inject(SelectContainer);


            var Container = new Element('div', {
                styles: {
                    width: '100%'
                }
            }).inject(Elm);

            this.$Grid = new Grid(Container, {
                multipleSelection: false,
                columnModel: [{
                    header: QUILocale.get(lg, 'tax.grid.taxentries.active.title'),
                    dataIndex: 'activeButton',
                    dataType: 'button',
                    width: 60
                }, {
                    header: QUILocale.get('quiqqer/system', 'id'),
                    dataIndex: 'id',
                    dataType: 'number',
                    width: 60
                }, {
                    header: QUILocale.get(lg, 'tax.grid.taxentries.area.title'),
                    dataIndex: 'area',
                    dataType: 'string',
                    width: 200
                }, {
                    header: QUILocale.get(lg, 'tax.grid.taxentries.vat.title'),
                    dataIndex: 'vat',
                    dataType: 'string',
                    width: 80
                }, {
                    header: QUILocale.get(lg, 'tax.grid.taxentries.euvat.title'),
                    dataIndex: 'euvatIcon',
                    dataType: 'node',
                    width: 60
                }],
                buttons: [{
                    name: 'add',
                    text: QUILocale.get('quiqqer/system', 'add'),
                    textimage: 'fa fa-plus',
                    events: {
                        click: this.createChild
                    }
                }, {
                    name: 'edit',
                    text: QUILocale.get('quiqqer/system', 'edit'),
                    textimage: 'fa fa-edit',
                    disabled: true,
                    events: {
                        click: function () {
                            this.updateChild(
                                this.$Grid.getSelectedData()[0].id
                            );
                        }.bind(this)
                    }
                }, {
                    type: 'seperator'
                }, {
                    name: 'delete',
                    text: QUILocale.get('quiqqer/system', 'delete'),
                    textimage: 'fa fa-trash',
                    disabled: true,
                    events: {
                        click: function () {
                            this.deleteChild(
                                this.$Grid.getSelectedData()[0].id
                            );
                        }.bind(this)
                    }
                }]
            });

            this.$Grid.addEvents({
                onClick: function () {
                    var selecteData = self.$Grid.getSelectedData(),
                        buttons = self.$Grid.getButtons();

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

                    if (selecteData.length == 1) {
                        Delete.enable();
                        Edit.enable();
                        return;
                    }

                    Delete.disable();
                    Edit.disable();
                },

                onDblClick: function () {
                    this.updateChild(
                        this.$Grid.getSelectedData()[0].id
                    );
                }.bind(this)
            });

            return Elm;
        },

        /**
         * event : on inject
         */
        $onInject: function () {
            var self = this;

            this.refresh().then(function () {
                self.$Select.setValue(self.$current);
                self.resize();

            }).then(function () {

                return new Promise(function (resolve) {
                    moofx(self.getElm()).animate({
                        opacity: 1
                    }, {
                        duration: 200,
                        callback: function () {
                            self.fireEvent('loaded');
                            resolve();
                        }
                    });
                });
            });
        },

        /**
         * resize
         *
         * @return {Promise}
         */
        resize: function () {
            var self = this;

            return new Promise(function (resolve) {
                self.$Grid.setHeight(
                    self.getElm().getSize().y - 60
                ).then(function () {
                    self.$Grid.resize();
                    resolve();
                });
            });
        },

        /**
         * Refresh the data - read
         *
         * @return {Promise}
         */
        refresh: function () {
            return new Promise(function (resolve, reject) {
                var buttons = this.$Grid.getButtons();

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

                if (Edit) {
                    Edit.disable();
                }

                if (Delete) {
                    Delete.disable();
                }


                var self = this,
                    value = this.$Select.getValue();

                return new Promise(function (res2) {
                    if (value && value !== '') {
                        return this.loadTaxByTaxType(value);
                    }

                    TypeHandler.getList().then(function (list) {
                        if (!list.length) {
                            return Promise.resolve();
                        }

                        self.$Select.clear();

                        for (var i = 0, len = list.length; i < len; i++) {
                            self.$Select.appendChild(
                                list[i].title,
                                list[i].id
                            );
                        }

                        self.$current = list[0].id;
                        self.$Select.setValue(self.$current);

                        return self.loadTaxByTaxType(list[0].id);

                    }).then(res2, reject);
                }).then(resolve, reject);


            }.bind(this));
        },

        /**
         * Load the data to the grid
         * @returns {Promise}
         */
        loadTaxByTaxType: function (taxTypeId) {
            return new Promise(function (resolve, reject) {
                Handler.getTaxByType(taxTypeId).then(function (result) {
                    if (!result.length) {
                        this.$Grid.setData({
                            data: result
                        });

                        resolve();
                        return;
                    }

                    var i, len, entry;

                    for (i = 0, len = result.length; i < len; i++) {
                        entry = result[i];

                        if (parseInt(entry.active) === 1) {
                            result[i].activeButton = {
                                icon: 'fa fa-check',
                                taxId: entry.id,
                                styles: {
                                    lineHeight: 16
                                },
                                events: {
                                    onClick: this.toggleTaxEntryStatus
                                }
                            };
                        } else {
                            result[i].activeButton = {
                                icon: 'fa fa-remove',
                                taxId: entry.id,
                                styles: {
                                    lineHeight: 16
                                },
                                events: {
                                    onClick: this.toggleTaxEntryStatus
                                }
                            };
                        }

                        if (parseInt(entry.euvat)) {
                            result[i].euvatIcon = new Element('span', {
                                'class': 'fa fa-check'
                            });
                        } else {
                            result[i].euvatIcon = new Element('span', {
                                'class': 'fa fa-remove'
                            });
                        }
                    }

                    this.$Grid.setData({
                        data: result
                    });

                    this.$Grid.sort(2, 'DESC');

                    resolve();

                }.bind(this), reject);
            }.bind(this));
        },

        /**
         * Toggle the tax status
         *
         * @param {Object} Btn
         */
        toggleTaxEntryStatus: function (Btn) {

            Btn.setAttribute('icon', 'fa fa-spinner fa-spin');

            Handler.toggleStatus(
                Btn.getAttribute('taxId')
            ).then(function (status) {
                this.$setTaxEntryButtonStatus(Btn, status);
            }.bind(this));
        },

        /**
         * Activate the tax
         *
         * @param {Object} Btn
         */
        activatetaxEntry: function (Btn) {
            Btn.setAttribute('icon', 'fa fa-spinner fa-spin');

            Handler.activate(
                Btn.getAttribute('taxId')
            ).then(function (status) {
                this.$setTaxEntryButtonStatus(Btn, status);
            }.bind(this));
        },

        /**
         * Deactivate the tax
         *
         * @param {Object} Btn
         */
        deactivatetaxEntry: function (Btn) {
            Btn.setAttribute('icon', 'fa fa-spinner fa-spin');

            Handler.activate(
                Btn.getAttribute('taxId')
            ).then(function (status) {
                this.$setTaxEntryButtonStatus(Btn, status);
            }.bind(this));
        },

        /**
         *
         * @param {Object} Btn
         * @param {Boolean|Number} status
         */
        $setTaxEntryButtonStatus: function (Btn, status) {
            if (status) {
                Btn.setAttribute('icon', 'fa fa-check');
                return;
            }

            Btn.setAttribute('icon', 'fa fa-remove');
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
            var self = this,
                title = QUILocale.get(lg, 'tax.window.create.title');

            if (typeof taxEntryId !== 'undefined') {
                title = QUILocale.get(lg, 'tax.window.update.title');
            }

            new QUIConfirm({
                title: title,
                icon: 'fa fa-plus',
                maxHeight: 600,
                maxWidth: 800,
                autoclose: false,
                events: {
                    onOpen: function (Win) {

                        var Content = Win.getContent();

                        Win.Loader.show();

                        Content.set('html', createTemplate);

                        var Select = Content.getElement('[name="area"]'),
                            Data = Promise.resolve(false);

                        if (typeof taxEntryId !== 'undefined') {
                            Data = Handler.get(taxEntryId);
                        }

                        Promise.all([
                            Handler.getTaxByType(self.$Select.getValue()),
                            AreaHandler.getList(),
                            Data
                        ]).then(function (data) {
                            var allEntries = data[0],
                                areas = data[1],
                                taxData = data[2];

                            if (!taxData) {
                                var usedAreas = allEntries.map(function (o) {
                                    return o.areaId;
                                }).unique();

                                for (var i = 0, len = areas.length; i < len; i++) {
                                    if (usedAreas.contains(areas[i].id)) {
                                        continue;
                                    }

                                    new Element('option', {
                                        value: areas[i].id,
                                        html: QUILocale.get(
                                            areas[i].title[0],
                                            areas[i].title[1]
                                        )
                                    }).inject(Select);
                                }
                            } else {
                                Win.setAttribute('areaId', taxData.areaId);

                                // areaid is defined
                                new Element('div', {
                                    text: QUILocale.get(
                                        'quiqqer/areas',
                                        'area.' + taxData.areaId + '.title'
                                    )
                                }).replaces(Select);
                            }

                            if (taxData) {
                                var Vat = Content.getElement('[name="vat"]');
                                var Area = Content.getElement('[name="area"]');
                                var EuVat = Content.getElement('[name="eu_vat"]');

                                if (parseInt(taxData.euvat)) {
                                    EuVat.checked = true;
                                }

                                if (Area) {
                                    Area.value = taxData.areaId || '';
                                }

                                Vat.value = taxData.vat || '';
                            }

                            Win.Loader.hide();
                        });
                    },

                    onSubmit: function (Win) {
                        // fields
                        var Content = Win.getContent(),
                            Vat = Content.getElement('[name="vat"]'),
                            Area = Content.getElement('[name="area"]'),
                            EUvat = Content.getElement('[name="eu_vat"]');

                        var data = {
                            vat: Vat.value,
                            euvat: EUvat.checked ? 1 : 0,
                            taxTypeId: self.$Select.getValue()
                        };

                        if (Area) {
                            data.areaId = Area.value;
                        } else {
                            data.areaId = Win.getAttribute('areaId');
                        }

                        if (typeof taxEntryId === 'undefined') {

                            Handler.createChild(
                                self.$Select.getValue(),
                                Area.value
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
                title: QUILocale.get(lg, 'tax.window.delete.title'),
                text: QUILocale.get(lg, 'tax.window.delete.text', {
                    id: taxEntryId
                }),
                information: QUILocale.get(lg, 'tax.window.delete.information', {
                    id: taxEntryId
                }),
                icon: 'fa fa-trash',
                textimage: 'fa fa-trash',
                maxHeight: 300,
                maxWidth: 450,
                autoclose: false,
                events: {
                    onSubmit: function (Win) {
                        Win.Loader.show();
                        Handler.deleteChild(taxEntryId).then(function () {
                            return self.refresh();
                        }).then(function () {
                            Win.close();
                        });
                    }
                }
            }).open();
        }
    });
});
