(function(){
'use strict'
    angular
    .module('app')
    .service('ProductService', ProductService)
    ProductService.$inject = ['$http'];
    function ProductService($http){
        return {
            productTypeGet: function(){
                return $http.get('/api/v1/products/type')
                    .then((response) => {
                        return response;
                    })
                    .catch((err) => {
                    return err;
                    })
            },
            rolesGet: function(){
                return $http.get('/api/v1/roles')
                    .then((response) => {
                        return response;
                    })
                    .catch((err) => {
                        return err;
                    })
            },
            bundleCreate: function(data){
                return $http.post('/api/v1/bundles/create', data)
                    .then((response) => {
                        return response;
                    })
                    .catch((err) => {
                        return err;
                    })
            },
            bundlesGet: function(data) {
                return $http.get('/api/v1/bundles', data)
                    .then((response) => {
                        return response.data;
                    })
                    .catch((err) => {
                        return err;
                    })
            },
            bundlesGetByRole: function(bundleRequest) {
                return $http.get('/api/v1/bundles/bundles-by-role', {params:bundleRequest})
                    .then((response) => {
                        return response.data;
                    })
                    .catch((err) => {
                        return err;
                    })
            },
            bundlesFulfilledGetByRole: function(fulfilledRequest) {
                return $http.get('/api/v1/bundles/bundles-by-role-fulfilled', {params:fulfilledRequest})
                    .then((response) => {
                        return response.data;
                    })
                    .catch((err) => {
                        return err;
                    })
            },
            productCreate: function(data){
                return $http.post('/api/v1/products/create', data)
                    .then((response) => {
                        return response;
                    })
                    .catch((err) => {
                        return err;
                    })
            },
            productEdit: function(data){
                return $http.put('/api/v1/products/edit', data)
                    .then((response) => {
                        return response;
                    })
                    .catch((err) => {
                        return err;
                    })
            },
            productsGet: function(productRequest){
                return $http.get('/api/v1/products/wholesale-store', {params:productRequest})
                    .then((response) => {
                        return response.data;
                    })
                    .catch((err) => {
                        return err;
                    })
            },
            productGet: function(id){
                return $http.get('/api/v1/products/show/' + id)
                    .then((response) => {
                        var product = response.data;
                        var items = response.data.items;
                        var responseObject = {
                            product: product,
                            items: items
                        }
                        return responseObject;
                    })
                    .catch((err) => {
                        return err;
                    })
            },
            productWholesaleGet: function(id){
                return $http.get('/api/v1/products/wholesale/' + id)
                    .then((response) => {
                        var product = response.data;
                        var items = response.data.items;
                        var responseObject = {
                            product: product,
                            items: items
                        }
                        return responseObject;
                    })
                    .catch((err) => {
                        return err;
                    })
            },
            getProductEdit: function(id){
                return $http.get('/api/v1/products/show-edit/' + id)
                    .then((response) => {
                        var product = response.data;
                        var items = response.data.items;
                        var responseObject = {
                            product: product,
                            items: items
                        }
                        return responseObject;
                    })
                    .catch((err) => {
                        return err;
                    })
            },
            bundleGet: function(id){
                return $http.get('/api/v1/bundles/show/' + id)
                    .then((response) => {
                        return response.data;
                    })
                    .catch((err) => {
                        return err;
                    })
            },
            deleteProduct: function(id){
                return $http.delete('/api/v1/products/delete/' + id)
                    .then((response) => {
                        return response.data;
                    })
                    .catch((err) => {
                        return err;
                    })
            },
            deleteBundle: function(id){
                return $http.delete('/api/v1/bundles/delete/' + id)
                    .then((response) => {
                        return response.data;
                    })
                    .catch((err) => {
                        return err;
                    })
            },
            bundleUpdate: function(data){
                return $http.put('/api/v1/bundles/edit/'+ data.id, data)
                    .then((response) => {
                        return response.data;
                    })
                    .catch((err) => {
                        return err;
                    })
            },
            deleteItem: function(id){
                return $http.delete('/api/v1/items/'+ id)
                    .then((response) => {
                        return response.data;
                    })
                    .catch((err) => {
                        return err;
                    })
            }
        }
    }
})();
