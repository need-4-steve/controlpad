(function(){
    'use strict'
    angular
    .module('app')
    .service('SubscriptionService', SubscriptionService)
    SubscriptionService.$inject = ['$http'];
    function SubscriptionService($http){
        return {
            renewSubscriptionGet: function(){
                return $http.get('/api/v1/subscriptions/renew-subscription')
                    .then((response) => {
                        return response;
                    })
                    .catch((err) => {
                        return err;
                    })
            },
            showSubscriptionGet: function(){
                return $http.get('/api/v1/subscriptions/show')
                    .then((response) => {
                        return response;
                    })
                    .catch((err) => {
                        return err;
                    })
            },
            allSubscriptionsGet: function(){
                return $http.get('/api/v1/subscriptions/all-subscriptions')
                .then((response) => {
                    return response;
                })
                .catch((err) => {
                    return err;
                })
            },
            userSubscriptionsGet: function(){
                return $http.get('/api/v1/subscriptions/user-subscriptions')
                .then((response) => {
                    return response;
                })
                .catch((err) => {
                    return err;
                })
            },
            createSubscriptionPost: function(sub){
                return $http.post('/api/v1/subscriptions/create', sub)
                .then((response) => {
                    return response;
                })
                .catch((err) => {
                    return err;
                })
            },
            showSubscriptionPlanGet: function(id){
                return $http.get('/api/v1/subscriptions/show-plan/' + id)
                .then((response) => {
                    return response;
                })
                .catch((err) => {
                    return err;
                })
            },
            editSubscriptionPlanPut: function(subPlan){
                return $http.put('/api/v1/subscriptions/edit/'+subPlan.id, subPlan)
                .then((response) => {
                    return response;
                })
                .catch((err) => {
                    return err;
                })
            }
        }
    }
})();
