(function(){
    'use strict';
    angular
    .module('app')
    .service('RepSettingsService', RepSettingsService)
    RepSettingsService.$inject = ['$http'];
    function RepSettingsService($http){
        return {
            UserShippingCostGet: function(id){
                return $http.get('/api/v1/rep-ship-rate/' + id)
                    .then((response) => {
                        return response;
                    })
                    .catch((err) => {
                        return err;
                    })
            },
            UpdateUserShippingPost: function(data){
                return $http.post('/api/v1/rep-ship-rate/' + data.id, data)
                    .then((response) => {
                        return response.data;
                    })
                    .catch((err) => {
                        return err;
                    })
            },
            ShowForStoreGet: function(){
                return $http.get('/api/v1/show-for-store')
                    .then((response) => {
                        return response.data.data;
                    })
                    .catch((err) => {
                        return err;
                    })
            }


        }
    }
})();