(function(){
'use strict';
    angular
    .module('app')
    .controller('StoreHeaderController', StoreHeaderController)
    StoreHeaderController.$inject = ['$scope', '$window', '$http', 'CategoryService', 'CartService'];
    function StoreHeaderController($scope, $window, $http, CategoryService, CartService){
      console.log('store header controller loaded');
        $scope.productByCategory = [];
        $scope.cart = {};
        $scope.catHierarchy = [];
        $scope.cartLink = false;
        $scope.subtotal = 0.00;

        if (window.location.href.indexOf('cart') > -1 || window.location.href.indexOf('/orders/create') > -1) {
            $scope.cartLink = true;
        }

        $scope.preventDefault = function(event) {
            event.stopPropagation();
        };

        angular.element($window).bind("scroll", function(){
          $scope.pageOffset = window.pageYOffset;
          if ($scope.pageOffset >= 120) {
            $scope.fixed = true;
            $scope.fill = true;
          } else {
            $scope.fixed = false;
            $scope.fill = false;
          }
          $scope.$apply();
        });

        $scope.sideCart = function(){
          console.log('sideCart called');
          $scope.getCart();
        }

        $scope.getCart = function(){
            $scope.loading = true;
            CartService.get()
            .then((cart) => {
                $scope.cart.lines = cart.lines;
                $scope.checkoutButton = $scope.cart.lines.length;
                $scope.subtotal = cart.subtotal;
                $scope.loading = false;
                $scope.$apply();
            })
            .catch((error) => {
                console.log(error);
            });
        }

        $scope.deleteCartLine = function(item_id){
            CartService.removeItem(item_id)
            .then((response) => {
                $scope.getCart();
            })
            .catch((err) => {
                console.log(err);
            })
        }

        $scope.updateCartline = function(item_id, quantity){
            if (quantity < 1) {
                quantity = 1;
            }
            CartService.updateQuantity(item_id, quantity, null)
            .then((response) => {
                $scope.getCart();
            })
            .catch((error) => {
                console.log(error)
            })
        }

        $scope.checkInv = function (line) {
            if (line.quantity !== null && line.quantity > 0){
                var itemToCheck = {
                    'item_id': line.item_id,
                    'quantity': line.quantity
                };
                CartService.checkInventory(itemToCheck)
                    .then((response) => {
                        if (response.error == true) {
                            $scope.inventoryError = true;
                            $scope.inventoryMessage = response.message;
                            line.quantity = 1;
                        }
                    }).catch((err)=>{
                        console.log(err);
                    });
            }
        }

        $scope.quantityChange = function(direction, quantity, item_id) {
            if (direction === 'plus') {
                quantity += 1;
            } else if (direction === 'minus') {
                quantity -= 1;
            }
            $scope.updateCart(quantity, item_id);
        }

        $scope.updateCart = function(quantity, item_id){
            CartService.updateQuantity(item_id, quantity, null)
            .then((response) => {
                $scope.getCart();
            })
            .catch((error) => {
                console.log(error)
            })
        }

        $scope.getCategories = function(){
            CategoryService.categoriesGet()
            .then((response) => {
                var temp = []
                // remove empty categories
                for(var category in response) {
                  if (response[category].children.length > 0) temp.push(response[category])
                }
                $scope.catHierarchy = temp;
            })
            .catch((error) => {
                console.log(error);
            });
        }
        $scope.getCategories();
        if ($scope.autoGetCart) {
          $scope.getCart();
        }
    }
})();
