(function(){
'use strict';
	angular
	.module('app')
	.factory('CategoryService', CategoryService)
	CategoryService.$inject = ["$http"]
	function CategoryService($http) {

		var productsByCategory;

		return {
			productsByCategory: productsByCategory,

			categoriesGet: function(){
				return $http.get('/api/v1/categories/hierarchy')
				.then((response) => {
					return response.data;
				})
				.catch((error) => {
					return error;
				});
			},
			subCategoriesGet: function(){
				return $http.get('/api/v1/categories/children')
				.then((response) => {
					return response.data;
				})
				.catch((error) => {
					return error;
				});
			},
			categoryGet: function(id){
				return $http.get('/api/v1/products/products-by-category/' + id)
				.then((response) => {
					this.productsByCategory = response.data;
					return this.productsByCategory;
				})
				.catch((error) => {
					return error;
				});
			},
			productsGet: function(){
				return this.productsByCategory;
			},
			showCategoryGet: function(id){
				return $http.get('/api/v1/categories/' + id)
				.then((response) => {
					return response.data;
				})
				.catch((error) => {
					return error;
				});
			},
			editCategoryPut: function(category){
				return $http.patch('/api/v1/categories/' + category.id, category)
				.then((response) => {
					return response.data;
				})
				.catch((error) => {
					return error;
				});
			},
			deleteCategory: function(id){
				return $http.delete('/api/v1/categories/' + id)
				.then((response) => {
					return response;
				})
				.catch((error) => {
					return error;
				});
			},
			categoryCreate: function(category){
				return $http.post('/api/v1/categories', category)
				.then((response) => {
					return response.data;
				})
				.catch((error) => {
					return error;
				});
			}
		}
	}
})();
