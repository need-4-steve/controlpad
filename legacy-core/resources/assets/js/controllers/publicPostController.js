(function(){
'use strict';
    angular
    .module('app')
    .controller('PublicPostController', PublicPostController)
    PublicPostController.$inject = ['$scope', '$http'];
    function PublicPostController($scope, $http) {
        $scope.loading = true;
        $http.get('/api/v1/announcements/public').success(function(posts) {
            $scope.posts = posts;
            $scope.currentPage = 1;
            $scope.pageSize = 10;
            $scope.loading = false;
        });
    }
})();
