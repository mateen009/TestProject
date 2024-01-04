/**
 * Grid toolbar
 */

define(function () {
    'use strict';

    var mixin = {

        /**
         * Fixed Magento method in case when exist only one column in grid
         */
        checkTableWidth: function () {
            var cols = this.$cols,
                total = cols.length,
                rightBorder,
                tableWidth = this.$table.offsetWidth;

            if (total > 2) {
                rightBorder = cols[total - 2].offsetLeft;
            }

            if (this._tableWidth !== tableWidth) {
                this._tableWidth = tableWidth;

                this.onTableWidthChange(tableWidth);
            }

            if (this._rightBorder !== rightBorder) {
                this._rightBorder = rightBorder;

                this.onColumnsWidthChange();
            }

            return this;
        }
    };

    return function (target) {
        return target.extend(mixin);
    };
});
