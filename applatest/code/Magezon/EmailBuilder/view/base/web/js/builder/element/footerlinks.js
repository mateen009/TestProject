define([
    'jquery',
    'angular',
], function($, angular) {

    var footer_links = function(magezonBuilderUrl, magezonBuilderService, magezonBuilderFilter) {
        return {
            replace: true,
            templateUrl: function(elem) {
                return magezonBuilderUrl.getTemplateUrl(elem, 'Magezon_EmailBuilder/js/templates/builder/element/footerlinks.html');
            },
            controller: function($scope, $controller) {
                var parent = $controller('baseController', {$scope: $scope});
                angular.extend(this, parent);
                $scope.getLinkParams = function(link) {
                    var params = {
                        type: 'custom',
                        url: '',
                        id: 0,
                        title: '',
                        extra: '',
                        nofollow: 0,
                        blank: 0
                    };
                    if (link) {
                        if (link.indexOf('{{mgzlink') === -1) {
                            params['url']  = link;
                            params['type'] = 'custom';
                        } else {
                            link.gsub(/\{\{mgzlink(.*?)\}\}/i, function (match) {
                                params = magezonBuilderFilter.parseAttributesString(match[1]);
                            });
                        }
                    }
                    if (params['url']) params['url'] = magezonBuilderService.removeslashes(params['url']);
                    return params;
                }
            },
            controllerAs: 'mgz'
        }
    };

    return footer_links;
});