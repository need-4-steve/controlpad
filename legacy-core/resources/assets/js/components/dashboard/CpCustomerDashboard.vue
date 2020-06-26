<template>
    <div class="my-orders-wrapper">
      <h2>Welcome, {{ user.first_name }} {{ user.last_name }}</h2>
      <br/>
      <h3>Last 5 Orders</h3>
      <table class="cp-table-standard desktop">
        <thead>
          <th>Order ID</th>
          <th>Date of order</th>
          <th>Customer Name</th>
          <th>Subtotal</th>
          <th>Tax</th>
          <th>Shipping</th>
          <th>Order Total</th>
          <th>Order Status</th>
        </thead>
        <tbody>
          <tr v-for="order in orders" :class="{'hold-order': order.status === 'hold' }">
             <td><a :href="'/orders/' + order.receipt_id">{{ order.id }}</a></td>
             <td>{{ order.created_at | cpStandardDate}}</td>
             <td>{{ order.buyer_last_name }}, {{ order.buyer_first_name }}</td>
             <td>{{ order.subtotal_price | currency }}</td>
             <td>{{ order.total_tax | currency }}</td>
             <td>{{ order.total_shipping | currency }}</td>
             <td>{{ order.total_price | currency }}</td>
             <td>{{ order.status}}</td>
          </tr>
        </tbody>
      </table>

      <section class="cp-table-mobile">
        <div v-for='order in orders'>
          <div></div><span></span>
          <div>
            <span>Order ID: </span><span><a :href="'/orders/' + order.receipt_id">{{ order.id }}</a></span>
          </div>
          <div>
            <span>Date of orders: </span><span>{{ order.created_at | cpStandardDate}}</span>
          </div>
          <div>
            <span>Customer Name: </span><span>{{ order.buyer_last_name }}, {{ order.buyer_first_name }}</span>
          </div>
          <div>
            <span>Subtotal:</span><span>{{ order.subtotal_price | currency }}</span>
          </div>
          <div>
            <span>Tax: </span><span>{{ order.total_tax | currency }}</span>
          </div>
          <div>
            <span>Shipping: </span><span>{{ order.total_shipping | currency }}</span>
          </div>
          <div>
            <span>Order Total: </span><span>{{ order.total_price | currency }}</span>
          </div>
          <div>
            <span>Order Status: </span><span>{{ order.status}}</span>
          </div>
        </div>
      </section>
      <div class="align-center">
        <div class="no-results" v-if="noResults">
          <span>No recent orders</span>
        </div>
        <img class="loading" :src="$getGlobal('loading_icon').value" v-if="loading">
      </div>
      <br/>
      <div v-if="$getGlobal('autoship_enabled').show">
        <h3>Subscription: {{ activeSubs.length > 0 ? 'Active' : 'Disabled' }}</h3>
        <br/>
        <button class="cp-button-standard"><a :href="manageLink">Manage Subscription</a></button>
      </div>
    </div>
</template>

<script>
const Orders = require('../../resources/OrdersAPIv0.js')
const Autoship = require('../../resources/AutoshipAPIv0.js')
const moment = require('moment')
const Auth = require('auth')
const Users = require('../../resources/UsersAPIv0.js')

module.exports = {
  data: function () {
    return {
      Auth: Auth,
      noResults: false,
      loading: true,
      orders: [],
      user: {},
      activeSubs: []
    }
  },
  created () {
    this.getUser()
    this.getOrders()
    this.getActiveSubs()
  },
  methods: {
    getOrders: function () {
      this.loading = true
      let params = {
        page: 1,
        per_page: 5,
        sort_by: '-created_at',
        buyer_id: Auth.getAuthId()
      }

      this.orders = {}
      Orders.getOrders(params)
        .then((response) => {
          if (response.error) {
            return this.$toast(response.message, {error: true})
          }
          if (response.total === 0) {
            this.noResults = true
            setTimeout(function () {
              this.noResults = false
            }.bind(this), 3000)
          }
          this.loading = false
          this.orders = response.data
        })
    },
    getUser () {
      Users.getById({}, Auth.getAuthId())
      .then((response) => {
        if (!response.error) {
          this.user = response
        }
      })
    },
    getActiveSubs () {
      Autoship.getSubscriptions({show_disabled: false, buyer_pid: Auth.getAuthPid(), per_page: 2})
      .then((response) => {
        if (!response.error) {
          this.activeSubs = response.data
        }
      })
    }
  },
  computed: {
    manageLink () {
      if (this.activeSubs.length == 1) {
        return '/my-subscriptions/' + this.activeSubs[0].id
      } else {
        return '/my-subscriptions'
      }
    }
  }
}
</script>

<style lang="sass">
</style>
