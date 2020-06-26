(function(){
'use strict';
    angular
    .module('app')
    .directive('cpUpload', function(){
        return {
            restrict: 'AE',
            replace: true,
            scope: {
              name: "@"
            },
            templateUrl: '/templates/cp-upload.html',
            controller: function($scope) {
                    document.getElementById("uploadBtn").onchange = function () {
                    document.getElementById("uploadFile").value = this.value;
                };
            }
        }
    });
})();
