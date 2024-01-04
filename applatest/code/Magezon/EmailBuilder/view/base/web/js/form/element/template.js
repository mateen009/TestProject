define([
    'angular'
], function(angular) {

    return {
        templateOptions: {
            labelProp: 'label',
            valueProp: 'value'
        },
        controller: function($scope, $timeout, magezonBuilderService) {
            $scope.loaded = false;
            $scope.$watch('model.template_id', function(value) {
                if ($scope.loaded) {
                    magezonBuilderService.post('mgzemailbuilder/ajax/loadTemplate', {
                        template_id: value
                    }, true, function (res) {
                        $scope.$apply(function () {
                            if (res != null) {
                                $scope.model.email_content = res.content;
                            }
                        });
                    });
                }
                $scope.loaded = true;
            });
        }
    }
});