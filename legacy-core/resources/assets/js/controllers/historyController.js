(function(){
'use strict';
    angular
    .module('app')
    .controller('HistoryController', HistoryController)
    HistoryController.$inject = ['$scope', '$http'];
    function HistoryController($scope, $http) {

        $scope.loading = true;
        $scope.currentPage = 1;
        $scope.pageSize = 15;

        if(model != '' && id == '') {
            var url = '/api/v1/history/' + model;
        }

        if(model != '' && id != '') {
            var url = '/api/v1/history/model/' + model;
        }

        if(id != '') {
            url += '/id/' + id;
        }

        if(model == '' && id == '') {
            var url = '/api/v1/history/';
        }

        $http.get(url).success(function(histories) {
            $scope.histories = histories;
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
