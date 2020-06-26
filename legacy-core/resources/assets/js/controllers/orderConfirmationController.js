(function(){
'use strict';
    angular
    .module('app')
    .controller('OrderConfirmationController', OrderConfirmationController)
    OrderConfirmationController.$inject = ['$scope', '$http'];
    function OrderConfirmationController($scope, $http) {

        $scope.loading = true;
        // get data
        $http.get('/api/v1/order/new').then(function(data) {
            $scope.data = data.data;
            $scope.loading = false;
            $scope.data.total_price = $scope.data.total_price + $scope.data.total_tax;
        });
    }
})();
