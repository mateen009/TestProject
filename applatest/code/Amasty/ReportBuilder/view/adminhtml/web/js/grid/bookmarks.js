/**
 * Mixins for Magento's Magento_Ui/grid/controls/bookmarks/bookmarks.js
 */
define(function () {
    'use strict';

    var mixin = {

        /**
         * Added synthetic event for Magento saveState method
         */
        saveState: function () {
            this._super();

            this.trigger('saveState');

            return this;
        }
    };

    return function (target) {
        return target.extend(mixin);
    };
});
