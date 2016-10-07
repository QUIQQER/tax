/**
 * Tax management
 *
 * @package package/quiqqer/tax/bin/classes/TaxEntries
 * @author www.pcsg.de (Henning Leutz)
 *
 * @require qui/QUI
 * @require qui/classes/DOM
 * @requrie Ajax
 */
define('package/quiqqer/tax/bin/classes/TaxEntries', [

    'qui/QUI',
    'qui/classes/DOM',
    'Ajax'

], function (QUI, QDOM, QUIAjax) {
    "use strict";

    return new Class({
        Extends: QDOM,
        Type: 'package/quiqqer/tax/bin/classes/TaxEntries',

        initialize: function (options) {
            this.parent(options);
        },

        /**
         * Return all tax groups
         *
         * @returns {Promise}
         */
        getList: function () {
            return new Promise(function (resolve, reject) {
                QUIAjax.get(
                    'package_quiqqer_tax_ajax_entries_getList',
                    resolve, {
                        'package': 'quiqqer/tax',
                        onError: reject
                    });
            });
        },

        /**
         * Return the tax group data
         *
         * @param {Number} taxId
         * @returns {Promise}
         */
        get: function (taxId) {
            return new Promise(function (resolve, reject) {
                QUIAjax.get(
                    'package_quiqqer_tax_ajax_entries_get',
                    resolve, {
                        'package': 'quiqqer/tax',
                        onError: reject,
                        taxId: taxId
                    });
            });
        },

        /**
         * Return all tax entries from the tax type
         *
         * @param {Number} taxTypeId
         */
        getTaxByType: function (taxTypeId) {
            return new Promise(function (resolve, reject) {
                QUIAjax.get(
                    'package_quiqqer_tax_ajax_entries_getTaxByType',
                    resolve, {
                        'package': 'quiqqer/tax',
                        onError: reject,
                        taxTypeId: taxTypeId
                    });
            });
        },

        /**
         * Create a new tax group
         *
         * @param {Number} taxTypeId
         * @param {Number} areaId
         * @returns {Promise}
         */
        createChild: function (taxTypeId, areaId) {
            return new Promise(function (resolve, reject) {
                if (typeof taxTypeId === 'undefined') {
                    reject('taxTypeId missing');
                    return;
                }

                QUIAjax.post(
                    'package_quiqqer_tax_ajax_entries_create',
                    resolve, {
                        'package': 'quiqqer/tax',
                        onError: reject,
                        taxTypeId: taxTypeId,
                        areaId: areaId
                    });
            });
        },

        /**
         * Create a new tax group
         *
         * @param {Number} taxId
         * @param {Object} data
         *
         * @returns {Promise}
         */
        updateChild: function (taxId, data) {
            return new Promise(function (resolve, reject) {
                QUIAjax.post(
                    'package_quiqqer_tax_ajax_entries_update',
                    resolve, {
                        'package': 'quiqqer/tax',
                        taxId: taxId,
                        data: JSON.encode(data),
                        onError: reject
                    });
            });
        },

        /**
         * Delete a tax group
         *
         * @param {Number} taxId
         */
        deleteChild: function (taxId) {
            return new Promise(function (resolve, reject) {
                QUIAjax.post(
                    'package_quiqqer_tax_ajax_entries_delete',
                    resolve, {
                        'package': 'quiqqer/tax',
                        taxId: taxId,
                        onError: reject
                    });
            });
        },

        /**
         * Toggle the status from the Tax Entry
         *
         * @param {Number} taxId
         * @returns {Promise}
         */
        toggleStatus: function (taxId) {
            return new Promise(function (resolve, reject) {
                QUIAjax.post(
                    'package_quiqqer_tax_ajax_entries_toggle',
                    resolve, {
                        'package': 'quiqqer/tax',
                        taxId: taxId,
                        onError: reject
                    });
            });
        },

        /**
         * Activate the Tax Entry
         *
         * @param {Number} taxId
         * @returns {Promise}
         */
        activate: function (taxId) {
            return new Promise(function (resolve, reject) {
                QUIAjax.post(
                    'package_quiqqer_tax_ajax_entries_activate',
                    resolve, {
                        'package': 'quiqqer/tax',
                        taxId: taxId,
                        onError: reject
                    });
            });
        },

        /**
         * Deactivate the Tax Entry
         *
         * @param {Number} taxId
         * @returns {Promise}
         */
        deactivate: function (taxId) {
            return new Promise(function (resolve, reject) {
                QUIAjax.post(
                    'package_quiqqer_tax_ajax_entries_deactivate',
                    resolve, {
                        'package': 'quiqqer/tax',
                        taxId: taxId,
                        onError: reject
                    });
            });
        }
    });
});
