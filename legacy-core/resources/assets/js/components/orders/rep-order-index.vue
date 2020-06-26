<template>
    <div class="rep-index-wrapper">
        <div class="action-btn-wrapper">
            <a class="cp-button-link" @click="orderDownloadModal = true">Download</a>
        </div>
        <cp-table-controls
            :date-picker="true"
            :date-range="dates"
            :index-request="indexRequest"
            :resource-info="pagination"
            :get-records="getOrders">
        </cp-table-controls>
        <table class="cp-table-standard desktop">
            <thead>
                <th>PDF</th>
                <th>Receipt ID</th>
                <th>Date of order</th>
                <th>Customer Name</th>
                <th>Subtotal</th>
                <th>Tax</th>
                <th>Shipping</th>
                <th>Order Total</th>
                <th>Date Paid</th>
                <th>Cash Order</th>
                <th>Source of Order</th>
                <th>Order Status</th>
                <th v-if="$getGlobal('inventory_confirmation').show">Inventory Received</th>
            </thead>
            <tbody>
                <tr v-for="order in orders" :class="{'hold-order': order.status === 'hold' }">
                       <td><a @click="printOrder(order.id)"><i class='mdi mdi-file'></i></a></td>
                       <td><a :href="'/orders/' + order.receipt_id">{{ order.receipt_id }}</a></td>
                       <td>{{ order.created_at | cpStandardDate}}</td>
                       <td>{{ order.buyer_last_name }}, {{ order.buyer_first_name }}</td>
                       <td>{{ order.subtotal_price | currency }}</td>
                       <td>{{ order.total_tax | currency }}</td>
                       <td>{{ order.total_shipping | currency }}</td>
                       <td>{{ order.total_price | currency }}</td>
                       <td v-if="order.paid_at">{{ order.paid_at | cpStandardDate}}</td>
                       <td v-else>{{ 'unpaid' }}</td>
                       <td>
                           <span>{{ order.cash ? 'Yes' : 'No'}}</span>
                       </td>
                       <td>{{ order.source }}</td>
                       <td>{{ order.status}}</td>
                       <td v-if="$getGlobal('inventory_confirmation').show">
                         <button
                            v-if="!order.inventory_received_at && order.buyer_id == Auth.getAuthId()"
                            class="cp-button-standard"
                            type="button"
                            name="button"
                            @click="showConfirm = true; selectedOrder = order">Receive Inventory</button>
                        <span v-else>{{order.inventory_received_at | cpStandardDate}}</span>
                       </td>
                </tr>
            </tbody>
        </table>

            <section class="cp-table-mobile">
                <div v-for='order in orders'>
                    <div></div><span></span>
                    <div>
                        <span>PDF: </span><span><a @click="printOrder(order.id)"><i class='mdi mdi-file'></i></a></span>
                    </div>
                    <div>
                        <span>Invoice ID: </span><span><a :href="'/orders/' + order.receipt_id">{{ order.receipt_id }}</a></span>
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
                        <span>Date Paid: </span><span>{{ order.paid_at || 'unpaid' }}</span>
                    </div>
                    <div>
                        <span>Cash Order: </span>
                        <span v-if="order.cash === 1">Yes</span>
                        <span v-if="order.cash === 0">No</span>
                    </div>

                    <div>
                        <span>Source of Order: </span><span>{{ order.mobile || 'Web' }}</span>
                    </div>
                    <div>
                        <span>Order Status: </span><span>{{ order.status}}</span>
                    </div>
                    <div v-if="$getGlobal('inventory_confirmation').show">
                        <span>Inventory Received: </span>
                        <span v-if="order.inventory_received_at === null && order.buyer_id == Auth.getAuthId()"><button class="cp-button-standard" type="button" name="button" @click="showConfirm = true; selectedOrder = order">Receive Inventory</button></span>
                        <span v-else>{{order.inventory_received_at | cpStandardDate}}</span>
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
        <cp-confirm
            :message="'You are about to transfer items from this order into your available inventory. This action cannot be undone. Would you like to continue?'"
            v-model="showConfirm"
            :show="showConfirm"
            :callback="confrimInventory"
            :on-canceled="resetSelectedOrder"
            :params="selectedOrder"></cp-confirm>
        <cp-order-download-modal v-if="orderDownloadModal" @close="orderDownloadModal = false"
            :title="downloadTitle" :start-date="dates.start_date"
            :end-date="dates.end_date" :type="downloadType"
            :sort-column="indexRequest.column" :sort-order="indexRequest.order"></cp-order-download-modal>
    </div>
</template>

<script>
const Orders = require('../../resources/OrdersAPIv0.js')
const moment = require('moment')
const CpOrdersFile = require('../../libraries/CpOrdersFile.js')
const Auth = require('auth')

module.exports = {
  name: 'CpRepOrderIndex',
  routing: [
    {
      name: 'site.CpRepOrderIndex',
      path: 'inventory/rep-orders',
      meta: {
        title: 'Orders'
      },
      props: true
    }
  ],
  data: function () {
    return {
      Auth: Auth,
      downloadTitle: 'My Orders',
      downloadType: 'purchase',
      orderDownloadModal: false,
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
    resetSelectedOrder: function () {
      this.selectedOrder = {}
    },
    confrimInventory: function (order) {
      Orders.acceptInventory(order.id)
        .then((response) => {
          if (response.error) {
            this.$toast(response.message, {error: true})
          } else {
            this.showConfirm = false
            this.selectedOrder.inventory_received_at = response.inventory_received_at
            this.selectedOrder = {}
            this.$toast('Inventory has been received.')
          }
        })
    },
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

      // corp to rep, and transfer orders
      request.type_id = [1,11]

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
    },
    printOrder: function (orderId) {
      new CpOrdersFile(null, ['pdf'], 'order', {orderId: orderId}).run()
    }
  },
  components: {
    CpTableControls: require('../../cp-components-common/tables/CpTableControls.vue')
  }
}
</script>

<style lang="sass">
    @import "resources/assets/sass/var.scss";
    .rep-index-wrapper {
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
