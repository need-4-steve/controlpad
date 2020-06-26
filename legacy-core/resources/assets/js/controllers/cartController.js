(function() {
'use strict';
angular
  .module('app')
  .controller('CartController', CartController)
  CartController.$inject = ['$scope', '$http', '$filter', 'GetShippingService', 'CartService']
  function CartController($scope, $http, $filter, GetShippingService, CartService) {
    $scope.showDetail = true;
    $scope.loading = true;
    $scope.shipping_rates;
    $scope.cart = null;
    $scope.checkoutButton;
    $scope.emptyCart = true;
    $scope.inventoryError = false;
    $scope.inventoryMessage = '';
    $scope.down = true;
    $scope.disabledBtn = false;
    $scope.notDisabled = false;
    $scope.shipping_error = '';
    $scope.autoGetCart = false; // If store header is used we don't want it to pull the cart
    $scope.autoshipPlans = [];
    $scope.model = {selectedPlan: null};

    $scope.getCart = function(){
      $scope.loading = true;
      CartService.get()
      .then((response) => {
        if (response.error){
          $scope.shipping_error = response.message;
          $scope.loading = false;
        } else {
          $scope.cart = response;
          $scope.emptyCart = $scope.cart.lines.length == 0;
          $scope.checkoutButton = $scope.cart.lines.length;
          let autoshipContainer = document.getElementById('autoship-container');
          if (autoshipContainer != null) {
            $scope.checkAutoship($scope.cart.type);
          } else {
            $scope.loading = false;
          }
        }
        $scope.$apply();
      })
      .catch((error) => {
        console.log(error);
      });
    }

    $scope.checkAutoship = function (cartType) {
      $http.get($scope.autoshipUrl + '/plans?visibilities[]=2')
        .then((response) => {
          $scope.autoshipPlans = response.data.data;
          $scope.loading = false
        })
        .catch((err) => {
          console.log(err);
          // TODO show error
        });
    }

    $scope.deleteCartLine = function(itemID){
      CartService.removeItem(itemID)
      .then((response) => {
        $scope.getCart();
      })
      .catch((err) => {
        console.log(err);
      })
    }

    $scope.updateCartline = function(quantity, item_id){
      if (quantity < 1) {
        quantity = 1;
      }
      CartService.updateQuantity(item_id, quantity)
      .then((response) => {
        $scope.getCart();
      })
      .catch((error) => {
        console.log(error);
      })
    }

    $scope.checkInv = function (line) {
      $scope.inventoryError = false
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
                line.quantity = response.quantity_available;
              }
              $scope.updateCartline(line.quantity, line.item_id);
            }).catch((err)=>{
                console.log(err);
            });
        }
    }
    $scope.toggleClass = function(position) {
      if ($scope.toggle === position) {
        $scope.toggle = null;
      } else {
        $scope.toggle = position;
        $scope.down = !$scope.down;
        $scope.up = !$scope.up;
      }
    }

    $scope.disableButton = function(){
      $scope.notDisabled = true;
      $scope.disabledBtn = true;
    }

    $scope.getUrlVars = function() {
      var vars = {};
      var parts = window.location.href.replace(/[?&]+([^=&]+)=([^&]*)/gi, function(m,key,value) {
        vars[key] = value;
      });
      return vars;
    }

    $scope.checkout = function() {
      if ($scope.model.selectedPlan != null) {
        window.location = '/orders/create?selectedPlanPid=' + $scope.model.selectedPlan.pid;
      } else {
        window.location = '/orders/create';
      }
    }

    $scope.getUrlParam = function(parameter, defaultvalue) {
      var urlparameter = defaultvalue;
      if(window.location.href.indexOf(parameter) > -1){
        urlparameter = $scope.getUrlVars()[parameter];
      }
      return urlparameter;
    }

    // Hacked in for public cart flow
    $scope.storeReturnUrl = $scope.getUrlParam('storeReturnUrl', null);
    if ($scope.storeReturnUrl) {
      localStorage.setItem('storeReturnUrl', $scope.storeReturnUrl);
    } else {
      $scope.storeReturnUrl = localStorage.getItem('storeReturnUrl');
    }

    if (window.location.host.split('.')[0] == 'cart') {
      // If public cart we need to allow a pid to be set through query param
      let cartPid = $scope.getUrlParam('cart_pid', null);
      if (cartPid) {
        localStorage.setItem('cartPid', cartPid);
        $scope.getCart();
      } else {
        // cart pid is required
        $scope.loading = false;
        $scope.cart = {};
      }
    } else {
      $scope.getCart();
    }
  }
})();
