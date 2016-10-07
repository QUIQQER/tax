/**
 * @module package/quiqqer/tax/bin/classes/TaxTypes
 * @author www.pcsg.de (Henning Leutz)
 *
 * @require qui/QUI
 * @require qui/classes/DOM
 * @require Ajax
 */
define('package/quiqqer/tax/bin/classes/TaxTypes', [

    'qui/QUI',
    'qui/classes/DOM',
    'Ajax'

], function (QUI, QDOM, QUIAjax) {
    "use strict";

    return new Class({
        Extends: QDOM,
        Type: 'package/quiqqer/tax/bin/classes/TaxTypes',

        initialize: function (options) {
            this.parent(options);
        },

        /**
         * Return all tax types
         *
         * @param {Array} [ids] - list of tax types ids
         * @returns {Promise}
         */
        getList: function (ids) {
            return new Promise(function (resolve, reject) {
                QUIAjax.get('package_quiqqer_tax_ajax_types_getList', resolve, {
                    'package': 'quiqqer/tax',
                    onError: reject,
                    ids: JSON.encode(ids || [])
                });
            });
        },

        /**
         * Return the tax group data
         *
         * @param {Number} taxTypeId
         * @returns {Promise}
         */
        get: function (taxTypeId) {
            return new Promise(function (resolve, reject) {
                QUIAjax.get('package_quiqqer_tax_ajax_types_get', resolve, {
                    'package': 'quiqqer/tax',
                    onError: reject,
                    taxTypeId: taxTypeId
                });
            });
        },

        /**
         * Create a new tax type
         *
         * @returns {Promise}
         */
        createChild: function () {
            return new Promise(function (resolve, reject) {
                QUIAjax.post('package_quiqqer_tax_ajax_types_create', resolve, {
                    'package': 'quiqqer/tax',
                    onError: reject
                });
            });
        },

        /**
         * Create a new tax types
         *
         * @param {Number} taxTypeId
         * @param {Object} data
         *
         * @returns {Promise}
         */
        updateChild: function (taxTypeId, data) {
            return new Promise(function (resolve, reject) {
                QUIAjax.post('package_quiqqer_tax_ajax_types_update', resolve, {
                    'package': 'quiqqer/tax',
                    taxTypeId: taxTypeId,
                    data: JSON.encode(data),
                    onError: reject
                });
            });
        },

        /**
         * Delete a tax types
         *
         * @param {Number} taxTypeId
         */
        deleteChild: function (taxTypeId) {
            return new Promise(function (resolve, reject) {
                QUIAjax.post('package_quiqqer_tax_ajax_types_delete', resolve, {
                    'package': 'quiqqer/tax',
                    taxTypeId: taxTypeId,
                    onError: reject
                });
            });
        }
    });
});
