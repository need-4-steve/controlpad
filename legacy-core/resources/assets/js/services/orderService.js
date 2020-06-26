(function(){
'use strict'
    angular
    .module('app')
    .factory('OrderService', OrderService)
    OrderService.$inject = ['$http'];
    function OrderService($http){
        return {
            ordersGet: function(request){
                return $http.get('/api/v1/orders',  {params: request})
                    .then((response) => {
                        return response;
                    })
                    .catch((err) => {
                        return err;
                    })
            },
            orderTypesGet: function(){
                return $http.get('/api/v1/orders/order-types')
                    .then((response) => {
                        return response;
                    })
                    .catch((err) => {
                        return err;
                    })
            },
            orderShow: function(receipt_id){
                return $http.get('/api/v1/orders/show/' + receipt_id)
                    .then((response) => {
                        return response.data;
                    })
                    .catch((err) => {
                        return err;
                    })
            }
        }
    }
})();
