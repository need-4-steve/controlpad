(function(){
'use strict'
    angular
    .module('app')
    .controller('CategoryController', CategoryController)
    CategoryController.$inject = ['$scope', '$http', '$window', 'CategoryService']
    function CategoryController($scope, $http, $window, CategoryService){

        $scope.categoryList = ""
        $scope.categories = []
        $scope.categoryCreated = false
        $scope.firstSelect = {}
        $scope.secondSelect = {}
        $scope.showOnStore = true
        $scope.pageSize = 30
        $scope.currentPage = 1
        $scope.catAddSuccess = false
        $scope.catAddSuccessMessage = "Category Added Successfully!"
        $scope.addedImage = []
        $scope.loading = true
        $scope.subcategories = []
        $scope.subCategories = []
        $scope.noneObject = {
            name: 'None',
            id: 'none'
        }

        $scope.getCategories = function(){
            CategoryService.categoriesGet()
                .then((response) => {
                    $scope.categories = response
                    $scope.loading = false
                })
                .catch((error) => {
                    console.log(error)
                })
        }
        $scope.getCategories()

        $scope.getSubCategories = function(){
            CategoryService.subCategoriesGet()
                .then((response) => {
                    $scope.subCategories = response
                    if ($scope.firstSelect) {
                        $scope.subCategories.unshift($scope.noneObject)
                    }
                    $scope.loading = false
                })
                .catch((error) => {
                    console.log(error)
                })
        }
        $scope.getSubCategories();

        $scope.createCategory = function(){
            if ($scope.catId == undefined){
                $scope.formObject = {
                    name: $scope.catName,
                    show_on_store: $scope.showOnStore,
                    parent_id: null,
                    file: $scope.addedImage
                }
            } else {
                $scope.formObject = {
                    name: $scope.catName,
                    show_on_store: $scope.showOnStore,
                    parent_id: $scope.catId,
                    file: $scope.addedImage
                }
            }
            CategoryService.categoryCreate($scope.formObject)
                .then((response) => {
                    $scope.categoryCreated = true
                    $scope.catId = response.id
                    window.scrollTo(0,0)
                    $scope.catAddSuccess = true;
                    setTimeout(function (){
                        $scope.$apply(function(){
                             $scope.catAddSuccess = false
                        });
                    }, 3000);
                })
                .catch((error) => {
                    console.log(error)
                })
        }

        $scope.addSubCatToProduct = function() {
            if ($scope.firstSelect.name === undefined && $scope.secondSelect.id === 'none'){
                $scope.secondSelect = ''
                return false
            }
            if ($scope.secondSelect.id === 'none') {
                $scope.addedCategories.push($scope.firstSelect)
                $scope.addedCategoriesId.push($scope.firstSelect.id)
                $scope.addedCategories.name = $scope.firstSelect.name
            }
            else {
                $scope.addedCategories.push($scope.secondSelect)
                $scope.addedCategoriesId.push($scope.firstSelect.id)
                $scope.addedCategoriesId.push($scope.secondSelect.id)
            }
            $scope.secondSelect = ''
            $scope.firstSelect = ''
        }

        $scope.removeCategory = function($index){
            $scope.addedCategories.splice($index, 1)
            $scope.addedCategoriesId.splice($index, 1)
        }

        $scope.checkbox = function() {
            checkbox()
        }

        //dropzone options for category/create
        Dropzone.options.dropZoneMedia = {
            maxFileSize: 10,
            maxFiles: 1,
            dictDefaultMessage: 'Drop file here or click to upload',
            dictMaxFilesExceeded: 'You may only upload one file',
            init: function(){
                var self = this;
                self.options.addRemoveLinks = true
                self.options.dictRemoveFile = 'Delete'
            },
            success: function(file, response){
                $scope.imageId = response.id
                $scope.addedImage.push($scope.imageId)
            },
        }

        $scope.addSubcategory = function(){
            $scope.subObject = {
                name: $scope.subcategory.name,
                parent_id: $scope.catId,
            }
            CategoryService.categoryCreate($scope.subObject)
                .then((response) => {
                    $scope.subcategory.name = ''
                    $scope.subcategories.push(response)
                })
                .catch((error) => {
                    console.log(error)
                });
        }

        $scope.deleteCategory = function(category, index){
            $scope.addedCategories.splice(index, 1)
            CategoryService.deleteCategory(category.id)
            .then((response) => {
                $scope.categoryDeleted = response
            })
            .catch((error) => {
                console.log(error)
            });
        }

        //used at /category/create for deletion
        $scope.deleteSubcategory = function(subcategory, index){
            $scope.subcategories.splice(index, 1)
            CategoryService.deleteCategory(subcategory.id)
                .then((response) => {
                    $scope.subCatDeleted = response
                })
                .catch((error) => {
                    console.log(error)
                });
        }
    }
})();
