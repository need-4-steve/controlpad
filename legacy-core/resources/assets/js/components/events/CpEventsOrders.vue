<template>
  <div id="" v-if="event">
    <p v-if="pageError"><strong class="errorText">{{ pageError }}</strong></p>
    <div class="event-data">
      <table>
        <tr>
          <th>Date:</th>
          <td>{{ event.date | cpStandardDate(true) }}</td>
        </tr>
        <tr>
          <th>Location:</th>
          <td>{{ event.location }}</td>
        </tr>
        <tr>
          <th>Host:</th>
          <td>{{ event.host_name }}</td>
        </tr>
        <tr>
          <th>Status:</th>
          <td>{{ event.status }}</td>
        </tr>
      </table>
    </div>
    <h3>Orders for {{ event.name }}</h3>
    <table class="cp-table-standard">
      <thead>
        <tr>
          <th>Receipt Id</th>
          <th>Customer Name</th>
          <th>Purchase Date</th>
          <th>Order Status</th>
          <th>Total</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="order in orders">
          <td><a :href="`/orders/${order.receipt_id}`">{{ order.receipt_id }}</a></td>
          <td>{{ order.buyer_first_name}} {{ order.buyer_last_name}}</td>
          <td>{{ order.paid_at | cpStandardDate(true) }}</td>
          <td>{{ order.status }}</td>
          <td>{{ order.subtotal_price | currency }}</td>
        </tr>
      </tbody>
    </table>
    <div class="align-center">
      <div class="no-results" v-if="noResults">
            <span>No orders for this {{ $getGlobal('events_title').value.single.toLowerCase() }}</span>
        </div>
      <img class="loading" :src="$getGlobal('loading_icon').value" v-if="loading">
    </div>
  </div>
</template>
<script>
const Events = require('../../resources/EventsAPIv0.js')
const Orders = require('../../resources/OrdersAPIv0.js')
module.exports = {
  name: 'CpEventsOrders',
  routing: [
    {
      name: 'site.CpEventsOrders',
      path: '/events/:id',
      meta: {
        title: 'Event Details'
      },
      props: true
    }
  ],
  data: () => ({
    loading: false,
    noResults: false,
    event: null,
    orders: [],
    pageError: null
  }),
  props: {
    eventProp: {
      type: Object,
      default () {
        return null
      }
    },
    id: {
      type: String
    }
  },
  created () {
    if (!this.eventProp) {
      this.getEvent()
    } else {
      this.event = this.eventProp
    }
  },
  mounted () {
    this.getOrders()
  },
  methods: {
    getEvent () {
      Events.getEvent({}, this.id)
        .then((response) => {
          if (response.error) {
            if (response.code == 404) {
              this.pageError = 'Event missing'
            } else {
              this.pageError = (!response.message.message ? 'Unexpected error. Please try again later' : response.message.message)
            }
          } else {
            this.event = response
          }
        })
    },
    getOrders () {
      this.loading = true
      let request = {
        event_id: this.id
      }
      Orders.getOrders(request).then((response) => {
        this.loading = false
        if (response.data.length === 0) {
          this.noResults = true
        }
        this.orders = response.data
      })
    }
  }
}
</script>
<style lang="scss" scoped>
</style>
