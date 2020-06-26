(function(){
'use strict';
    angular
    .module('app')
    .controller('RegistrationController', RegistrationController)
    RegistrationController.$inject = ['$scope', '$window', 'UserService'];
    function RegistrationController($scope, $window, UserService) {
        var vm = this;
        vm.user = {};
        vm.message = {
            error: false,
            success: false,
            errorMessage: [],
        };
        vm.clearErrors = function() {
            vm.message.error = false;
            vm.message.errorMessage = [];
        }
        vm.createRep = function () {
            // event.preventDefault();
            vm.clearErrors();
            UserService.createUser(vm.user)
                .then((response) => {
                    if (response.status === 422) {
                        vm.message.error = true;
                        vm.message.errorMessage = vm.message.errorMessage.concat(response.data);
                        console.log(response.data);
                    } else {
                        $window.location.href = '/banking/create';
                    }
                })
                .catch((error) => {
                    console.log(error);
                });
        }
    }
})();
