(function(){
'use strict';
    angular
    .module('app')
    .controller('InventoryController', InventoryController)
    InventoryController.$inject = ['$scope', '$http'];
    function InventoryController($scope, $http) {
        $scope.currentPage = 1;
        $scope.pageSize = 15;
        $scope.loading = true;
        $scope.count = 0;
        $scope.priceSaved = false;

        $scope.savePrice = function savePrice(inventory, price){
            $http.post('/api/v1/inventory/save-price', {inventory, price}).success(function(data) {
                $scope.priceSaved = true;
                setTimeout(function (){
                    $scope.$apply(function(){
                        $scope.priceSaved = false;
                    });
                }, 2000);
            });
        };

        $scope.saveQuantity = function saveQuantity(inventory, quantity){
            $http.post('/api/v1/inventory/save-quantity', {inventory, quantity}).success(function(data) {
            });
        };
    }
})();
