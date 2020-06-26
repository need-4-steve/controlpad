(function(){
"use strict"
    angular
    .module('app')
    .controller('UserShowController', UserShowController)
    UserShowController.$inject = ['$scope', '$http'];
    function UserShowController($scope, $http) {

        $scope.applyCredit = function(id) {
            $scope.loading_credit = true;
            $.post('/users/updateCredit', {
                ids: [id],
                amount: $scope.amount
            }, function(result) {
                $scope.amount = null;
                $scope.credit = result;
                $scope.loading_credit = false;
                $scope.$apply();
            });

        }
    }
})();
