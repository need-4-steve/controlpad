(function(){
'use strict';
angular
    .module('app')
    .factory('GetShippingService', GetShippingService)
    function GetShippingService($http){
        return {
            getShippingRates: function() {
               return $http.get('/api/v1/shippingMethod/shipping-cost')
                    .then(function(data){
                        return data.data;
                    })
                    .catch(function(err){
                        return err
                    })
                }      
            }
        }

})();