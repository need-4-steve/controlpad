(function(){
'use strict';
    angular
    .module('app')
    .controller('StoreController', StoreController)
    StoreController.$inject = ['$scope', '$http', '$window','CategoryService'];
    function StoreController($scope, $http, $window, CategoryService) {

        $scope.loading = true;
        $scope.products = [];
        $scope.productByCategory = [];
        $scope.activeCategory = "";
        $scope.activeSlide = location.href.substr(location.href.lastIndexOf("=") + 1);
        $scope.catList = [];
        $scope.selectedIndex = "";
        $scope.autoGetCart = true;

        $scope.bodyHidden = function(){
            if ($("body").hasClass("hideBody")) {
                $("body").removeClass("hideBody");
            } else {
                $("body").addClass("hideBody");
            }
        }
        $scope.getCategories = function(){
            CategoryService.categoriesGet()
            .then((response) => {
                $scope.catList = response;
                $scope.getActiveCategory();
                $scope.catHierarchy = response;
            })
            .catch((error) => {
                console.log(error);
            });
        }
        $scope.getCategories();

        $scope.getActiveCategory = function() {
            for (var i = 0; i < $scope.catList.length; i++) {
                if ($scope.catList[i].id == $scope.activeSlide) {
                    $scope.activeCategory = $scope.catList[i].placement;
                    $scope.selectedIndex = $scope.catList[i].placement;
                }
            }
        }

        $scope.shBanners = {
            initialSlide: 0,
            slidesToShow: 1,
            slidesToScroll: 1,
            lazyLoad: 'ondemand',
            fade: true,
            arrows: false,
            swipe: true,
            touchMove: true,
            autoplay: true
        };

        $scope.catSlider = {
            slide: "li",
            initialSlide: 0,
            slidesToShow: 5,
            slidesToScroll: 1,
            touchMove: true,
            arrows: true,
            easing: 'easeOutElastic',
            responsive: [
                {
                    breakpoint: 1600,
                    settings: {
                        slidesToShow: 5
                    }
                },
                {
                    breakpoint: 1350,
                    settings: {
                        slidesToShow: 8
                    }
                },
                {
                    breakpoint: 800,
                    settings: {
                        slidesToShow: 6
                    }
                },
                {
                    breakpoint: 650,
                    settings: {
                        slidesToShow: 4
                    }
                },
                {
                    breakpoint: 480,
                    settings: {
                        slidesToShow: 2
                    }
                },
                {
                    breakpoint: 350,
                    settings: {
                        slidesToShow: 2
                    }
                }
            ]
        };
    }
})();
