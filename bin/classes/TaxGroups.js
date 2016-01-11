/**
 *
 * @package package/quiqqer/
 * @author www.pcsg.de (Henning Leutz)
 *
 * @require qui/QUI
 * @require qui/classes/DOM
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
        getList: function () {
            return new Promise(function (resolve, reject) {
                QUIAjax.get(
                    'package_quiqqer_tax_ajax_groups_getList',
                    function (result) {
                        resolve(result);
                    }, {
                        'package': 'quiqqer/tax',
                        onError  : reject
                    });
            });
        }
    });
});
