(function(){
"use strict"
    angular
    .module('app')
    .controller('SmsMessageController', SmsMessageController)
    SmsMessageController.$inject = ['$scope', '$http', 'MessageService'];
    function SmsMessageController($scope, $http, MessageService) {
        var vm = this;

        vm.selectedName = [];
        vm.idArray = [];
        vm.successful = false;

        vm.getUserNames = function(){
            MessageService.userNamesGet()
                .then((response) => {
                    $scope.names = response.map(function(name){
                        return {
                            name: name.full_name,
                            id: name.id
                        };
                    });
                })
                .catch((error) => {
                    console.log(error);
                });
        }
        vm.getUserNames();

        vm.onSelect = function ($model) {
            vm.nameObject = {
                name: $model.name
            }
            vm.selectedName.push(vm.nameObject);
            vm.idArray.push($model.id);
            vm.selected = "";
        };

        vm.removeName = function (index) {
            vm.idArray.splice(index, 1);
            vm.selectedName.splice(index, 1);
        }

        vm.sendText = function(){
            vm.textForm = {
                ids: vm.idArray,
                body: vm.body
            }
            MessageService.sendTextPost(vm.textForm)
            .then((response) => {
                console.log(response);
                if (response.status === 200) {
                    vm.successMessage = response.data.message;
                    vm.body = "";
                    vm.idArray = [];
                    vm.selectedName = [];
                    vm.successful = true;
                    setTimeout(function (){
                        $scope.$apply(function(){
                            vm.successful = false;
                        });
                    }, 5000);
                } else {
                    console.log('didnt send');
                }
            })
            .catch((error) => {
                console.log(error);
            });

        }

    }

})();
