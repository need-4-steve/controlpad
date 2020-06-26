@inject('_settings', 'globalSettings')
@extends('layouts.store')

@section('content')
<style>
    @media (max-width: 767px) {
        .event-details {
            text-align: center;
        }
        .desc-details {
            font-size: 130%;
            text-align: center;
        }
        .event-details-wrapper {
            margin: auto;
        }
        .event-header-wrapper {
            width: 90%;
            margin: 32px auto 0px;
        }
    }
    @media (min-width: 678px) {
        .event-header-wrapper {
            width: 75%;
            margin: 60px auto 0px;
        }
    }
    .filter-wrapper {
      height:auto !important;
    }
    .product-item.item{
      margin-right:10px;
      margin-left:10px;
    }
    body{
      font-family: "Open-Sans", "Helvetica Neue", "sans-serif" !important;
    }
    .cp-panel-border-change{
        margin: 24px 20px;
        padding: 0 20px;
        border: 1px solid rgb(238, 238, 238);
        box-sizing: border-box;
    }
    .product-change{
        padding-top:50px;
        float: none;
        text-align: left;
    }
    .event-title-row {
        display: flex;
        flex-wrap: wrap;
    }
    .event-details-wrapper {
        max-width: 800px;
    }
    .img-div{
        margin-bottom: 10px;
        margin-right: 10px;
        text-align: center;
    }
    .img-div-change{
        width:auto;
        max-height: 440px;
    }
    .event-details-change-h1{
        margin: 0;
    }
    .event-details-change{
        font-size: 22px;
        padding-top:15px;
    }
    .desc-details-div{
        font-size: 16px;
        margin-top: 30px;
    }
    .margin-auto{
        margin: 0 auto;
    }
    </style>
    <input id="store-owner" type="hidden" value="{{ $storeOwnerId }}">
     <div id="event-app">

       <div class="event-header-wrapper">
            <div class="event-title-row">
                <div class="img-div">
                    <img class="img-div-change" v-if="event.img" :src="event.img" :alt="event.name"/>
                </div>
                <div class="event-details-wrapper">
                  <div class="event-details">
                      <h1 class="event-details-change-h1">@{{ event.name }}</h1>
                    </div>
                    <div class="event-details event-details-change" v-if="event.host_name !== null">
                        <strong>Host: </strong> @{{ event.host_name }} <br />
                    </div>
                    <div class="event-details event-details-change">
                        <strong>Location: </strong> @{{ event.location }} <br />
                    </div>
                    <div class="event-details event-details-change">
                        <strong>{{ app('globalSettings')->getGlobal('events_title', 'single') }} Date: </strong> @{{ event.date | cpStandardDate }}<br />
                    </div>
                    <div class="event-details event-details-change">
                        <strong>{{ app('globalSettings')->getGlobal('events_title', 'single') }} Time: </strong> @{{ event.date | cpStandardTime }}<br />
                    </div>
                </div>
            </div>
            <div class="desc-details-div" v-if="event.description !== null">
                <strong>Description: </strong>  <div v-html="event.description"></div> <br />
            </div>
        </div>
        @if (!$store->rep->settings->hide_products)
            @include('product.partials.sort_form')
            @include('product.partials.products')
            {{ $store->products->appends($store->queryStrs)->links() }}
        @endif
    </div>
@endsection
@section('scripts')
    <script src="https://unpkg.com/vue@2.4.2/dist/vue.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/vue-resource/1.5.0/vue-resource.min.js"></script>
    <script type="text/javascript">
        /* global Vue */
        var path = window.location.pathname
        const eventId = path.substr(path.lastIndexOf('/') + 1)
        const endpoint = "{{ env('TEST_EVENTS_API', 'https://events.controlpadapi.com/api/v0/') }}"
        const eventApp = new Vue({
          el: '#event-app',
          data: {
            event: {},
            id: eventId,
            eventRequest: {
              sponsor_id: document.getElementById('store-owner').value
            }
          },
          filters: {
            cpStandardDate: (date) => {
              date = moment.utc(date).local()
              return date.format('MM/DD/YYYY')
            },
            cpStandardTime: (date) => {
              date = moment.utc(date).local()
              return date.format('hh:mm a z')
            }
          },
          mounted () {
            this.getEvents()
          },
          methods: {
            getEvents () {
              this.$http.get(endpoint + 'events/' + this.id, { params: this.eventRequest })
                .then(function (response) {
                  this.event = response.data
                }, function (error) {
                  console.log(error.statusText)
                })
            }
          }
        })
        eventApp
    </script>
@endsection
