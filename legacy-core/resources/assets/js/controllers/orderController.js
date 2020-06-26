(function(){
'use strict';
    angular
    .module('app')
    .controller('OrderController', OrderController)
    OrderController.$inject = ['$http', 'OrderService'];
    function OrderController($http, OrderService) {
        var vm = this;
        vm.order = {};

        vm.showOrder = function(receipt_id){
            OrderService.orderShow(receipt_id)
            .then((response) => {
                vm.order = response.data;
            })
            .catch((error) => {
                console.log(error);
            });
        }

    }

})();
