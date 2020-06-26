@inject('_settings', 'globalSettings')
@extends('layouts.store')
@section('content')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/font-awesome@4.6.1/css/font-awesome.min.css">
<link rel="stylesheet" type="text/css" href="{{  asset('css/flexslider.css') }}"/>
 <input id="store-owner" type="hidden" value="{{ $storeOwnerId }}">
 <div id="event-app">
    <section class="home-blog slider">
      <div class="flexslider padding-none">
        <ul class="slides padding-none">
          <li v-if="singlebanner.show == 1"  v-for="singlebanner in banneritems"> <img :src="singlebanner.value"  /> </li>
        </ul>
      </div>
    </section>
    <span class="control-hide" id="lbl_banner_1">{{ $_settings->getStore('banner_image_1') }}</span>
    <span class="control-hide" id="lbl_banner_2">{{ $_settings->getStore('banner_image_2') }}</span>
    <span class="control-hide" id="lbl_banner_3">{{ $_settings->getStore('banner_image_3') }}</span>
    <span class="control-hide" id="lbl_show_banner_1">{{ $_settings->getStore('show_banner_image_1') }}</span>
    <span class="control-hide" id="lbl_show_banner_2">{{ $_settings->getStore('show_banner_image_2') }}</span>
    <span class="control-hide" id="lbl_show_banner_3">{{ $_settings->getStore('show_banner_image_3') }}</span>
    @if (!isset($store->rep) || !$store->rep->settings->hide_products)
    <div class="event-detail-change">
          <h1>{{ $_settings->getGlobal('events_title', 'value')['plural'] }}</h1>
          <div class="cp-grid-standard">
              <div class="cp-cell-5" v-for="(event, index) in events">
                  <div v-if="event.items_purchased >= event.items_limit && event.items_limit !== null" class="product-item sold-out">
                    <img :src="event.img" :alt="event.name" />
                    <h3>Sold Out</h3>
                    <strong>@{{ event.name }}</strong>
                    <br />
                    <span v-if="event.host_name !== null">
                    <i>Host</i>: @{{ event.host_name }}
                    </span>
                  </div>
                  <span v-else>
                  <a :href="'/store/events/' + event.id">
                      <img v-if="event.img" :src="event.img" :alt="event.name" />
                      <strong>@{{ event.name }}</strong>
                      <br />
                      <span v-if="event.host_name !== null">
                      <i>Host</i>: @{{ event.host_name }}
                      </span>
                  </a>
                  </span>
              </div>
          </div>
      </div>
      @endif
</div>
<style type="text/css">
.padding-none
{
  padding:0;
}
.event-detail-change
{
  max-width: 1440px; margin: 0 auto; text-align: center;
}
.control-hide
{
  display:none;
}
</style>
@endsection
@section('scripts')
<script src="https://unpkg.com/vue@2.4.2/dist/vue.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/vue-resource/1.5.0/vue-resource.min.js"></script>
    <script type="text/javascript">
        /* global Vue */
        const endpoint = "{{ env('TEST_EVENTS_API', 'https://events.controlpadapi.com/api/v0/') }}"
        const eventApp = new Vue({
          el: '#event-app',
          data: {
            events: [],
            eventRequest: {
              sponsor_id: document.getElementById('store-owner').value
            },
            banneritems:[{value :$("#lbl_banner_1").text(),show:$("#lbl_show_banner_1").text()},{value :$("#lbl_banner_2").text(),show:$("#lbl_show_banner_2").text()},{value :$("#lbl_banner_3").text(),show:$("#lbl_show_banner_3").text()}]
          },
          mounted () {
            this.getEvents(),
            this.getStoreSettings()
          },
          methods: {
            getStoreSettings: function () {



              console.log(this.banneritems);



            },
            getEvents () {
              this.$http.get(endpoint + 'events', { params: this.eventRequest })
                .then(function (response) {
                  this.events = response.data.data
                }, function (error) {
                  console.log(error.statusText)
                })
            }
          }
        })
    </script>
@endsection
