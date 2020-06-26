(function(){
  'use strict';
  angular
  .module('app')
  .controller('ProductShowController', ProductShowController)
  ProductShowController.$inject = ['$scope', '$window', 'CartService', 'CategoryService'];
  function ProductShowController($scope, $window, CartService, CategoryService) {
    $scope.childTags = false;
    $scope.showTags = false;
    $scope.fixed = false;
    $scope.fill = false;
    $scope.showErr = false;
    $scope.count = 0;
    $scope.cartLineObj = {};
    $scope.cartLineObj.quantity = 1;
    $scope.cartLineObj.item_id = null;
    $scope.catHierarchy = [];
    $scope.inventoryCheck = {};
    $scope.inventoryError = false;
    $scope.inventoryMessage = '';
    $scope.successMessage = false;
    $scope.minMaxError = false;
    $scope.errorMessage = "";
    $scope.eventId = null;
    $scope.autoGetCart = true;

    $scope.lgCarousel = {
      initialSlide: 0,
      slidesToShow: 1,
      slidesToScroll: 1,
      lazyLoad: 'ondemand',
      fade: true,
      arrows: false,
      swipe: true,
      touchMove: true
    };
    $scope.slickConfig = {
      initialSlide: 0,
      slidesToShow: 4,
      slidesToScroll: 1,
      lazyLoad: 'ondemand',
      asNavFor: '.lg-img',
      focusOnSelect: true,
      arrows: true,
      touchMove: true,
      easing: 'easeOutElastic',
      responsive: [
        {
          breakpoint: 767,
          settings: {
            slidesToShow: 3
          }
        },
        {
         breakpoint: 600,
          settings: {
            slidesToShow: 1
          }
        }
      ]
    };
    $scope.recommendedItems = {
      initialSlide: 0,
      slidesToShow: 4,
      slidesToScroll: 1,
      lazyLoad: 'ondemand',
      focusOnSelect: true,
      arrows: true,
      touchMove: true,
      easing: 'easeOutElastic',
      responsive: [
        {
            breakpoint: 768,
            settings: {
                slidesToShow: 3
            }
        },
        {
            breakpoint: 480,
            settings: {
                slidesToShow: 1
            }
        }
      ]
    };

    angular.element($window).bind("scroll", function(){
      $scope.pageOffset = window.pageYOffset;
      if ($scope.pageOffset >= 80) {
        $scope.fixed = true;
        $scope.fill = true;
      } else {
        $scope.fixed = false;
        $scope.fill = false;
      }
      $scope.$apply();
    });

    $scope.checkInv = function () {
      if ($scope.cartLineObj.quantity !== null && $scope.cartLineObj.quantity > 0){
        var itemToCheck = {
          'item_id': $scope.selectedItem.id,
          'quantity': $scope.cartLineObj.quantity,
          'corporate': $scope.selectedItem.corporate
        };
        CartService.checkInventory(itemToCheck)
          .then((response) => {
            if (response.error == true) {
              $scope.inventoryError = true;
              $scope.inventoryMessage = response.message;
              $scope.cartLineObj.quantity = response.quantity_available;
            }
          }).catch((err) => {
            console.log(err);
          });
        }
    }

    $scope.addToCart = function() {
      console.log('addToCart clicked');
      CartService.addItem(
        $scope.selectedItem.id,
        $scope.cartLineObj.quantity,
        $scope.eventId
      )
        .then((response)=>{
          $scope.successMessage = true;
          window.scrollTo(0,0);
          if(response.status === 400) {
            $scope.minMaxError = true
            $scope.successMessage = false
            $scope.errorMessage = response.data.message
            console.log($scope.errorMessage)
            window.scrollTo(0,0)
          }
          $scope.$apply();
        })
        .catch((err)=> {
           if(err) $scope.showErr = true;
        })
      $scope.showErr = true;
      $scope.count ++;
      $scope.product_id = $('.addProduct').attr('data-product-id');
      $('.addProduct').addClass('disabled').attr('disabled', 'disabled');
      $('#form').prepend('<input type="hidden" name="products[' + $scope.count + '][id]" value="' + $scope.product_id + '">');
    };

    $scope.closeSuccess = function(){
      location.reload();
    }
    $scope.getCategories = function(){
      CategoryService.categoriesGet()
      .then((response) => {
        $scope.catHierarchy = response;
      })
      .catch((error) => {
        console.log(error);
      });
    }
    $scope.getCategories();

    $scope.selectItems = function (svariant) {
      $scope.selectedVariant = svariant
      $scope.items = $scope.selectedVariant.items
      $scope.selectedItem = $scope.items[0]
      if ($scope.selectedVariant.images.length > 0) {
        $scope.images = $scope.selectedVariant.images
      } else {
        $scope.images = $scope.product.images
      }
      if ($scope.selectedVariant.description) {
        $scope.description = $selectedVariant.description
      } else {
        $scope.description = $scope.product.long_description
      }
      $scope.mainImage = $scope.images[0]
    }

    $scope.selectImage = function(image) {
      $scope.mainImage = image
    }

    $scope.itemSelected = function (item) {
      // Workaround for selectedItem not binding correctly
      $scope.selectedItem = item
    }
  }
})();
