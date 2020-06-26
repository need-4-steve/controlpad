(function(){
"use strict"
    angular
    .module('app')
	.controller('UserSiteController', UserSiteController)
	UserSiteController.$inject = ['$scope', '$http'];
	function UserSiteController($scope, $http) {

		$scope.loading = true;
		$scope.currentPage = 1;
		$scope.pageSize = 15;

		$http.get('/api/v1/userSites').success(function(userSites) {
			$scope.userSites = userSites;
			$scope.checkbox = checkbox();
			$scope.loading = false;
		});

		$scope.pageChangeHandler = function(num) {

		};
	};
})();
