<template>
<div>
  <div class="order-index-wrapper">
    <div class="cp-download-buttons">
      <a v-if="Auth.hasAnyRole('Admin', 'Superadmin') && selectedStatus === 'unfulfilled'" class="cp-button-link" @click="showDownloadModal('picklist')">Download Picklist</a>
      <a class="cp-button-link" @click="showDownloadModal('order-list')">Download Orders</a>
      <a v-if="$getGlobal('shipping_link').show && Auth.hasAnyRole('Rep')" v-bind:href="$getGlobal('shipping_link').value" target="_blank" class="cp-button-link">{{$getGlobal('shipping_link_text').value}}</a>
    </div>
    <cp-tabs :items="tabs" :callback="changeTab"></cp-tabs>
    <cp-table-controls :date-picker="true" :date-range="indexRequest" :index-request="indexRequest" :resource-info="pagination" :get-records="getOrders"></cp-table-controls>
    <div class="status-select">
      <select v-model="statusUpdate" @change="showConfirm = true" :class="{ 'disable-select-box': disableSelectBox }">
        <option :value='null' selected disabled>Change Status to:</option>
        <option v-for="status in orderStatuses" :value="status.name" v-show="statusShow(status.name)">{{status.name.charAt(0).toUpperCase() + status.name.slice(1)}}</option>
      </select>
    </div>
    <table class="cp-table-standard desktop">
      <thead>
        <th><input v-if="orders.length > 0" type="checkbox" :id="'all'" :value="'all'" v-model="selectAll" @change="pageSelect()" /></th>
        <th>Receipt ID</th>
        <th @click="sortColumn('updated_at')">Last Updated
          <span v-show="indexRequest.column == 'updated_at'">
            <span v-show="reverseSort"><i class='mdi mdi-sort-ascending'></i></span>
            <span v-show="!reverseSort"><i class='mdi mdi-sort-descending'></i></span>
          </span>
        </th>
        <th @click="sortColumn('customer_id')" v-if="orderActive.all">Buyer ID
          <span v-show="indexRequest.column == 'customer_id'">
            <span v-show="reverseSort"><i class='mdi mdi-sort-ascending'></i></span>
            <span v-show="!reverseSort"><i class='mdi mdi-sort-descending'></i></span>
          </span>
        </th>
        <th v-else>Buyer ID</th>
        <th @click="sortColumn('buyer_first_name')">First Name
          <span v-show="indexRequest.column == 'buyer_first_name'">
            <span v-show="reverseSort"><i class='mdi mdi-sort-ascending'></i></span>
            <span v-show="!reverseSort"><i class='mdi mdi-sort-descending'></i></span>
          </span>
        </th>
        <th @click="sortColumn('buyer_last_name')">Last Name
          <span v-show="indexRequest.column == 'buyer_last_name'">
            <span v-show="reverseSort"><i class='mdi mdi-sort-ascending'></i></span>
            <span v-show="!reverseSort"><i class='mdi mdi-sort-descending'></i></span>
          </span>
        </th>
        <th>Order Type
          <span v-show="indexRequest.column == 'type'">
            <span v-show="reverseSort"><i class='mdi mdi-sort-ascending'></i></span>
            <span v-show="!reverseSort"><i class='mdi mdi-sort-descending'></i></span>
          </span>
        </th>
        <th>Tender
          <span v-show="indexRequest.column == 'cash'">
            <span v-show="reverseSort"><i class='mdi mdi-sort-ascending'></i></span>
            <span v-show="!reverseSort"><i class='mdi mdi-sort-descending'></i></span>
          </span>
        </th>
        <th @click="sortColumn('created_at')">Date Paid
          <span v-show="indexRequest.column == 'created_at'">
            <span v-show="reverseSort"><i class='mdi mdi-sort-ascending'></i></span>
            <span v-show="!reverseSort"><i class='mdi mdi-sort-descending'></i></span>
          </span>
        </th>
        <th @click="sortColumn('status')">Order Status
          <span v-show="indexRequest.column == 'status'">
            <span v-show="reverseSort"><i class='mdi mdi-sort-ascending'></i></span>
            <span v-show="!reverseSort"><i class='mdi mdi-sort-descending'></i></span>
          </span>
        </th>
        <th @click="sortColumn('total_price')">Total
          <span v-show="indexRequest.column == 'total_price'">
            <span v-show="reverseSort"><i class='mdi mdi-sort-ascending'></i></span>
            <span v-show="!reverseSort"><i class='mdi mdi-sort-descending'></i></span>
          </span>
        </th>

      </thead>
      <tbody>
        <tr v-for="(order, index) in orders" :key="index" :class="{'hold-order': order.status === 'hold' }">
          <td v-if="order.token"><input type="checkbox" :id="order.id" :value="order.receipt_id" v-model="ordersToUpdate" @click="checkOrdersLength()" /></td>
          <td v-else><input v-if="order.status != 'cancelled'" type="checkbox" :id="order.id" :value="order.receipt_id" v-model="ordersToUpdate" @click="checkOrdersLength()" v-show="showUpdateStatus" /></td>
          <td><a :href="`/orders/${order.receipt_id}`">{{ order.confirmation_code }}</a></td>
          <td>{{ order.updated_at | cpStandardDate }}</td>
          <td>{{ order.buyer_id }}</td>
          <td>{{ order.buyer_first_name }}</td>
          <td>{{ order.buyer_last_name }}</td>
          <td v-if="order.type_description">{{ order.type_description }}</td>
          <td v-else>N/A</td>
          <td>{{ getTender(order.payment_type) }}</td>
          <td>{{ order.created_at | cpStandardDate}}</td>
          <td>{{ order.status.toUpperCase() }}</td>
          <td>{{ order.total_price | currency }}</td>
          <td v-if="order.status === 'unfulfilled' && $getGlobal('enable_shipping_label_creation').show"><button class="cp-button-standard" @click="purchaseShipping(order)">Purchase Shipping</button></td>
          <td v-if="order.shipment">
            <div>
              <button class="cp-button-standard" @click="printLabel(order.shipment.label_url)">Print Label</button>
            </div>
          </td>
        </tr>
      </tbody>
    </table>
    <section class="cp-table-mobile">
      <div v-for="order in orders">
        <div><span>Receipt ID: </span><span><a :href="`/orders/${order.receipt_id}`">{{ order.receipt_id }}</a></span></div>
        <div><span>Last Updated: </span><span>{{ order.updated_at | cpStandardDate}}</span></div>
        <div><span>Buyer ID: </span><span>{{ order.buyer_id }}</span></div>
        <div><span>First Name: </span><span>{{ order.buyer_first_name }}</span></div>
        <div><span>Last Name: </span><span>{{ order.buyer_last_name }}</span></div>
        <div v-if="order.type_description"><span>Order Type: </span><span>{{ order.type_description }}</span></div>
        <div v-else><span>N/A</span></div>
        <div v-if="order.cash"><span>Tender: </span><span>Cash</span></div>
        <div v-else><span>Tender: </span><span>CC</span></div>
        <div v-if="order.token"><span>Date Paid: </span><span>unpaid</span></div>
        <div v-else><span>Date Paid: </span><span>{{ order.created_at | cpStandardDate}}</span></div>
        <div><span>Order Status: </span><span>{{ order.status.toUpperCase() }}</span></div>
        <div><span>Total: </span><span>{{ order.total_price | currency }}</span></div>
        <div v-if="order.status === 'unfulfilled' && $getGlobal('enable_shipping_label_creation').show"><button class="cp-button-standard" @click="purchaseShipping(order)"><span>Purchase Shipping</span></button><span></span></div>      </div>
    </section>
    <cp-dialog :open="shippoModal" @close="shippoModal = false">
        <div slot="content" v-show="shippoModal">
          <cp-shippo ref="shippoForm" :show-modal="shippoModal" :get-orders="getOrders" v-model="shippoModal" @close="shippoModal = false"></cp-shippo>
        </div>
    </cp-dialog>
    <div class="align-center">
      <div class="no-results" v-if="noResults">
        <span>No results for this timeframe</span>
      </div>
      <img class="loading" :src="$getGlobal('loading_icon').value" v-if="loading">
      <cp-pagination :pagination="pagination" :callback="getOrders" :offset="2"></cp-pagination>
    </div>
    <cp-order-download-modal
      v-if="orderDownloadModal"
      @close="orderDownloadModal = false"
      :title="downloadTitle"
      :start-date="indexRequest.start_date"
      :end-date="indexRequest.end_date"
      :type="downloadType"
      :status="indexRequest.status"
      :sort-column="indexRequest.column"
      :sort-order="indexRequest.order">
    </cp-order-download-modal>
  </div>
  <cp-confirm
  :message="'Update status to ' + statusUpdate + ' for selected orders?'"
  v-model="showConfirm"
  :show="showConfirm"
  :callback="updateStatuses"
  :onCancelled="() => { statusUpdate = null }"
  :params="{}"></cp-confirm>
