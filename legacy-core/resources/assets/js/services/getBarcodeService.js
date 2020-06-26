(function(){
'use strict';
angular
    .module('app')
    .factory('GetBarcodeService', GetBarcodeService)
    function GetBarcodeService($http){
        return {
            getOrder: function(orderNum) {
               return $http.get('/api/v1/shippingInvoice/order-and-shipping-details/' + orderNum)
                    .then((data) => {
                        return data.data;
                    })
                    .catch((err) => {
                        return err;
                    })
                },
            completeOrder: function(orderId){
                return $http.post('/api/v1/fulfill-order/', orderId)
                    .then((response) => {
                        return response;
                    })
                    .catch((err) => {
                        return err
                    })
            }
        }
    }

})();
