(function(){
'use strict';
    angular
    .module('app')
    .service('MessageService', MessageService)
    MessageService.$inject = ['$http']
    function MessageService($http){
        return {
            userNamesGet: function(lead){
                return $http.get('/api/v1/user/names/')
                    .then((response) => {
                        return response.data;
                    })
                    .catch((err) => {
                        return err;
                    })
            },
            sendEmailPost: function(email){
                return $http.post('/blast_email', email)
                    .then((response) => {
                        return response;
                    })
                    .catch((err) => {
                        return err;
                    })
            },
            sendTextPost: function(text){
                return $http.post('/blast_sms', text)
                    .then((response) => {
                        return response;
                    })
                    .catch((err) => {
                        return err;
                    })
            },
        }
    }
})();
