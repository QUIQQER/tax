/**
 *
 * @package package/quiqqer/
 * @author www.pcsg.de (Henning Leutz)
 *
 * @require qui/QUI
 * @require qui/classes/DOM
 * @require package/quiqqer/tax/bin/classes/TaxTypes
 *
 * @event onLoaded
 */
define('package/quiqqer/tax/bin/controls/TaxTypes', [

    'qui/QUI',
    'qui/controls/Control',
    'qui/controls/windows/Confirm',
    'controls/grid/Grid',
    'package/quiqqer/tax/bin/classes/TaxTypes',
    'package/quiqqer/tax/bin/controls/TaxTypesEdit',
    'Locale'

], function (QUI, QUIControl, QUIConfirm, Grid, TaxTypes, TaxTypesEdit, QUILocale) {
    "use strict";

    var lg      = 'quiqqer/tax',
        Handler = new TaxTypes();

    return new Class({
        Extends: QUIControl,
        Type   : 'package/quiqqer/tax/bin/controls/TaxTypes',

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
                columnModel: [{
                    header   : QUILocale.get('quiqqer/system', 'id'),
                    dataIndex: 'id',
                    dataType : 'number',
                    width    : 60
                }, {
                    header   : QUILocale.get(lg, 'tax.grid.taxtype.title'),
                    dataIndex: 'title',
                    dataType : 'string',
                    width    : 300
                }, {
                    header   : QUILocale.get(lg, 'tax.grid.taxtype.taxgroup.title'),
                    dataIndex: 'groupTitle',
                    dataType : 'string',
                    width    : 300
                }],
                buttons    : [{
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
                title      : QUILocale.get(lg, 'taxtype.window.create.title'),
                text       : QUILocale.get(lg, 'taxtype.window.create.text'),
                information: QUILocale.get(lg, 'taxtype.window.create.information'),
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

                        Win.Loader.show(
                            QUILocale.get(lg, 'taxtype.message.create')
                        );

                        Handler.createChild().then(function (childId) {

                            Win.Loader.show(
                                QUILocale.get(
                                    lg,
                                    'taxtype.message.translationvars.create'
                                )
                            );

                            var currentLang = QUILocale.getCurrent(),
                                data        = {};

                            data[currentLang] = Input.value;

                            require([
                                'package/quiqqer/translator/bin/classes/Translator'
                            ], function (Translator) {

                                new Translator().setTranslation(
                                    'quiqqer/tax',
                                    'taxType.' + childId + '.title',
                                    data
                                ).then(function () {
                                    Win.Loader.show(
                                        QUILocale.get(
                                            lg,
                                            'taxtype.message.translation.publish'
                                        )
                                    );

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
         * Update a children, opens the edit sheet
         *
         * @param {number} taxTypeId - update
         */
        updateChild: function (taxTypeId) {
            var self = this;

            this.$Panel.Loader.show();

            this.$Panel.createSheet({
                title : QUILocale.get(lg, 'taxtype.edit.title', {
                    taxTypeId: taxTypeId
                }),
                events: {
                    onShow: function (Sheet) {
                        Sheet.getContent().setStyles({
                            padding: 20
                        });

                        var Edit = new TaxTypesEdit({
                            taxTypeId: taxTypeId,
                            events   : {
                                onLoaded: function () {
                                    self.$Panel.Loader.hide();
                                }
                            }
                        });

                        Edit.inject(Sheet.getContent());

                        Sheet.addButton({
                            text     : QUILocale.get('quiqqer/system', 'save'),
                            textimage: 'icon-save',
                            events   : {
                                onClick: function () {
                                    self.$Panel.Loader.show();
                                    Edit.update().then(function () {
                                        Sheet.hide();
                                    }).then(function () {
                                        return self.$Panel.refresh();
                                    }).then(function () {
                                        self.$Panel.Loader.hide();
                                    });
                                }
                            }
                        });
                    }
                }
            }).show();
        },

        /**
         * Opens the delete dialog - delete
         *
         * @param {number} taxTypeId
         */
        deleteChild: function (taxTypeId) {
            var self = this;

            new QUIConfirm({
                title      : QUILocale.get(lg, 'taxtype.window.delete.title'),
                text       : QUILocale.get(lg, 'taxtype.window.delete.text', {
                    taxTypeId: taxTypeId
                }),
                information: QUILocale.get(lg, 'taxtype.window.delete.information', {
                    taxTypeId: taxTypeId
                }),
                icon       : 'icon-trash fa fa-trash',
                textimage  : 'icon-trash fa fa-trash',
                maxHeight  : 300,
                maxWidth   : 450,
                autoclose  : false,
                events     : {
                    onSubmit: function (Win) {
                        Win.Loader.show();
                        Handler.deleteChild(taxTypeId).then(function () {
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
