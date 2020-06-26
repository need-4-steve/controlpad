@extends('layouts.master')
@section('angular-app')
ng-app="app"
@endsection
@section('sub-layout')
   @yield('content')
@endsection