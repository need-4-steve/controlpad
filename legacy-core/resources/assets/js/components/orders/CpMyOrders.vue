<template>
    <div class="my-orders-wrapper">
        <cp-table-controls
            :date-picker="true"
            :date-range="dates"
            :index-request="indexRequest"
            :resource-info="pagination"
            :get-records="getOrders">
        </cp-table-controls>
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
                <span>No results for this timeframe</span>
            </div>
            <img class="loading" :src="$getGlobal('loading_icon').value" v-if="loading">
            <cp-pagination :pagination="pagination" :callback="getOrders" :offset="2"></cp-pagination>
        </div>
    </div>
</template>

<script>
const Orders = require('../../resources/OrdersAPIv0.js')
const moment = require('moment')
const Auth = require('auth')

module.exports = {
  name: 'CpMyOrders',
  routing: [
    {
      name: 'site.CpMyOrders',
      path: 'my-orders',
      meta: {
        title: 'Orders',
        nosubscription: true
      },
      props: true
    }
  ],
  data: function () {
    return {
      Auth: Auth,
      noResults: false,
      loading: false,
      orders: [],
      pagination: {
        per_page: 15
      },
      asc: false,
      dates: {
        start_date: moment().subtract(10, 'days').format('YYYY-MM-DD'),
        end_date: moment().format('YYYY-MM-DD')
      },
      indexRequest: {
        start_date: moment().subtract(10, 'days').format('YYYY-MM-DD'),
        end_date: moment().format('YYYY-MM-DD'),
        order: 'DESC',
        column: 'created_at',
        per_page: 15,
        search_term: '',
        page: 1
      },
      reverseSort: false,
      showConfirm: false,
      selectedOrder: {}
    }
  },
  mounted () {
    this.getOrders()
  },
  methods: {
    getOrders: function () {
      this.loading = true
      // Refactor to new structure
      let request = JSON.parse(JSON.stringify(this.indexRequest))
      request.page = this.pagination.current_page
      request.sort_by = this.indexRequest.column.toLowerCase()
      request.in_order = this.indexRequest.order.toLowerCase()
      request.start_date = moment(this.dates.start_date, 'YYYY-MM-DD').startOf('day').utc().format('YYYY-MM-DD HH:mm:ss.SSS')
      request.end_date = moment(this.dates.end_date, 'YYYY-MM-DD').endOf('day').utc().format('YYYY-MM-DD HH:mm:ss.SSS')
      request.buyer_id = Auth.getAuthId()

      this.orders = {}
      Orders.getOrders(request)
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
          response.per_page = parseInt(response.per_page)
          this.pagination = response
        })
    }
  },
  components: {
    CpTableControls: require('../../cp-components-common/tables/CpTableControls.vue')
  }
}
</script>

<style lang="sass">
    @import "resources/assets/sass/var.scss";
    .my-orders-wrapper {
        tr.hold-order {
            background: #fff17c;
            border-bottom: 1px solid $cp-main;
            border-top: 1px solid $cp-main;
        }
        .action-btn-wrapper {
            margin-bottom: 10px;
        }
    }

</style>
