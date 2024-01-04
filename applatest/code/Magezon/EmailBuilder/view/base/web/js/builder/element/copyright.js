define([
    'jquery',
    'angular',
], function($, angular) {

    var copyright = function(magezonBuilderUrl, magezonBuilderService, magezonBuilderFilter) {
        return {
            replace: true,
            templateUrl: function(elem) {
                return magezonBuilderUrl.getTemplateUrl(elem, 'Magezon_EmailBuilder/js/templates/builder/element/copyright.html');
            },
            controller: function($scope, $controller) {
                var parent = $controller('baseController', {$scope: $scope});
                angular.extend(this, parent);
            },
            controllerAs: 'mgz'
        }
    };

    return copyright;
});