</div>
</template>


<script>
const Orders = require('../../resources/OrdersAPIv0.js')
const OrdersOld = require('../../resources/orders.js')
const OrderStatus = require('../../resources/order-status.js')
const moment = require('moment')
const Auth = require('auth')
const _ = require('lodash')

module.exports = {
  name: 'CpOrderIndex',
  routing: [
    {
      name: 'site.CpOrderIndex',
      path: 'orders',
      meta: {
        title: 'All Orders'
      },
      props: true
    }
  ],
  data: function () {
    return {
      downloadTitle: null,
      downloadType: null,
      Auth: Auth,
      shippoModal: false,
      noResults: false,
      loading: false,
      reverseSort: false,
      showStatus: false,
      showUpdateStatus: true,
      statusUpdate: null,
      showConfirm: false,
      orders: [],
      ordersToUpdate: [],
      orderStatuses: {},
      tabs: [{
        name: 'ALL',
        active: false
      }],
      status: 'none',
      selectedStatus: 'all',
      orderActive: {
        all: true,
        open: false,
        closed: false
      },
      pagination: {
        total: 0,
        per_page: 15
      },
      asc: false,
      indexRequest: {
        start_date: moment().subtract(10, 'days').format('YYYY-MM-DD'),
        end_date: moment().format('YYYY-MM-DD'),
        order: 'DESC',
        column: 'created_at',
        per_page: 15,
        search_term: '',
        status: 'all'
      },
      markers: [],
      orderDownloadModal: false,
      center: {
        lat: 42,
        lng: -111.6946
      },
      selectAll: false,
      currentFulfillment: 'open',
      disableSelectBox: true
    }
  },
  computed: {
    ordersActiveJson: function () {
      return JSON.stringify(this.orderActive)
    }
  },
  mounted: function () {
    let per_page = Auth.hasAnyRole('Superadmin', 'Admin') ? '100' : '15'
    this.indexRequest.per_page = per_page
    this.pagination.per_page = per_page
    this.getStatuses()
  },
  methods: {
    checkOrdersLength: _.debounce(function () {
      if (this.ordersToUpdate.length >= 1) {
        this.disableSelectBox = false
      } else {
        this.disableSelectBox = true
      }
    }, 100),
    changeTab (name) {
      this.selectedStatus = name.toLowerCase()
      this.resetIndexRequest()
      this.getOrders()
    },
    getOrders () {
      this.loading = true
      this.orders = []
      // Refactor to new structure
      let request = JSON.parse(JSON.stringify(this.indexRequest))
      request.page = this.pagination.current_page
      request.sort_by = this.indexRequest.column.toLowerCase()
      request.in_order = this.indexRequest.order.toLowerCase()
      request.start_date = moment(request.start_date, 'YYYY-MM-DD').startOf('day').utc().format('YYYY-MM-DD HH:mm:ss.SSS')
      request.end_date = moment(request.end_date, 'YYYY-MM-DD').endOf('day').utc().format('YYYY-MM-DD HH:mm:ss.SSS')
      if (request.status === 'all') {
        delete request.status
      }
      if (Auth.hasAnyRole('Superadmin', 'Admin')) {
        request.type_id = [1,2,5,9]
      } else {
        request.type_id = [3,4,10,11]
      }
      Orders.getOrders(request)
        .then((response) => {
          this.loading = false
          if (response.total === 0) {
            this.showNoResultsMessage()
          }
          this.handleOrderResponse(response)
        })
    },
    getTender(paymentType) {
      switch(paymentType) {
        case 'cash':
          return 'Cash'
        case 'e-wallet':
          return 'eWallet'
        case 'credit-card':
        case 'card-token':
          return 'Credit Card'
        default:
          return 'Unknown'
      }
    },
    pageSelect () {
      this.ordersToUpdate = []
      this.disableSelectBox = true
      this.statusUpdate = null
      if (this.selectAll) {
        for (var i = 0, len = this.orders.length; i < len; i++) {
          if (this.orders[i].status !== 'cancelled') {
            this.ordersToUpdate.push(this.orders[i].receipt_id)
          }
        }
        this.disableSelectBox = false
      }
    },
    printLabel (labelUrl) {
      window.open(labelUrl, '_blank')
    },
    statusShow (statusName) {
      if (this.selectedStatus === 'unfulfilled') {
        return true
      } else if (statusName === 'cancelled') {
        return false
      }
      return true
    },
    showDownloadModal (type) {
      this.downloadType = type
      this.downloadTitle = (type === 'order-list' ? 'Order Download' : 'Picklist Download')
      this.orderDownloadModal = true
    },
    purchaseShipping: function (order) {
      this.$refs.shippoForm.setOrder(JSON.parse(JSON.stringify(order)))
      this.shippoModal = true
    },
    handleOrderResponse: function (response) {
      this.markers = []
      if (response.error) {
        this.$toast(response.message, {
          error: true
        })
      } else {
        this.orders = response.data
        response.per_page = parseInt(response.per_page)
        this.pagination = response
        this.markers = response.markers
        this.ordersToUpdate = []
      }
    },
    resetIndexRequest: function () {
      this.reverseSort = false
      this.indexRequest = {
        start_date: this.indexRequest.start_date,
        end_date: this.indexRequest.end_date,
        order: 'DESC',
        column: 'created_at',
        status: this.selectedStatus.toLowerCase(),
        per_page: this.indexRequest.per_page,
        search_term: this.indexRequest.search_term,
        page: 1
      }
      this.pagination.current_page = 1
      this.selectAll = false
    },
    sortColumn: function (column) {
      this.reverseSort = !this.reverseSort
      this.indexRequest.column = column
      this.asc = !this.asc
      if (this.asc === true) {
        this.indexRequest.order = 'asc'
      } else {
        this.indexRequest.order = 'desc'
      }
      this.getOrders()
    },
    updateStatuses: function () {
      if (this.selectAll === true && this.statusUpdate.toLowerCase() === 'cancelled') {
        this.$toast('You cannot cancel orders on a mass select', {
          error: true,
          dismiss: true
        })
        return
      }
      if (this.statusUpdate) {
        let request = {
          orders: this.ordersToUpdate,
          status: this.statusUpdate.toLowerCase()
        }
        OrdersOld.updateStatus(request)
          .then((response) => {
            this.handleUpdateResponse(response)
            this.statusUpdate = null
            this.disableSelectBox = true
            this.selectAll = false
            this.getOrders()
          })
      }
    },
    handleUpdateResponse: function (response) {
      if (response.error) {
        this.$toast(response.message, {
          error: true
        })
      } else {
        this.$toast('Successfully updated!')
        this.status = 1
      }
      this.ordersToUpdate = []
    },
    showNoResultsMessage: function () {
      this.noResults = true
      setTimeout(function () {
        this.noResults = false
      }.bind(this), 3000)
    },
    getStatuses: function () {
      OrderStatus.getIndex()
        .then((response) => {
          this.orderStatuses = response
          var all = this.tabs.pop()
          for (var i = 0, len = this.orderStatuses.length; i < len; i++) {
            if ((!!this.orderStatuses[i].visible) === true) {
              var active = false
              if (i === 0) {
                active = true
                this.selectedStatus = this.orderStatuses[i].name.toLowerCase()
                this.indexRequest.status = this.orderStatuses[i].name
              }
              this.tabs.push({
                name: this.orderStatuses[i].name.toUpperCase(),
                active: active
              })
            }
          }
          this.tabs.push(all)
          this.getOrders()
        })
    }
  },
  components: {
    CpOrderDownloadModal: require('./CpOrderDownloadModal.vue'),
    CpShippo: require('../shipping/CpShippo.vue'),
    CpTableControls: require('../../cp-components-common/tables/CpTableControls.vue'),
    CpTabs: require('../../cp-components-common/navigation/CpTabs.vue')
  }
}
</script>

