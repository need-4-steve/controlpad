(function(){
'use strict';
    angular
    .module('app')
    .factory('UserService', UserService)
    UserService.$inject = ["$http"]
    function UserService($http) {
        return {
            createUser: function(user){
                return $http.post('/api/v1/registration/create', user)
                .then((response) => {
                    return response.data
                })
                .catch((error) => {
                    return error;
                })
            },
            getNames: function() {
                return $http.get('/api/v1/user/names')
                .then((response) => {
                    return response.data
                })
                .catch((error) => {
                    return error;
                })
            },
            getAuthUser: function() {
              return $http.get('/api/v1/user/auth/')
              .then((response) => {
                  return response.data
              })
              .catch((error) => {
                  return error;
              })
            }
        }
    }
})();
