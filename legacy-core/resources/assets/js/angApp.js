(function(){
'use strict'
    angular
    .module('app', [
        'ui.bootstrap',
        'ngAnimate',
        'ngMaterial',
        'ngAria',
        'ngSanitize',
        'slickCarousel',
        'ngOrderObjectBy'
        ])

    .run(function run($http) {
        $http.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('#token').getAttribute('content')
    })

/*******
filters
********/

    // pagination filter
    .filter('startFrom', function(){
        return function(input, start){
            if (!input || !input.length) {
                return
            }
            start = +start
            return input.slice(start)
        }
    })

    .filter('filterDropdown', function () {
        return function (secondSelect, firstSelect) {
            var filtered = []
            if (firstSelect === null) {
                return filtered
            }
            angular.forEach(secondSelect, function (s2) {
                if (s2.parent_id === firstSelect.id || s2.id === 'none') {
                    filtered.push(s2)
                }
            })
            return filtered
        };
    })

    .filter('myDateFormat', function myDateFormat($filter){
        return function(text){
            var  tempdate= new Date(text.replace(/-/g,'/'))
            return $filter('date')(tempdate, 'MMM-dd-yyyy')
        }
    })

    // popover
    .directive('cpPopover', function() {
        return function(scope, element, attrs) {
            element.find('a[rel=popover]').popover({ placement: 'bottom', html: 'true'});
        };
    })

    .filter('phone', function () {
            return function (tel) {
            if (!tel) { return ''; }

            var value = tel.toString().trim().replace(/^\+/, '');

            if (value.match(/[^0-9]/)) {
                return tel
            }

            var country, city, number;

            switch (value.length) {
                case 10: // +1PPP####### -> C (PPP) ###-####
                    country = 1;
                    city = value.slice(0, 3);
                    number = value.slice(3);
                    break;

                case 11: // +CPPP####### -> CCC (PP) ###-####
                    country = value[0];
                    city = value.slice(1, 4);
                    number = value.slice(4);
                    break;

                case 12: // +CCCPP####### -> CCC (PP) ###-####
                    country = value.slice(0, 3);
                    city = value.slice(3, 5);
                    number = value.slice(5);
                    break;

                default:
                    return tel;
            }

            if (country == 1) {
                country = ''
            }

            number = number.slice(0, 3) + '-' + number.slice(3);

            return (country + " (" + city + ") " + number).trim();
        };
    });

})();
