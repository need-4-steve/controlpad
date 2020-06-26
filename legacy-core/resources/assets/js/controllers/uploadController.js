(function(){
'use strict'
angular
    .module('app')
    .controller('UploadController', UploadController)
    UploadController.$inject = ['$scope', 'MediaService'];
    function UploadController($scope, MediaService) {

        $scope.addedImage = [];
        $scope.mediaCreated = false;
        $scope.imagesDropped = false;
        $scope.fromDatabase = true;

        $scope.createMedia = function(){
            $scope.mediaForm = {
                'title': $scope.title,
                'description': $scope.description,
                'images': $scope.addedImage
            }
            MediaService.mediaCreate($scope.mediaForm)
            .then((response) => {
                $scope.mediaCreated = true;
                $scope.title = "";
                $scope.description = "";
                Dropzone.forElement("#drop-zone-media").removeAllFiles(true);
                setTimeout(function (){
                    $scope.$apply(function(){
                        $scope.mediaCreated = false;
                    });
                }, 5000);
            })
            .catch((error) => {
                console.log(error);
            });
        }

        //dropzone options for media/create
        Dropzone.options.dropZoneMedia = {
            maxFileSize: 10,
            maxFiles: 1,
            dictDefaultMessage: "Drop file here or click to upload",
            dictMaxFilesExceeded: "You may only upload one file",
            addRemoveLinks: true,
            dictRemoveFile: "Delete",
            init: function(){
                this.on("removedfile", function(id, index){
                    $scope.addedImage.splice(index, 1);
                    MediaService.deleteFile($scope.imageId)
                        .then((response) => {
                            $scope.deletedResponse = response;
                        })
                        .catch((error) => {
                            console.log(error);
                        });
                })
            },
            success: function(file, response){
                $scope.imageId = response.id;
                $scope.addedImage.push($scope.imageId);
            }
        }

        // dropzone options for product/create
        Dropzone.options.dropZone = {
            maxFiles: 10,
            maxFileSize: 10,
            acceptedFiles: ".png, .jpg, .jpeg, .gif, .bmp, .tiff",
            dictDefaultMessage: "Drop files here or click to upload",
            dictRemoveFile: "Delete",
            addRemoveLinks: true,
            init: function(){
                var myDropzone = this;
                myDropzone.on('removedfile', function(file){
                    angular.forEach($scope.addedImages, function(image, key){
                        if (image.filename == file.name) {
                            $scope.images = image.id;
                            if ($scope.images == undefined) {
                                $scope.images = image;
                            }
                            $scope.addedImages.splice(key, 1);
                        }
                    });
                });
            },
            success: function(file, response){
                file.imageId = response.id;
                $scope.addedImages.push(response.id);
            },
            removedfile: function(file) {
                var _ref;
                if (file.previewElement) {
                    if ((_ref = file.previewElement) != null) {
                        _ref.parentNode.removeChild(file.previewElement);
                    }
                }
                for (var i = 0; i < $scope.addedImages.length; ++i) {
                    if ($scope.addedImages[i] == file.imageId) {
                        $scope.addedImages.splice(i, 1);
                    }
                }
                return this._updateMaxFilesReachedClass();
            }
        }

    }
})();
