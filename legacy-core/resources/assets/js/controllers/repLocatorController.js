(function(){
'use strict';
    angular
    .module('app')  
    .controller('repLocatorController', repLocatorController)
    repLocatorController.$inject = ['$scope', '$http'];
	function repLocatorController($scope, $http) {

		// initialize variables
		$scope.results = false;

		//find nearest reps by zip code
		$scope.searchZip = function() {
			$scope.loading = true;
			$http.get('/api/v1/repLocator/search/' + $scope.zip)
				.success(function(reps) {	
				$scope.loading = false;
				$scope.reps = reps;
				if($scope.reps.length > 0) {
					$scope.results = true;
				} else {
					$scope.results = false;
				}
			// validation
			}).error(function(data) {
				$scope.loading = false;
				if ($('.alert').length > 0) {
					$('.alert').slideUp(function() {
						$(this).remove();
						showAlert();
					});
				}
				else showAlert();
				function showAlert() {
					$('#main').prepend('<div class="alert alert-danger display-none">' + data.error_msg + '</div>');
					$('.alert').slideDown();
				}
			});
		};

	}
})();
