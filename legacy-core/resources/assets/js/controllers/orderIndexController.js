(function(){
'use strict';
    angular
    .module('app')
    .controller('OrderIndexController', OrderIndexController)
    OrderIndexController.$inject = ['$scope', '$http', 'OrderService'];
    function OrderIndexController($scope, $http, OrderService) {

        var vm = this;
        vm.pageSize = 15;
        vm.orders = [];
        vm.currentPage = 1;
        vm.asc = true;
        vm.start_date = new Date(moment().subtract(90, 'days').calendar());
        vm.end_date = new Date(moment().format('L'));
        vm.ordersObject = {
            'column': 'id',
            'order': 'asc',
            'per_page': 15,
            'search_term': "",
            'page': vm.currentPage
        };
        vm.loading = true;

        vm.getOrders = function(){
            vm.ordersObject.page = vm.currentPage;
            vm.ordersObject.start_date = vm.start_date;
            vm.ordersObject.end_date = vm.end_date;
            if (vm.asc === true) {
                vm.ordersObject.order = 'asc';
            } else {
                vm.ordersObject.order = 'desc';
            }
            OrderService.ordersGet(vm.ordersObject)
                .then((response) => {
                    vm.orderMessage = response.data.message;
                    vm.orders = response.data.orders;
                    vm.loading = false;
                })
                .catch((error) => {
                    console.log(error);
                });
        }
        vm.getOrders();

        vm.sortByColumn = function(column) {
            vm.asc = !vm.asc;
            vm.ordersObject.column = column;
            vm.getOrders()
        }

        vm.getOrderTypes = function(){
            OrderService.orderTypesGet()
            .then((response) => {
                vm.orderTypes = response.data;
            })
            .catch((error) => {
                console.log(error);
            });
        }
        vm.getOrderTypes();

        vm.searchLastName = function(last_name){
            last_name = vm.customer_last_name;
            vm.ordersObject.search_term = last_name;
            OrderService.ordersGet(vm.ordersObject)
                .then((response) => {
                    vm.orderMessage = response.data.message;
                    vm.orders = response.data.orders;
                })
                .catch((error) => {
                    console.log(error);
                });
        }

        vm.searchOrderType = function(type){
            type = vm.order_type;
            vm.ordersObject.type = type;
            OrderService.ordersGet(vm.ordersObject)
                .then((response) => {
                    vm.orderMessage = response.data.message;
                    vm.orders = response.data.orders;
                })
                .catch((error) => {
                    console.log(error);
                });
        }

        vm.setPage = function(page){
            page = vm.currentPage;
            vm.getOrders();
        }
    }
})();
