(function(){
'use strict';
    angular
    .module('app')
    .directive('cpTags', function(){
    return {
        restrict: 'AE',
        controller: function($http, $scope){
            $scope.selectedTag = [];

            $http.get('/api/v1/productTags').success(function(response){
                $scope.tags = response.map(function(tag){
                    return {
                        name: tag.name
                    };
                });
            })

            $scope.onSelect = function ($item) {
                $scope.nameObject = [name = $item.name]
                $scope.selectedTag.push($scope.nameObject);
            };

        },
        template: "<input type='text' ng-model='selected' typeahead='tag as tag.name for tag in tags | filter:$viewValue | limitTo:15' typeahead-on-select='onSelect($item)' class='form-control tagger new'/>"
    }
    })
})();
