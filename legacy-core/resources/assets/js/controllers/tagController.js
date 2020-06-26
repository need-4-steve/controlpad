(function(){
"use strict"
    angular
    .module('app')
    .controller('TagController', TagController)
    TagController.$inject = ['$http', '$scope'];
    function TagController($http, $scope){
        $scope.selectedTag = [];
        $scope.tag = [];

        $scope.addTag = function(){
            if ($scope.selected === null) {
                return false;
            }
            $scope.tag = $scope.selected.split(", ");
            for (var i = 0; i < $scope.tag.length; i++) {
                $scope.newTags.push($scope.tag[i]);
            }
            $scope.selected = "";
            $scope.tag = [];
        }

        $scope.deleteTag = function($index){
            $scope.newTags.splice($index, 1);
        }

        $http.get('/api/v1/tags/product-tags').success(function(response){
            $scope.tags = response.map(function(tags){
               return {
                   name: tags.name
               };
            });
        })

        $scope.onSelect = function ($item) {
            $scope.nameObject = [name = $item.name]
            $scope.selectedTag.push($scope.nameObject);
        };
    };
})();
