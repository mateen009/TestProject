define([
    'jquery',
    'uiComponent',
    'ko',
    'amcharts',
    'amrepbuilder_helpers',
    'uiRegistry',
    'underscore'
], function ($, Component, ko, amcharts, helpers, registry, _) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Amasty_ReportBuilder/components/chart',
            chart: {
                data: {}
            },
            interval: 'day',
            components: [
                'index = bookmarks',
                'index = amrepbuilder_loader'
            ]
        },

        initChart: function (node) {
            var self = this;

            if (!self.source.display_chart) {
                return false;
            }

            registry.get(self.components, function () {
                helpers.initComponentsArray(arguments, self);

                self.bookmarks.on('saveState', function () {
                    self._updateData();
                });
            });

            self.chart = am4core.create(node, amcharts.XYChart);
            am4core.useTheme(am4themes_animated);

            self.chart.cursor = new am4charts.XYCursor();
            self.chart.scrollbarX = new am4core.Scrollbar();

            self._initExport();
        },

        /**
         * Update chart Data via Ajax
         *
         * @return {object}
         */
        _updateData: _.debounce(function () {
            var self = this;

            $.ajax({
                url: self.source.chart_update_url,
                data: {
                    report_id: self.source.report_id,
                    grid_data: self.bookmarks.current
                },
                method: 'POST',
                showLoader: false
            }).done(function (response) {
                self.chart.data = response.data;
                self.interval = response.interval;

                self._initSeries();
                self._initAxis('x', response.xAxisType);
                self._initAxis('y', response.yAxisType);
                self._renderChart();

                self.loader.isLoad(false);
            });
        }),

        /**
         * Chart data exporting initialization
         */
        _initExport: function () {
            var self = this;

            self.chart.exporting.menu = new am4core.ExportMenu();
            self.chart.exporting.menu.items = [{
                label: "...",
                menu: [{
                    label: "Image",
                    menu: [
                        {type: "png", label: "PNG"},
                        {type: "jpg", label: "JPG"},
                        {type: "svg", label: "SVG"},
                        {type: "pdf", label: "PDF"}
                    ]
                }, {
                    label: "Print", type: "print"
                }]
            }];
        },

        /**
         * Axis Series initialization
         */
        _initSeries: function () {
            var self = this;

            if (self.chart.series.length) {
                self.chart.series.removeIndex(0);
            }

            self.series = new am4charts.LineSeries();
            self.chart.cursor.snapToSeries = self.series;
        },

        /**
         * Axis initialization
         */
        _initAxis: function (axis, type) {
            if (this.chart[axis + 'Axes'].length) {
                this.chart[axis + 'Axes'].removeIndex(0);
            }

            switch (type) {
                case 'smallint':
                case 'decimal':
                    this._initValue(axis);
                    break;
                case 'text':
                case 'varchar':
                case 'int':
                    this._initCategory(axis);
                    break;
                default:
                    this._initDate(axis);
            }
        },

        /**
         * Chart rendering
         */
        _renderChart: function () {
            var self = this,
                labelX,
                labelY;

            self.chart.xAxes.push(self.valueAxisX);
            self.chart.yAxes.push(self.valueAxisY);
            self.chart.series.push(self.series);

            labelX = self.valueAxisX.renderer.labels.template;
            labelX.maxWidth = 150;
            labelX.truncate = true;
            labelX.wrap = true;
            labelX.tooltipText = "{valueX}";

            labelY = self.valueAxisY.renderer.labels.template;
            labelY.maxWidth = 200;
            labelY.truncate = true;
            labelY.wrap = true;
            labelY.tooltipText = "{valueY}";

            self.chart.zoomOutButton.marginRight = 60;
        },

        /**
         * Axis Type Value initialization
         *
         * @param {string} axis target axis name
         */
        _initValue: function (axis) {
            var self = this,
                axisUpperCase = axis.toUpperCase()

            self['valueAxis' + axisUpperCase] = new am4charts.ValueAxis();
            self['valueAxis' + axisUpperCase].dataFields['value' + axisUpperCase] = 'value' + axisUpperCase;
            self.series.dataFields['value' + axisUpperCase] = 'value' + axisUpperCase;
        },

        /**
         * Axis Type Category initialization
         *
         * @param {string} axis target axis name
         */
        _initCategory: function (axis) {
            var self = this,
                axisUpperCase = axis.toUpperCase()

            self.category = 'value' + axisUpperCase;

            self['valueAxis' + axisUpperCase] = new am4charts.CategoryAxis();
            self['valueAxis' + axisUpperCase].dataFields.category = 'value' + axisUpperCase;
            self.series.dataFields['category' + axisUpperCase] = 'value' + axisUpperCase;
        },

        /**
         * Axis Type Date initialization
         *
         * @param {string} axis target axis name
         */
        _initDate: function (axis) {
            var self = this,
                axisUpperCase = axis.toUpperCase();

            self['valueAxis' + axisUpperCase] = new am4charts.DateAxis();
            self.series.dataFields['date' + axisUpperCase] = 'value' + axisUpperCase;
            self['valueAxis' + axisUpperCase].baseInterval = {
                "timeUnit": self.interval,
                "count": 1
            };

            self.chart.cursor[axis + 'Axis'] = self['valueAxis' + axisUpperCase];
        },
    });
});
