(function(){
'use strict';
angular
.module('app')
.controller('OrderCreateController', OrderCreateController)
    OrderCreateController.$inject = ['$scope', '$http'];
    function OrderCreateController($scope, $http){
        $scope.coupon = {};
        $scope.billingAddr = {};
        $scope.shippingAddr = {};
        $scope.planInterval = '';
        $scope.nextBillDate = '';
        $scope.selectedPlanPid = null;
        $scope.selectedPlan = null;
        $scope.oldAddress = {
          line_1: null,
          line_2: null,
          city: null,
          state: null,
          zip: null
        };
        $scope.sameAddress = false;
        $scope.shippingRates = [];
        $scope.selfPickup = { enabled: false };
        $scope.states = [{
          'value': 'AL',
          'name': 'Alabama'
        },
        {
          'value': 'AK',
          'name': 'Alaska'
        },
        {
          'value': 'AZ',
          'name': 'Arizona'
        },
        {
          'value': 'AR',
          'name': 'Arkansas'
        },
        {
          'value': 'CA',
          'name': 'California'
        },
        {
          'value': 'CO',
          'name': 'Colorado'
        },
        {
          'value': 'CT',
          'name': 'Connecticut'
        },
        {
          'value': 'DE',
          'name': 'Delaware'
        },
        {
          'value': 'DC',
          'name': 'District Of Columbia'
        },
        {
          'value': 'FL',
          'name': 'Florida'
        },
        {
          'value': 'GA',
          'name': 'Georgia'
        },
        {
          'value': 'HI',
          'name': 'Hawaii'
        },
        {
          'value': 'ID',
          'name': 'Idaho'
        },
        {
          'value': 'IL',
          'name': 'Illinois'
        },
        {
          'value': 'IN',
          'name': 'Indiana'
        },
        {
          'value': 'IA',
          'name': 'Iowa'
        },
        {
          'value': 'KS',
          'name': 'Kansas'
        },
        {
          'value': 'KY',
          'name': 'Kentucky'
        },
        {
          'value': 'LA',
          'name': 'Louisiana'
        },
        {
          'value': 'ME',
          'name': 'Maine'
        },
        {
          'value': 'MD',
          'name': 'Maryland'
        },
        {
          'value': 'MA',
          'name': 'Massachusetts'
        },
        {
          'value': 'MI',
          'name': 'Michigan'
        },
        {
          'value': 'MN',
          'name': 'Minnesota'
        },
        {
          'value': 'MS',
          'name': 'Mississippi'
        },
        {
          'value': 'MO',
          'name': 'Missouri'
        },
        {
          'value': 'MT',
          'name': 'Montana'
        },
        {
          'value': 'NE',
          'name': 'Nebraska'
        },
        {
          'value': 'NV',
          'name': 'Nevada'
        },
        {
          'value': 'NH',
          'name': 'New Hampshire'
        },
        {
          'value': 'NJ',
          'name': 'New Jersey'
        },
        {
          'value': 'NM',
          'name': 'New Mexico'
        },
        {
          'value': 'NY',
          'name': 'New York'
        },
        {
          'value': 'NC',
          'name': 'North Carolina'
        },
        {
          'value': 'ND',
          'name': 'North Dakota'
        },
        {
          'value': 'OH',
          'name': 'Ohio'
        },
        {
          'value': 'OK',
          'name': 'Oklahoma'
        },
        {
          'value': 'OR',
          'name': 'Oregon'
        },
        {
          'value': 'PA',
          'name': 'Pennsylvania'
        },
        {
          'value': 'RI',
          'name': 'Rhode Island'
        },
        {
          'value': 'SC',
          'name': 'South Carolina'
        },
        {
          'value': 'SD',
          'name': 'South Dakota'
        },
        {
          'value': 'TN',
          'name': 'Tennessee'
        },
        {
          'value': 'TX',
          'name': 'Texas'
        },
        {
          'value': 'UT',
          'name': 'Utah'
        },
        {
          'value': 'VT',
          'name': 'Vermont'
        },
        {
          'value': 'VA',
          'name': 'Virginia'
        },
        {
          'value': 'WA',
          'name': 'Washington'
        },
        {
          'value': 'WV',
          'name': 'West Virginia'
        },
        {
          'value': 'WI',
          'name': 'Wisconsin'
        },
        {
          'value': 'WY',
          'name': 'Wyoming'
        },
        {
          'value': 'AA',
          'name': 'Armed Forces Americas'
        },
        {
          'value': 'AE',
          'name': 'Armed Forces Middle East, Europe, Africa and Canada'
        },
        {
          'value': 'AP',
          'name': 'Armed Forces Pacific'
        }];
        $scope.step = 1;
        $scope.years = [];
        $scope.user = {
          firstName: '',
          lastName: '',
          email: ''
        };
        $scope.disabledBtn = false;
        $scope.notDisabled = false;
        $scope.couponError = null;
        $scope.payment = {
          card: {
            name: '',
            number: '',
            code: '',
            month: null,
            year: null
          }
        };
        $scope.addresses = {
            billing: {
                state: ''
            },
            shipping: {
                state: ''
            }
        };

        //error and success message handling variables
        $scope.error = false;
        $scope.success = false;
        $scope.errorMessage = [];
        $scope.errorMap = {};
        $scope.successMessage = '';

        $scope.useSelfPickup = function() {
          if ($scope.selfPickup.enabled) {
            $scope.oldAddress = $scope.addresses.shipping;
            $scope.addresses.shipping = {
              line_1: $scope.businessAddress.address_1,
              line_2: $scope.businessAddress.address_2,
              city: $scope.businessAddress.city,
              state: $scope.businessAddress.state,
              zip: $scope.businessAddress.zip
            };
            $scope.sameAddress = false;
          } else {
            $scope.addresses.shipping = $scope.oldAddress;
          }
        }

        $scope.createCheckout = function(cartPid) {
          $http.post($scope.ordersUrl + '/carts/' + cartPid + '/create-checkout')
              .then((response) => {
                  $scope.checkout = response.data;
                  // $scope.$apply();
              })
              .catch((err) => {
                  console.log(err);
                  $scope.error = true;
                  $scope.errorMessage = ['Something went wrong. Please try again later.'];
              })
        }

        $scope.couponApply = function() {
          console.log($scope);
            if ($scope.coupon.code.length == 0) {
              $scope.couponError = 'No code provided';
              return;
            }
            let request = {
              coupon_code: $scope.coupon.code
            };
            $http.patch($scope.ordersUrl + '/checkouts/' + $scope.checkout.pid, request)
                .then((response) => {
                    $scope.checkout = response.data;
                })
                .catch((err) => {
                    console.log(err);
                    if (err.status == 422 && err.data.coupon_code) {
                      $scope.couponError = err.data.coupon_code[0];
                    } else {
                      $scope.errorMessage = ['Something went wrong. Please try agian later.'];
                    }
                    setTimeout(function () {
                      $scope.$apply(function () {
                        $scope.couponError = null;
                      })
                    }, 2000)
                });
        }

        $scope.setStep = function (step) {
          $scope.step = step;
        }

        $scope.nextButton = function() {
            $scope.errorMap = {};
            let request = {
              billing_address: $scope.addresses.billing,
              shipping_address: $scope.addresses.shipping,
              self_pickup: $scope.selfPickup.enabled,
              autoship_pid: $scope.selectedPlanPid
            };
            let name = $scope.user.firstName + ' ' + $scope.user.lastName;
            request.billing_address.name = name;
            request.billing_address.email = $scope.user.email;
            request.shipping_address.name = name;
            $http.patch($scope.ordersUrl + '/checkouts/' + $scope.checkout.pid, request)
                .then((response) => {
                    $scope.checkout = response.data;
                    $scope.step = 2;
                })
                .catch((err) => {
                    if (err.status == 422 && err.data['business_address.email']) {
                      $scope.errorMap = err.data
                    } else {
                      $scope.error = true;
                      $scope.errorMessage = [(err.message ? err.message : 'Unexpected Error')];
                    }
                    setTimeout(function (){
                        $scope.$apply(function(){
                            $scope.error = false;
                        });
                    }, 9000);
                });
        }

        $scope.getYears = function(){
            var currentYear = new Date().getFullYear();
            for (var i = 0; i <= 10; i++){
                $scope.years.push(currentYear + i);
            }
        }
        $scope.getYears();

        $scope.updateAddress = function() {
            $scope.addresses.billing = angular.copy($scope.addresses.shipping);
        }
        $scope.validateCardData = function (card) {
            let today = new Date();
            let year = today.getFullYear();
            let month = today.getMonth() + 1; // zero offset
            let errors = [];
            if (!card.name || /^\s*$/.test(card.name)) {
                errors.push('Card name required');
            }
            // check sum
            if (!card.number || card.number.indexOf(' ') > -1 || isNaN(card.number) ||
                card.number.length < 13 || card.number.length > 16) {
                errors.push('Card number must be between 13 and 16 numbers');
            } else {
                let length = card.number.length;
                let checkSum = parseInt(card.number.substring(length - 1, length));
                let sum = 0;
                let isOdd = true;
                for (let i = length - 2; i >= 0; --i) {
                    if (isOdd) {
                        let res = 2 * parseInt(card.number.substring(i, i + 1));
                        sum += res > 9 ? res - 9 : res;
                    } else {
                        sum += parseInt(card.number.substring(i, i + 1));
                    }
                    isOdd = !isOdd;
                }
                if (((sum + checkSum) % 10) !== 0) {
                    errors.push('Card number invalid');
                }
            }

            if (isNaN(card.month) || card.month < 1 || card.month > 12) {
                errors.push('Card month must be between 1 and 12')
            } else if (isNaN(card.year) || card.year < year || (card.year === year && card.month < month)) {
                errors.push('Card expired.')
            }
            if (!card.code || card.code.length < 3 || card.code.length > 4) {
              errors.push('CVV should be 3 or 4 characters');
            }
            $scope.errorMessage = errors;
            if (Object.keys(errors).length > 0) {
                $scope.error = true;
                return false;
            }
            return true;
        }
        $scope.createOrder = function () {
            $scope.error = false;
            $scope.disabledBtn = true;
            $scope.notDisabled = true;
            let request = {
                buyer: {
                  first_name: $scope.user.firstName,
                  last_name: $scope.user.lastName,
                  email: $scope.user.email
                },
                payment: {}
            }
            if ($scope.checkout.total > 0) {
              request.payment = JSON.parse(JSON.stringify($scope.payment));
              request.payment.card.month = parseInt(request.payment.card.month);
              request.payment.card.year = parseInt(request.payment.card.year);
              request.payment.type = 'card';
              if (!$scope.validateCardData(request.payment.card)) {
                $scope.disabledBtn = false;
                $scope.notDisabled = false;
                return;
              }
            } else {
              request.payment.type = 'cash';
              request.payment.cash_type = 'Zero';
            }
            request.payment.amount = $scope.checkout.total;
            request.source = 'Web';
            $http.post($scope.ordersUrl + '/checkouts/' + $scope.checkout.pid + '/process', request)
                .then((response) => {
                    localStorage.setItem('recentOrder', JSON.stringify(response.data.order));
                    location.href = '/orders/receipt';
                }).catch((error) => {
                    let message = null;
                    let timeoutError = true;
                    if (error.status == 422) {
                      if (error.data.result_code) {
                        switch (error.data.result_code) {
                          // TODO send back to cart page if cannot reserve
                          case 3: // All inventory unavailable
                            // Disable page - checkout no longer valid
                            alert("All selected products unavailable at this time. Please try again later.");
                            $scope.step = 0;
                            timeoutError = false;
                            break;
                          case 4: // Some inventory unavailable
                            alert("Some products were not available. Checkout updated with remaining products and prices.");
                            $scope.checkout = error.data.checkout;
                            // Allow normal error message to pass through as well
                            break;
                          case 5: // Payment failed
                            // message will just be the result from the failed transaction
                            if (!error.data.message) {
                              message = "Payment Failed. Try again later.";
                            }
                            break;
                          case 6: // Shipping address not set
                            // Go back to info page
                            alert("Shipping address not found.");
                            $scope.step = 1;
                            break;
                          case 8:
                            alert('Coupon already used. Checkout updated to remove coupon.')
                            $scope.checkout = error.data.checkout;
                            break;
                          case 13:
                            message = 'Unexpected System Error. Your order was not completed and you have not been charged. To complete your order you will need to refresh this page and start over.'
                          default:
                            // Unexpected result code - default error message for now
                        }
                      }
                    }
                    $scope.error = true;
                    if (message) {
                      $scope.errorMessage = [message];
                    } else {
                      $scope.errorMessage = [(error.data.message ? error.data.message : 'Unexpected Error')];
                    }
                    $scope.notDisabled = false;
                    if (timeoutError) {
                      setTimeout(function (){
                        $scope.$apply(function(){
                          $scope.error = false;
                        });
                      }, 60000);
                    }
                });
        }

        $scope.disableButton = function (){
            $scope.disabledBtn = true;
            $scope.notDisabled = true;
        }

        $scope.getAutoshipPlan = function (pid) {
          $http.get($scope.autoshipUrl + '/plans/pid/' + pid)
              .then((response) => {
                  $scope.selectedPlan = response.data;
                  // ex: 1 Month recurring, remove plurals when only 1
                  let cycleName = (response.data.frequency == 1 ? response.data.duration.replace('s', '') : response.data.duration)
                  $scope.planInterval = response.data.frequency.toString() + ' ' + cycleName + ' recurring';
                  $scope.nextBillDate = moment().add(response.data.frequency, response.data.duration).format("MMM DD, YYYY");
              })
              .catch((err) => {
                  console.log(err);
                  $scope.error = true;
                  $scope.errorMessage = [(err.message ? err.message : 'Failed to look up autoship plan')];
                  setTimeout(function (){
                      $scope.$apply(function(){
                          $scope.error = false;
                      });
                  }, 9000);
              });
        }

        $scope.getUrlVars = function() {
          var vars = {};
          var parts = window.location.href.replace(/[?&]+([^=&]+)=([^&]*)/gi, function(m,key,value) {
            vars[key] = value;
          });
          return vars;
        }

        $scope.getUrlParam = function(parameter, defaultvalue) {
          var urlparameter = defaultvalue;
          if(window.location.href.indexOf(parameter) > -1){
            urlparameter = $scope.getUrlVars()[parameter];
          }
          return urlparameter;
        }

        $scope.selectedPlanPid = $scope.getUrlParam('selectedPlanPid', null);
        if ($scope.selectedPlanPid) {
          $scope.getAutoshipPlan($scope.selectedPlanPid);
        }

        let cartPid = localStorage.getItem('cartPid');
        if (cartPid !== null) {
          $scope.createCheckout(cartPid);
        } else {
          $scope.error = true;
          $scope.errorMessage = ["Cart not found. Please go back to the store and try again."];
        }
    }
})();
