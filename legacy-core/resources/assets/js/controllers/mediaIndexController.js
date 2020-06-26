(function(){
'use strict';
    angular
    .module('app')
    .controller('MediaIndexController', MediaIndexController)
    MediaIndexController.$inject = ['$scope', '$http'];
    function MediaIndexController($scope, $http) {

        var requests = [
            'shared-with-representatives',
            'resources-uploaded-by-reps',
            'my-resources'
        ];
        var type = 'all';
        var file_count_url = "";
        var media_url = '/api/v1/media';
        $scope.title = "All Resources";
        $scope.loading = true;
        $scope.media_tag = 'all';
        $scope.media_type = 'all';
        $scope.media_types = [];
        $scope.showing_index = null;
        $scope.selected_type = "";
        $scope.selected_tag = "";
        $scope.filtertools = false;
        // hide if object empty
        $scope.val = "";

        /*
        * loop through requests and set ajax request accordingly
        * see helpers.js for getUrlVariables() function
        */
        angular.forEach(requests, function(request) {
            if (getUrlVariables('tag-and-type')) {
                var tag = getUrlVariables('tag');
                tag = tag.replace(/%20/g, " ");
                $scope.title = tag.capitalize();
                media_url = '/api/v1/media/tag-and-type/' + tag + '/' + getUrlVariables('type');
            } else if (getUrlVariables('by-type')) {
                type = getUrlVariables('type')
                $scope.title = 'All ' + type + ' files'
                media_url = '/api/v1/media/by-type/' + type;
            } else if (getUrlVariables('shared-with-representatives')) {
                media_url = '/api/v1/media/shared-with-representatives';
                $scope.title = "Shared With Reps";
            } else if (getUrlVariables('resources-uploaded-by-reps')) {
                media_url = '/api/v1/media/resources-uploaded-by-reps';
                $scope.title = "Uploaded by Reps";
            } else if(getUrlVariables(request)) {
                media_url = '/api/v1/media/' + request;
                var title = request.replace(/-/g, ' ');
                $scope.title = title.capitalize();
                $scope.filtertools = true
            }
        });
        //show filter tools when showing all media
        if (media_url === '/api/v1/media') {
            $scope.filtertools = true;
        }

        // toggle sort buttons
        $('.btn-group .btn').click(function() {
            $(this).parent().children('.btn').removeClass('active');
            $(this).addClass('active');
        });

        // get all media types from json media object
        $scope.getMediaTypes = function(media_data) {
            angular.forEach(media_data, function(media) {
                if ($scope.media_types.indexOf(media.type) == -1) {
                    $scope.media_types.push(media.type);

                }
            });
        }

        // an index of all media
        $scope.getMediaAll = function () {
            $scope.loading = true;
            $http.post(media_url, {'searchTerm':'', 'limit':'50'}).success(function(media){
                $scope.media = media.data;
                $scope.filteredMedia = $scope.media;
                $scope.getMediaTypes($scope.media);
                $scope.loading = false;
            });
        }

        $scope.getMediaByType = function() {
            if ($scope.selected_type === "") {
                $scope.getMediaAll();
            } else {
                $scope.loading = true;
                $http.get('/api/v1/media/by-type/' + $scope.selected_type).success(function(media) {
                    $scope.media = media;

                    $scope.filteredMedia = $scope.media;
                    $scope.loading = false;
                });
            }
        }

            // Shows/hides the options on hover
            $scope.hoverOn = function(media) {
                return media.showOptions = true;
            };
            $scope.hoverOff = function(media) {
                return media.showOptions = false;
            };
            // select all
            $scope.selectAll = function() {
                // determine whether to select all or unselect all
                angular.forEach($scope.media, function(media) {
                    if (media.selected == undefined || media.selected == false) {
                        select_all = true;
                    }
                    else select_all = false;
                });
                // select all
                if (select_all == true) {
                    angular.forEach($scope.media, function(media) {
                        media.selected = true;
                    });
                }
                // unselect all
                else {
                    angular.forEach($scope.media, function(media) {
                        media.selected = false;
                    });
                }
            }

            // view file
            $scope.viewFile = function(id, $index) {
                $http.get('/media/ajax/' + id).success(function(data) {
                    $('#modal #ajax-content').html(data);
                    $('#modal').modal('toggle');
                    $scope.showing_index = $index;
                    //console.log($scope.showing_index);
                });
            }

            // download file
            $scope.download = function(url) {
                window.location.href = '/uploads/' + url;
            }

            // delete file
            $scope.deleteFile = function(id) {
                setTimeout(function() {
                    // throw new Error("my error message");
                    var confirm = confirm('Are you sure you want to delete this file?');
                    if (confirm == true) {
                        $http.get('/media/destroy/' + id).success(function() {
                            $scope.media.splice($scope.media[id], 1);
                            $scope.media_counts --;
                        });
                    }
                }, 1);
            }

            // change file
            $('body').on('click', '.changeFile', function() {
                console.log($scope.filteredMedia);
                $scope.length = $scope.filteredMedia.length - 1;
                $('.changeFileButtons').after('<img src="/img/loading.gif">');
                var direction = $(this).attr('data-direction');
                if (direction == 'forward') {
                    if ($scope.showing_index + 1 <= $scope.length) $scope.showing_index ++;
                    else $scope.showing_index = 0;
                }
                else {
                    if ($scope.showing_index - 1 >= 0) $scope.showing_index --;
                    else $scope.showing_index = $scope.length;
                }
                id = $scope.filteredMedia[$scope.showing_index].id;
                $http.get('/media/ajax/' + id).success(function(data) {
                    $('#modal #ajax-content').fadeOut(function() {
                        $(this).html(data).fadeIn();
                    });
                });
            });

        $scope.getMediaAll();


        $http.get(file_count_url).success(function(data) {
            $scope.media_counts = data;
        });

    }
})();
