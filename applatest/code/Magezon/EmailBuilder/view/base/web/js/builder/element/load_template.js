define([
    'jquery',
    'angular',
], function($, angular) {

    var load_template = function(magezonBuilderUrl, magezonBuilderService, magezonBuilderFilter) {
        return {
            replace: true,
            templateUrl: function(elem) {
                return magezonBuilderUrl.getTemplateUrl(elem, 'Magezon_EmailBuilder/js/templates/builder/element/load_template.html');
            },
            controller: function($scope, $controller) {
                var parent = $controller('baseController', {$scope: $scope});
                angular.extend(this, parent);
            },
            controllerAs: 'mgz'
        }
    };

    return load_template;
});