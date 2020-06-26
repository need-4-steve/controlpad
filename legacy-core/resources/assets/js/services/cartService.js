(function(){
'use strict';
angular
  .module('app')
  .factory('CartService', CartService)
  CartService.$inject = ['$http'];
  function CartService($http){
    return {
      creatingCart: false,
      cart: null,
      get: function () {
        return new Promise((resolve, reject) => {
          if (this.cart) {
            resolve(this.cart);
            return true;
          }
          let cartPid = localStorage.getItem('cartPid');
          if (cartPid !== null) {
            $http.get('/api/v1/carts/' + cartPid)
              .then((response) => {
                this.cart = response.data;
                this.calculateTotals();
                resolve(this.cart);
              })
              .catch((err) => {
                if (err.status = 404) {
                  this.create().then((cart) => {
                    resolve(cart);
                  })
                } else {
                  // TODO show error
                  reject(err);
                }
              });
          } else {
            this.create().then((cart) => {
              resolve(cart);
            });
          }
        });
      },
      create: function () {
        return new Promise((resolve, reject) => {
          if (this.creatingCart) {
            reject('Already creating cart');
          }
          this.creatingCart = true;
          $http.post('/api/v1/carts')
          .then((response) => {
            this.creatingCart = false;
            this.cart = response.data;
            localStorage.setItem('cartPid', this.cart.pid);
            this.cart.lines = [];
            this.calculateTotals();
            resolve(this.cart);
          })
          .catch((err) => {
            this.creatingCart = false;
            reject({ error: true, message: err.data});
          });
        });
      },
      addItem: function (itemID, quantity, eventID) {
        return new Promise((resolve, reject) => {
          if (!this.cart) {
            reject('Cart not found');
          }
          $http.post('/api/v1/carts/' + this.cart.pid + '/lines', {item_id: itemID, quantity: quantity, event_id: eventID})
            .then((response) => {
              this.cart = response.data;
              this.calculateTotals();
              resolve(this.cart);
            })
            .catch((err) => {
              reject(err);
            });
        });
      },
      updateQuantity: function (itemID, quantity, eventID) {
        return new Promise((resolve, reject) => {
          if (!this.cart) {
            reject('Cart not found');
          }
          if (quantity <= 0) {
            this.removeItem(itemID)
              .then((cart) => {
                resolve(this.cart);
              });
          } else {

          }
          $http.patch('/api/v1/carts/' + this.cart.pid + '/lines', {item_id: itemID, quantity: quantity, event_id: eventID})
            .then((response) => {
              this.cart = response.data;
              this.calculateTotals();
              resolve(this.cart);
            })
            .catch((err) => {
              reject(err);
            });
        });
      },
      removeItem: function (itemID) {
        return new Promise((resolve, reject) => {
          if (!this.cart) {
            reject('Cart not found');
          }
          $http.delete('/api/v1/carts/' + this.cart.pid + '/lines/' + itemID)
            .then((response) => {
              this.cart.lines.forEach((l, index) => {
                if (l.item_id === itemID) {
                  this.cart.lines.splice(index, 1);
                  return;
                }
              });
              this.calculateTotals();
              resolve(this.cart);
            })
            .catch((err) => {
              reject(err);
            });
        });
      },
      //check the availablity of a given inventory item
      // TODO refactor to work with local cart context
      checkInventory: function (line) {
        return $http.get('/api/v1/inventory/check-availability', {params: line })
          .then((response) => {
            return response.data;
          })
          .catch((err) => {
            return err;
          })
      },
      calculateTotals: function () {
        if (this.cart) {
          this.cart.subtotal = 0.00;
          this.cart.lines.forEach((l) => {this.cart.subtotal += (l.price * l.quantity)});
        }
      }
    }
  }
})();
