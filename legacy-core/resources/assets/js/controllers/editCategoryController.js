(function(){
'use strict'
angular
    .module('app')
    .controller('EditCategoryController', EditCategoryController)
    EditCategoryController.$inject = ['$http', '$scope', 'CategoryService', '$window'];
    function EditCategoryController($http, $scope, CategoryService, $window) {
        var vm = this;
        vm.formObject = {};
        vm.successMessage = false;
        vm.deleted = false;
        vm.deleteMessage = false;
        vm.subcategories = [];
        vm.category = {};
        vm.catId = "";
        vm.categoryCreated = true;

        Dropzone.options.dropZoneMedia = {
            maxFileSize: 10,
            maxFiles: 1,
            dictDefaultMessage: "Drop file here or click to upload",
            dictMaxFilesExceeded: "You may only upload one file",
            init: function(){
                var self = this;
                self.options.addRemoveLinks = true;
                self.options.dictRemoveFile = "Delete";
            },
            success: function(file, response){
                vm.imageId = response.id;
            },
        }

        vm.editCategory = function() {
          vm.formObject = {
            'id': vm.category.id,
            'name': vm.category.name,
            'file': vm.imageId,
            'show_on_store': vm.category.show_on_store
          }
          console.log(vm.category.show_on_store)
            console.log(vm.formObject)
            CategoryService.editCategoryPut(vm.formObject)
                .then((response) => {
                    vm.successMessage = true
                    setTimeout(function (){
                        $scope.$apply(function(){
                            vm.successMessage = false;
                        });
                    }, 5000);
                })
                .catch((error) => {
                    console.log(error);
                });
        }

        vm.editSubCategory = function(sub) {
            CategoryService.editCategoryPut(sub)
                .then((response) => {
                    vm.successMessage = true
                    setTimeout(function (){
                        $scope.$apply(function(){
                            vm.successMessage = false;
                        });
                    }, 5000);
                })
                .catch((error) => {
                    console.log(error);
                });
        }
        vm.showCategory = function(id){
            CategoryService.showCategoryGet(id)
            .then((response) => {
                vm.category = response;
                vm.subcategories = response.children;
                vm.catId = response.id;
                vm.imageId = vm.category.media[0].id;
                var imageFile = {
                    url: vm.category.media[0].url_xs,
                    size: vm.category.media[0].size,
                }
                Dropzone.forElement("#drop-zone-media").emit("addedfile", imageFile);
                Dropzone.forElement("#drop-zone-media").emit("thumbnail", imageFile, vm.category.media[0].url_xs);
                Dropzone.forElement("#drop-zone-media").emit("complete", imageFile);
            })
            .catch((error) => {
                console.log(error);
            });
        }

        vm.deleteCategory = function(id){
            CategoryService.deleteCategory(vm.category.id)
            .then((response) => {
                if(response.status == 200){
                    vm.deleteMessage = false;
                    vm.deleted = true;
                    setTimeout(function (){
                        $scope.$apply(function(){
                            $window.location.href = '/category';
                        });
                    }, 200);
                }
            })
            .catch((error) => {
                console.log(error);
            });
        }

        vm.addSubcategory = function(){
            vm.subObject = {
                name: vm.subcategory.name,
                parent_id: vm.catId,
            }
            CategoryService.categoryCreate(vm.subObject)
                .then((response) => {
                    vm.subcategory.name = "";
                    vm.subcategories.push(response);
                })
                .catch((error) => {
                    console.log(error);
                });
        }
        vm.deleteSubcategory = function(subcategory, index){
            vm.subcategories.splice(index, 1);
            CategoryService.deleteCategory(subcategory.id)
                .then((response) => {

                })
                .catch((error) => {
                    console.log(error);
                });
        }

    }

})();
