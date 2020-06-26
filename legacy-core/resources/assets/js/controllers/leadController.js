(function(){
'use strict';
    angular
    .module('app')
    .controller('LeadController', LeadController)
    LeadController.$inject = ['$scope', '$http'];
    function LeadController($scope, $http) {

        $scope.loading = true;
        $scope.currentPage = 1;
        $scope.pageSize = 15;

        $http.get('/api/v1/leads').success(function(leads) {
            $scope.leads = leads;
            $scope.checkbox = function() {
                var checked = false;
                $('.bulk-check').each(function() {
                    if ($(this).is(":checked"))
                        checked = true;
                });
                if (checked == true) $('.applyAction').removeAttr('disabled');
                else $('.applyAction').attr('disabled', 'disabled');
            };
            $scope.loading = false;
        });

        $scope.pageChangeHandler = function(num) {
        };

    }
})();   
