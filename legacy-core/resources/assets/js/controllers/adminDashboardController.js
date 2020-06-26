(function(){
'use strict';
    angular
    .module('app')
    .controller('AdminDashboardController', AdminDashboardController)
    AdminDashboardController.$inject = ['$scope', '$window', '$http'];
    function AdminDashboardController($scope, $window, $http) {
      $scope.id = ''
      $scope.logInAsUser = function () {
        $http.post('/api/v1/login-as/' + $scope.id).success(function(response) {
        localStorage.setItem('auth-role', response.role)
         window.location = '/dashboard'
       })
      }
    }
})();
