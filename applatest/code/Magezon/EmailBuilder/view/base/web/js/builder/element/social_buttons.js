define([
    'jquery',
    'angular',
], function($, angular) {

    var social_buttons = function(magezonBuilderUrl) {
        return {
            replace: true,
            templateUrl: function(elem) {
                return magezonBuilderUrl.getTemplateUrl(elem, 'Magezon_EmailBuilder/js/templates/builder/element/social_buttons.html');
            },
            controller: function($scope, $controller) {
                var parent = $controller('baseController', {$scope: $scope});
                angular.extend(this, parent);
                //console.log($scope.element.social_items);

            },
            link: function(scope, element) {
                scope.socialImages = [];

                function initSocialImg() {
                    var _element = scope.element;
                    var items = [];

                    angular.forEach(_element.social_items, function(item, key) {
                        var img  = magezonBuilderUrl.getImageUrl(item.social_img);
                        items.push(img);
                    });

                    scope.socialImages = items;
                }

                scope.loadElement = function() {
                    initSocialImg();
                };
                initSocialImg();
                scope.$on('parentChanged', function(_element) {
                    initSocialImg();
                });
            },
            controllerAs: 'mgz'
        }
    };

    return social_buttons;
});