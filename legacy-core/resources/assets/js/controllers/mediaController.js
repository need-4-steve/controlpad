/***************************
 * Image Library
 ***************************/
(function(){
'use strict';
    angular
    .module('app')
    .controller('MediaController', MediaController)
    MediaController.$inject = ['$scope', '$http'];
	function MediaController($scope, $http) {

		// get images
		$scope.getImages = function(api_url) {
			$scope.loading = true;
			$http.get(api_url).success(function(media) {
				console.log(media);
				$scope.mediaList = media;
				$scope.loading = false;
			});
		};
		$scope.selectedCollection = '/api/v1/media/images';
		$scope.getImages($scope.selectedCollection);
		$scope.changeCollection = function() {
			$scope.getImages();
		};

		$scope.toggleSelected = function(media) {
			console.log(media);
			if (media.selected == undefined) {
				media.selected = true;
			} else {
				media.selected = undefined;
			}
		};

		// unselect all media
		$scope.unselect = function() {
			angular.forEach($scope.mediaList, function(media, key) {
				$scope.mediaList[key].selected = false;
			});
		};

		// insert image
		$scope.chooseImage = function() {
			if(typeof media_count === 'undefined') {
				media_count = $('#image-list > li').length;
			}
			var selected_images = 0;
			$scope.image_list = [];
			angular.forEach($scope.mediaList, function(file, key) {
				if(typeof file.selected !== 'undefined') {
					selected_images += 1;
					if(typeof image_id === 'undefined' || insert_type == 'wysiwyg_insert') {
						var destination = ".mce-combobox.mce-last.mce-abs-layout-item input.mce-textbox.mce-placeholder";
					}
					else {
						if (selected_images > 1) addImage();
						var destination = $('#image-list > li[data-empty="1"] input[name="images[' + media_count + '][id]"]');
						$('#image-list > li[data-empty="1"] .swappable').html('<img src="/img/loading.gif">');
						// push data about the destination into the file
						file.destination = media_count-1;
					}
					if(typeof destination !== 'undefined') $(destination).attr('value', file.largest);
					if(typeof image_id !== 'undefined') {
						var parent = $(destination).parents('.list-group-item');
						if(media_count == 0) checked = 'checked=""';
						else checked = '';
						$(parent).attr('data-empty', 0);
						$(parent).html('<img src="' + file.xs + '" class="thumb-md">' + '<i class="lnr lnr-cross removeImage pull-right removeImage"></i><br>' + '<label class="margin-top-2">' + '<input ' + checked + ' ng-model="images[' + media_count + '][featured]" name="images[' + media_count + '][featured]" type="radio">&nbsp;Featured Image' + '</label>' + '<input type="hidden" name="images[' + media_count + '][id]" value="' + file.id + '">' + '<input type="hidden" name="images[' + media_count + '][new_media]" value="1">' + '');
					}
					$('#image-list li:first input[name="images[1][featured]"]').prop('checked', true);
					// push the image to the list
					$scope.image_list.push(file);
				}
			});
			// when done perform image insert
			angular.forEach($scope.image_list, function(response, key){
				target = $('*[data-image-id="'+response.destination+'"]');
				$('.swappable', target).html('<img src="' + response.url_xs + '?id=' + response.id + '">');
				$('.id', target).val(response.id);
				target.attr('data-empty',0);
			});
		};

		// hide if object empty
		$scope.val = "";

		// download file
		$scope.download = function(url) {
			window.location.href = '/uploads/' + url;
		};

		// bulk action checkboxes
		$scope.checkbox = function() {
			var checked = false;
			$('.bulk-check').each(function() {
				if ($(this).is(":checked"))
					checked = true;
			});
			if (checked == true)
				$('.applyAction').removeAttr('disabled');
			else
				$('.applyAction').attr('disabled', 'disabled');
		};

		jQuery('.loading').remove();

		$scope.canShow = function(media) {
			return angular.equals(shownMedia, media);
		};

		$scope.currentPage = 1;
		$scope.pageSize = 20;

		$scope.pageChangeHandler = function(num) {

		};

	};
})();
