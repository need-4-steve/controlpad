(function(){
'use strict';
    angular
    .module('app')
    .controller('InventoryImportController', InventoryImportController)
    InventoryImportController.$inject = ['$scope', '$http', 'UserService'];
    function InventoryImportController($scope, $http, UserService) {
        UserService.getNames().then((response) => {
            $scope.users = response;
        });
    }
})();
