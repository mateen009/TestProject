var config = {
    map: {
        '*': {
            'amrepbuilder_helpers': 'Amasty_ReportBuilder/js/utils/helpers',
            'amcharts': 'Amasty_ReportBuilder/js/vendor/amcharts4/charts.min'
        }
    },
    shim: {
        'Amasty_ReportBuilder/js/vendor/amcharts4/core.min': {
            init: function () {
                return window.am4core;
            }
        },
        'Amasty_ReportBuilder/js/vendor/amcharts4/charts.min': {
            deps: [
                'Amasty_ReportBuilder/js/vendor/amcharts4/core.min',
                'Amasty_ReportBuilder/js/vendor/amcharts4/animated.min'
            ],
            exports: 'Amasty_ReportBuilder/js/vendor/amcharts4/charts.min',
            init: function () {
                return window.am4charts;
            }
        },
        'Amasty_ReportBuilder/js/vendor/amcharts4/animated.min': {
            deps: ['Amasty_ReportBuilder/js/vendor/amcharts4/core.min'],
            exports: 'Amasty_ReportBuilder/js/vendor/amcharts4/animated.min',
            init: function () {
                return window.am4themes_animated;
            }
        }
    },
    config: {
        mixins: {
            'Magento_Ui/js/grid/toolbar': {
                'Amasty_ReportBuilder/js/grid/toolbar': true
            },
            'Magento_Ui/js/grid/controls/bookmarks/bookmarks': {
                'Amasty_ReportBuilder/js/grid/bookmarks': true
            }
        }
    }
};
