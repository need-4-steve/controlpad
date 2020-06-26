(function(){
"use strict"
    angular
    .module('app')
    .controller('SubscriptionController', SubscriptionController)
    SubscriptionController.$inject = ['$scope', '$http', 'SubscriptionService'];
    function SubscriptionController($scope, $http, SubscriptionService) {
        $scope.currentPage = 1;
        $scope.pageSize = 15;
        var vm = this;
        vm.success = false;
        vm.failure = false;
        vm.loading = true;
        vm.subCreated = false;
        vm.formObject = {};
        vm.errorMessage = false;
        vm.updated = false;

        vm.createSubscriptionPlan = function(){
            SubscriptionService.createSubscriptionPost(vm.formObject)
            .then((response) => {
                if (response.status == 200){
                    vm.newSubPlan = response;
                    vm.subCreated = true;
                    vm.errorMessage = false;
                    vm.formObject = {};
                    setTimeout(function (){
                        $scope.$apply(function(){
                            vm.subCreated = false;
                        });
                    }, 5000);
                } else {
                    vm.errorMessage = true;
                    vm.subCreated = false;
                    setTimeout(function (){
                        $scope.$apply(function(){
                            vm.errorMessage = false;
                        });
                    }, 5000);
                }
            })
            .catch((error) => {
                console.log(error);
            })
        }

        vm.editSubscriptionPlan = function() {
            vm.formObject = {
                'id': vm.subscriptionPlan.id,
                'title': vm.subscriptionPlan.title,
                'duration': vm.subscriptionPlan.duration,
                'price': vm.subscriptionPlan.price,
                'renewable': vm.subscriptionPlan.renewable
            }
            SubscriptionService.editSubscriptionPlanPut(vm.formObject)
            .then((response) => {
                if (response.status == 200){
                    vm.updated = true;
                    vm.errorMessage = false;
                    setTimeout(function (){
                        $scope.$apply(function(){
                            vm.updated = false;
                        });
                    }, 5000);
                } else {
                    vm.errorMessage = true;
                    vm.updated = false;
                    setTimeout(function (){
                        $scope.$apply(function(){
                            vm.errorMessage = false;
                        });
                    }, 5000);
                }


            })
            .catch((error) => {
                console.log(error);
            });
        }

        vm.getAllSubscriptions = function(){
            SubscriptionService.allSubscriptionsGet()
            .then((response) => {
                vm.subscriptions = response.data;
                vm.loading = false;
            })
            .catch((error) => {
                console.log(error);
            });
        }
        vm.getAllSubscriptions();

        vm.getUserSubscriptions = function(){
            SubscriptionService.userSubscriptionsGet()
            .then((response) => {
                vm.userSubs = response.data;
                vm.loading = false;
            })
            .catch((error) => {
                console.log(error);
            });
        }
        vm.getUserSubscriptions();

        vm.renew = function(){
            SubscriptionService.renewSubscriptionGet()
                .then((response) => {
                    vm.renewSub = response.data.data;
                    if (response.data.error == true) {
                        vm.failure = true;
                        vm.success = false;
                    } else {
                        vm.failure = false;
                        vm.success = true;
                        vm.subscription.user.subscriptions[0].created_at = response.data.data.created_at;
                        vm.subscription.user.subscriptions[0].ends_at = response.data.data.ends_at;
                    }
                })
                .catch((error) => {
                    console.log(error);
                });
        }

        vm.showSubscription = function(){
            SubscriptionService.showSubscriptionGet()
                .then((response) => {
                    vm.subscription = response.data;
                    vm.loading = false;
                })
                .catch((error) => {
                    console.log(error);
                });
        }
        vm.showSubscription();

        vm.showSubscriptionPlan = function(id){
            SubscriptionService.showSubscriptionPlanGet(id)
                .then((response) => {
                    vm.subscriptionPlan = response.data;
                    vm.loading = false;
                })
                .catch((error) => {
                    console.log(error);
                });
            }

    }
})();
