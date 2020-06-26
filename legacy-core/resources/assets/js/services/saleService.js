(function(){
'use strict'
    angular
    .module('app')
    .factory('SalesService', SalesService)
    SalesService.$inject = ['$http'];
    function SalesService($http){
        return {
            salesGet: function(request){
                return $http.get('/api/v1/sales',  {params: request})
                    .then((response) => {
                        return response;
                    })
                    .catch((err) => {
                        return err;
                    })
            },
            saleShow: function(receipt_id){
                return $http.get('/api/v1/sales/show/' + receipt_id)
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
