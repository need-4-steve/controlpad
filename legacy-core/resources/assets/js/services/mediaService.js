(function(){
'use strict'
    angular
    .module('app')
    .factory('MediaService', MediaService)
    MediaService.$inject = ['$http'];
    function MediaService($http){
        return {
            mediaCreate: function(media){
                return $http.post('/api/v1/media/create', media)
                    .then((response) => {
                        return response;
                    })
                    .catch((err) => {
                        return err;
                    })
            },
            deleteFile: function(id){
                return $http.delete('/api/v1/media/' + id)
                    .then((response) => {
                        return response;
                    })
                    .catch((err) => {
                        return err;
                    })
            }
        }
    }
})();