<style lang="scss" scoped>
// @import "resources/assets/sass/var.scss";
.order-index-wrapper {
    .disable-select-box {
        pointer-events: none;
        color: $cp-lightGrey;
    }
    .cp-button-standard {
        &.download {
            background: #fff;
            p {
                color: #337ab7;
            }
            &:hover {
                background: $cp-main;
                a {
                    color: #fff;
                    text-decoration: none;
                }
            }
        }
    }
    .order-index-inputs {
        margin-top: 5px;
    }
    tr.hold-order {
        background: #fff17c;
        border-bottom: 1px solid lighten($cp-main, 25%);
        border-top: 1px solid lighten($cp-main, 25%);
    }
}
.order-detail-modal {
    position: absolute;
    background: #fff;
    height: 200px;
    box-shadow: 0 0 5px 0 #000;
    width: 100%;
    max-width: 300px;
    margin: 0 auto;
    z-index: 9;
    padding: 20px;
}
.status-select {
    float: right;
    display: inline;
    position: relative;
    background: #f8f8f8;
    height: 31px;
    width: 155px;
    margin: 10px;
    &:after {
        position: absolute;
        right: 5px;
        top: 10px;
        font-family: "Linearicons";
        content: "\e93a";
        font-size: 10px;
        pointer-events: none;
    }
    select {
        height: 31px;
        width: 155px;
        border: none;
        -webkit-appearance: none;
        -moz-appearance: none;
        -webkit-border-radius: 0;
        text-align: left;
        text-align-last: left;
        text-indent: 10px;
        background: #f5f5f5;
    }
}
@media(max-width: 768px) {
    .status-select {
        display: none;
    }
}
</style>
