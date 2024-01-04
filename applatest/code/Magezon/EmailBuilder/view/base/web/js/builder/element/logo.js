define([
    'jquery',
    'angular',
], function($, angular) {

    var email_logo = function(magezonBuilderUrl) {
        return {
            replace: true,
            templateUrl: function(elem) {
                return magezonBuilderUrl.getTemplateUrl(elem, 'Magezon_EmailBuilder/js/templates/builder/element/logo.html');
            },
            controller: function($scope, $controller) {
                var parent = $controller('baseController', {$scope: $scope});
                angular.extend(this, parent);
                // process Logo element data
                $scope.getSrc = function() {
                    var logo;
                    switch($scope.element.source) {
                        case 'media_library':
                            if ($scope.element.logo_img)
                                logo = magezonBuilderUrl.getImageUrl($scope.element.logo_img);
                            break;
                        case 'external_link':
                            if ($scope.element.custom_src)
                                logo = $scope.element.custom_src;
                            break;
                    }
                    return logo;
                }
            },
            controllerAs: 'mgz'
        }
    };

    return email_logo;
});