(function(){
'use strict';
    angular
    .module('app')
    .controller('SalesController', SalesController)
    SalesController.$inject = ['$scope', '$http', 'SalesService'];
    function SalesController($scope, $http, SalesService) {

        var vm = this;
        vm.pageSize = 15;
        vm.saless = [];
        vm.currentPage = 1;
        vm.asc = true;
        vm.start_date = new Date(moment().subtract(90, 'days').calendar());
        vm.end_date = new Date(moment().format('L'));
        vm.salesObject = {
            'column': 'id',
            'order': 'asc',
            'per_page': 15,
            'search_term': "",
            'page': vm.currentPage
        };

        vm.getSales = function(){
            vm.salesObject.page = vm.currentPage;
            vm.salesObject.start_date = vm.start_date;
            vm.salesObject.end_date = vm.end_date;
            if (vm.asc === true) {
                vm.salesObject.order = 'asc';
            } else {
                vm.salesObject.order = 'desc';
            }
            SalesService.salesGet(vm.salesObject)
                .then((response) => {
                    vm.saleMessage = response.data.message;
                    vm.sales = response.data.sales;
                })
                .catch((error) => {
                    console.log(error);
                });
        }
        vm.getSales();

        vm.sortByColumn = function(column) {
            vm.asc = !vm.asc;
            vm.salesObject.column = column;
            vm.getSales()
        }

        vm.searchLastName = function(last_name){
            last_name = vm.customer_last_name;
            vm.salesObject.search_term = last_name;
            SalesService.salesGet(vm.salesObject)
                .then((response) => {
                    vm.saleMessage = response.data.message;
                    vm.sales = response.data.sales;
                })
                .catch((error) => {
                    console.log(error);
                });
        }

        vm.setPage = function(page){
            page = vm.currentPage;
            vm.getSales();
        }
    }
})();
