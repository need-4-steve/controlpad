@extends('layouts.boss')
@section('angular-controller')
ng-controller="SubscriptionController as sub"
@endsection
@section('sub-title')
    My Monthly Plan
@endsection
@section('content')
@inject('_settings', 'globalSettings')
<div class="success-message align-center" ng-if="sub.success">Monthly plan extended until <span ng-bind="sub.renewSub.ends_at | myDateFormat"></span></div>
<div class="warning-message align-center" ng-if="sub.failure">Failed to extend monthly plan.  Please verify your banking information in your account settings.</div>
<div ng-cloak>
    <div id="renewModal" class="modal fade" tabindex="-1" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="renewSubscriptionModal" class="" action="{{ url('/') }}" method="POST">
                    {{csrf_field()}}
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title">Extend your monthly plan</h4>
                    </div>
                    <div class="modal-body">
                        <p>This will extend your current monthly plan by @{{sub.subscription.plan.duration}} days. All {{$_settings->getGlobal('company_name', 'value')}} monthly plans will automatically renew and bill from your payment bank account information on file. To change or view the account, visit My Account under your name in the top right. The monthly plan cost is @{{sub.subscription.price | currency}}. Would you like to pay early?</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                        <button type="button" class="cp-button-standard" data-dismiss="modal" ng-click="sub.renew()">Pay Now</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="rep-dash">
        <div class="panel panel-default">
            <div class="panel-heading gray">
                <h2 class="panel-title align-center">Current Monthly Plan</h2>
            </div>
            <table class="table">
                <thead>
                    <tr>
                        <th>Purchased</th>
                        <th>Next Payment Due</th>
                    </tr>
                </thead>
                <tbody>
                    <tr ng-repeat="subscription in sub.subscription.user.subscriptions">
                        <td ng-bind="subscription.created_at | myDateFormat"></td>
                        <td ng-bind="subscription.ends_at | myDateFormat"></td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="flex-end rep-dash">
            <a class="btn" data-toggle="modal" data-target="#renewModal">Add 30 days to your monthly plan</a>
        </div>
        <div class="align-center">
            <img class="loading" src="{{$_settings->getGlobal('loading_icon', 'value')}}" ng-if="sub.loading">
        </div>
    </div>
</div>
@endsection
