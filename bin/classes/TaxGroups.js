/**
 *
 * @package package/quiqqer/
 * @author www.pcsg.de (Henning Leutz)
 *
 * @require qui/QUI
 * @require qui/classes/DOM
 * @require Ajax
 */
define('package/quiqqer/tax/bin/classes/TaxGroups', [

    'qui/QUI',
    'qui/classes/DOM',
    'Ajax'

], function (QUI, QDOM, QUIAjax) {
    "use strict";

    return new Class({
        Extends: QDOM,
        Type   : 'package/quiqqer/tax/bin/classes/TaxGroup',

        initialize: function (options) {
            this.parent(options);
        },

        /**
         * Return all tax groups
         *
         * @returns {Promise}
         */
        getList: function (ids) {
            return new Promise(function (resolve, reject) {
                QUIAjax.get(
                    'package_quiqqer_tax_ajax_groups_getList',
                    resolve, {
                        'package': 'quiqqer/tax',
                        onError  : reject,
                        ids      : JSON.decode(ids || [])
                    });
            });
        },

        /**
         * Return the tax group data
         *
         * @param {Number} taxGroupId
         * @returns {Promise}
         */
        get: function (taxGroupId) {
            return new Promise(function (resolve, reject) {
                QUIAjax.get(
                    'package_quiqqer_tax_ajax_groups_get',
                    resolve, {
                        'package' : 'quiqqer/tax',
                        onError   : reject,
                        taxGroupId: taxGroupId
                    });
            });
        },

        /**
         * Create a new tax group
         *
         * @returns {Promise}
         */
        createChild: function () {
            return new Promise(function (resolve, reject) {
                QUIAjax.post(
                    'package_quiqqer_tax_ajax_groups_create',
                    resolve, {
                        'package': 'quiqqer/tax',
                        onError  : reject
                    });
            });
        },

        /**
         * Create a new tax group
         *
         * @param {Number} taxGroupId
         * @param {Object} data
         *
         * @returns {Promise}
         */
        updateChild: function (taxGroupId, data) {
            return new Promise(function (resolve, reject) {
                QUIAjax.post(
                    'package_quiqqer_tax_ajax_groups_create',
                    resolve, {
                        'package' : 'quiqqer/tax',
                        taxGroupId: taxGroupId,
                        data      : JSON.encode(data),
                        onError   : reject
                    });
            });
        },

        /**
         * Delete a tax group
         *
         * @param {Number} taxGroupId
         */
        deleteChild: function (taxGroupId) {
            return new Promise(function (resolve, reject) {
                QUIAjax.post(
                    'package_quiqqer_tax_ajax_groups_delete',
                    resolve, {
                        'package' : 'quiqqer/tax',
                        taxGroupId: taxGroupId,
                        onError   : reject
                    });
            });
        }
    });
});
