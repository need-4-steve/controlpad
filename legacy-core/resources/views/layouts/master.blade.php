<!DOCTYPE html>
<html @yield('angular-app')>
    <head>
        @inject('_settingsRepo', 'settingsRepo')
        <meta charset="utf-8">
        <meta name="description" content="">
        <meta name="author" content="">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf_token" content="{{ csrf_token() }}" id="token"/>
        <meta name="newSettings" content="{{ json_encode($_settingsRepo->index()) }}" id="new-global-settings"/>
        <title>{{$_settings->getGlobal('company_name', 'value')}}</title>
        @yield('meta')
        <link rel="icon" href="{{ $_settings->getGlobal('favicon', 'value') }}">
        @section('css')
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/angular-material@1.1.1/angular-material.min.css">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@3.3.6/dist/css/bootstrap.css">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@3.3.6/dist/css/bootstrap-theme.min.css">
        <link rel="stylesheet" type="text/css" href="{{  asset('packages/bootstrap-select/bootstrap-select.min.css') }}"/>
        <link rel="stylesheet" type="text/css" href="{{  asset('css/bootstrap-xl.css') }}"/>
        <link rel="stylesheet" type="text/css" href="{{  asset('packages/jquery-ui/jquery-ui-1.10.4.custom.min.css') }}"/>
        <link rel="stylesheet" type="text/css" href="{{  asset('css/animate.css') }}"/>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/font-awesome@4.6.1/css/font-awesome.min.css">
        <link rel="stylesheet" href="https://cdn.materialdesignicons.com/2.1.99/css/materialdesignicons.min.css"/>
        <link rel="stylesheet" type="text/css" href="{{  asset('css/croppie.css') }}"/>
        @show
        @include('layouts.partials.analytics')
        <script src="https://cdn.jsdelivr.net/npm/jquery@2.2.3/dist/jquery.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@3.3.6/dist/js/bootstrap.min.js"></script>
        <script src="{{ asset('packages/bootstrap-select/bootstrap-select.min.js') }}"></script>
        <script src="https://cdn.jsdelivr.net/npm/angular@1.5.8/angular.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/angular-animate@1.5.5/angular-animate.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/angular-aria@1.5.8/angular-aria.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/angular-ui-bootstrap@2.5.6/dist/ui-bootstrap-tpls.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/angular-sanitize@1.5.5/angular-sanitize.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/angular-material@1.1.1/angular-material.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/moment@2.14.1/min/moment.min.js"></script>
        <script src="{{ asset('packages/jquery-ui/jquery-ui-1.10.4.custom.min.js') }}"></script>
        <script src="https://cdn.jsdelivr.net/npm/angular-slick-carousel@3.1.7/dist/angular-slick.min.js"></script>
        <script src="{{ angularBuild('angular/angApp.js') }}"></script>
        <script src="{{ asset('js/functions.js') }}"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/flexslider/2.7.1/jquery.flexslider.min.js"></script>
        <script src="{{ asset('packages/jquery-ui/timepicker.js') }}"></script>
        <script type="text/javascript">
    $(window).load(function(){
      $('.flexslider').flexslider({
        animation: "slide",
        start: function(slider){
          $('body').removeClass('loading');
        }
      });
    });
  </script>
        {{-- Date Time Picker --}}
        <!-- Date-picker itself -->
        <script src="https://unpkg.com/eonasdan-bootstrap-datetimepicker@4.17.47/build/js/bootstrap-datetimepicker.min.js"></script>
        <link href="https://unpkg.com/eonasdan-bootstrap-datetimepicker@4.17.47/build/css/bootstrap-datetimepicker.min.css" rel="stylesheet">
        <script src="https://unpkg.com/vue-bootstrap-datetimepicker"></script>
        @yield('head')
        <script>
            var domain = '{{ Config::get("site.domain") }}'
            var path = '{{ Request::path() }}'
        </script>
        <!-- Load Facebook SDK for JavaScript -->
        <script>(function(d, s, id) {
          var js, fjs = d.getElementsByTagName(s)[0];
          if (d.getElementById(id)) return;
          js = d.createElement(s); js.id = id;
          js.src = "//connect.facebook.net/en_US/sdk.js#xfbml=1&version=v2.6";
          fjs.parentNode.insertBefore(js, fjs);
        }(document, 'script', 'facebook-jssdk'));</script>
    </head>

    <body class="layout-default" ng-cloak @yield('angular-controller')>
        <div id="fb-root"></div>
        @yield('header')

      <div id="vue-app">
        @yield('sub-layout')

      </div>
        @yield('footer')

        @yield('scripts')
    </body>
</html>
