define([
    'angular'
], function(angular) {

    return {
        templateOptions: {
            labelProp: 'label',
            valueProp: 'value'
        },
        controller: function($scope, $timeout, magezonBuilderService) {
            $scope.$watch('model.variable', function(value) {
                magezonBuilderService.post('mgzemailbuilder/ajax/addVariable', {
                    variable: value
                }, true, function(res) {
                    $scope.$apply(function() {
                        if (res != null)
                            $scope.model.email_content += res;
                    });
                });
            });
        }
    }
});