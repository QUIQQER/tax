/**
 * Tax groups management
 *
 * @package package/quiqqer/tax/bin/controls/TaxGroups
 * @author www.pcsg.de (Henning Leutz)
 *
 * @require qui/QUI
 * @require qui/controls/Control
 * @require qui/controls/windows/Confirm
 * @require controls/grid/Grid
 * @require package/quiqqer/tax/bin/classes/TaxGroups
 * @require package/quiqqer/tax/bin/controls/TaxGroupsEdit
 * @require Locale
 *
 * @event onLoaded
 */
define('package/quiqqer/tax/bin/controls/TaxGroups', [

    'qui/QUI',
    'qui/controls/Control',
    'qui/controls/windows/Confirm',
    'controls/grid/Grid',
    'package/quiqqer/tax/bin/classes/TaxGroups',
    'package/quiqqer/tax/bin/controls/TaxGroupsEdit',
    'Locale'

], function (QUI,
             QUIControl,
             QUIConfirm,
             Grid,
             TaxGroups,
             TaxGroupsEdit,
             QUILocale) {

    "use strict";

    var lg      = 'quiqqer/tax',
        Handler = new TaxGroups();

    return new Class({
        Extends: QUIControl,
        Type   : 'package/quiqqer/tax/bin/controls/TaxGroup',

        Binds: [
            'Panel',
            'createChild',
            'updateChild',
            'deleteChild',
            '$onInject',
            '$onCreate',
            '$onResize'
        ],

        initialize: function (options) {
            this.parent(options);

            this.$Panel = this.getAttribute('Panel');

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
                }, {
                    header   : QUILocale.get(lg, 'tax.grid.taxgroup.taxtypes.title'),
                    dataIndex: 'taxTypeNames',
                    dataType : 'string',
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
                            self.deleteChild(
                                self.$Grid.getSelectedData()[0].id
                            );
                        }
                    }
                }]
            });

            this.$Grid.addEvents({
                onClick: function () {
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


                    if (selecteData.length == 1) {
                        Edit.enable();
                        Delete.enable();
                        return;
                    }

                    Edit.disable();
                    Delete.disable();
                },

                onDblClick: function (event) {
                    self.updateChild(
                        self.$Grid.getDataByRow(event.row).id
                    );
                }
            });

            return Elm;
        },

        /**
         * event : on inject
         */
        $onInject: function () {
            this.refresh().then(function () {
                this.resize();
                this.fireEvent('loaded');
            }.bind(this), function () {
                this.resize();
                this.fireEvent('loaded');
            }.bind(this));
        },

        /**
         * event : on resize
         */
        $onResize: function () {
            this.$Grid.setHeight(this.getElm().getSize().y);
            this.$Grid.resize();
        },

        /**
         * Refresh the data - read
         *
         * @return {Promise}
         */
        refresh: function () {
            return Handler.getList().then(function (result) {

                this.$Grid.setData({
                    data: result
                });

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

                Edit.disable();
                Delete.disable();

            }.bind(this));
        },

        /**
         * opens the add dialog - create
         */
        createChild: function () {
            var self = this;

            new QUIConfirm({
                title      : QUILocale.get(lg, 'taxgroup.window.create.title'),
                text       : QUILocale.get(lg, 'taxgroup.window.create.text'),
                information: QUILocale.get(lg, 'taxgroup.window.create.information'),
                icon       : 'icon-plus fa fa-plus',
                textimage  : 'icon-plus fa fa-plus',
                maxHeight  : 300,
                maxWidth   : 450,
                autoclose  : false,
                events     : {
                    onOpen: function (Win) {
                        var Content     = Win.getContent(),
                            Information = Content.getElement('.information');

                        var Input = new Element('input', {
                            events: {
                                keyup: function (event) {
                                    if (event.key === 'enter') {
                                        Win.submit();
                                    }
                                }
                            },
                            styles: {
                                marginTop: 10
                            }
                        }).inject(Information, 'after');

                        Input.focus();
                    },

                    onSubmit: function (Win) {
                        var Content = Win.getContent(),
                            Input   = Content.getElement('input');

                        if (Input.value === '') {
                            return;
                        }

                        Win.Loader.show();

                        Handler.createChild().then(function (childId) {

                            var currentLang = QUILocale.getCurrent(),
                                data        = {};

                            data[currentLang] = Input.value;

                            require([
                                'package/quiqqer/translator/bin/classes/Translator'
                            ], function (Translator) {

                                new Translator().setTranslation(
                                    'quiqqer/tax',
                                    'taxGroup.' + childId + '.title',
                                    data
                                ).then(function () {
                                    return new Translator().publish();

                                }).then(function () {
                                    return new Translator().refreshLocale();

                                }).then(function () {
                                    return self.refresh();

                                }).then(function () {

                                    self.updateChild(childId);
                                    Win.close();
                                });

                            });
                        });
                    }
                }
            }).open();
        },

        /**
         *
         * @param {number} taxGroupId - update
         */
        updateChild: function (taxGroupId) {
            var self = this;

            this.$Panel.Loader.show();

            this.$Panel.createSheet({
                title : QUILocale.get(lg, 'taxgroup.edit.title', {
                    taxGroupId: taxGroupId
                }),
                events: {
                    onShow: function (Sheet) {
                        Sheet.getContent().setStyles({
                            padding: 20
                        });

                        Sheet.Edit = new TaxGroupsEdit({
                            taxGroupId: taxGroupId,
                            events    : {
                                onLoaded: function () {
                                    self.$Panel.Loader.hide();
                                }
                            }
                        });

                        Sheet.Edit.inject(Sheet.getContent());

                        Sheet.addButton({
                            text     : QUILocale.get('quiqqer/system', 'save'),
                            textimage: 'icon-save fa fa-save',
                            events   : {
                                onClick: function () {
                                    self.$Panel.Loader.show();


                                    Edit.update().then(function () {
                                        return Sheet.hide();
                                    }).then(function () {
                                        self.$Panel.Loader.hide();
                                    });
                                }
                            }
                        });
                    },

                    onClose: function (Sheet) {
                        Sheet.Edit.destroy();
                        Sheet.destroy();
                    },

                    onResize: function (Sheet) {
                        Sheet.Edit.resize();
                    }
                }
            }).show();
        },

        /**
         * Opens the delete dialog - delete
         *
         * @param {number} taxGroupId
         */
        deleteChild: function (taxGroupId) {
            var self = this;

            new QUIConfirm({
                title      : QUILocale.get(lg, 'taxgroup.window.delete.title'),
                text       : QUILocale.get(lg, 'taxgroup.window.delete.text', {
                    taxGroupId: taxGroupId
                }),
                information: QUILocale.get(lg, 'taxgroup.window.delete.information', {
                    taxGroupId: taxGroupId
                }),
                icon       : 'icon-trash fa fa-trash',
                textimage  : 'icon-trash fa fa-trash',
                maxHeight  : 300,
                maxWidth   : 450,
                autoclose  : false,
                events     : {
                    onSubmit: function (Win) {
                        Win.Loader.show();
                        Handler.deleteChild(taxGroupId).then(function () {
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
