<template>
  <div>
    <div class="invoice-index-wrapper">
        <div class="cp-download-buttons">
          <a class="cp-button-link" @click="showDownloadModal('invoice-list')">Download Invoices</a>
        </div>
        <cp-tabs
          v-if="Auth.hasAnyRole('Superadmin', 'Admin')"
          :items="[
            { name: 'Corporate Invoices', active: true },
            { name: $getGlobal('title_rep').value + ' Invoices', active: false },
            { name: 'ALL', active: false },
          ]"
          :callback="invoiceStatus"></cp-tabs>
        <cp-table-controls
          :date-picker="true"
          :date-range="indexRequest"
          :index-request="indexRequest"
          :resource-info="pagination"
          :get-records="getInvoices">
        </cp-table-controls>
         <div class="status-select">
              <select v-model="statusUpdate" @change="updateInvoiceStatuses()" :class="{ 'disable-select-box': disableSelectBox }">
                  <option :value="null" selected disabled>Change Status to:</option>
                  <option value='cancelled'>Cancelled</option>
              </select>
          </div>
        <table class="cp-table-standard desktop">
            <thead>
                  <th><!-- checkbox field --> </th>
                <th @click="sortColumn('receipt_id')">Receipt ID
                    <span v-show="indexRequest.column === 'receipt_id'">
                        <span v-show="reverseSort"><i class='mdi mdi-sort-ascending'></i></span>
                        <span v-show="!reverseSort"><i class='mdi mdi-sort-descending'></i></span>
                    </span>
                </th>
                <th @click="sortColumn('created_at')">Date Created
                    <span v-show="indexRequest.column === 'created_at'">
                        <span v-show="reverseSort"><i class='mdi mdi-sort-ascending'></i></span>
                        <span v-show="!reverseSort"><i class='mdi mdi-sort-descending'></i></span>
                    </span>
                </th>
                <th @click="sortColumn('expires_at')" >Date Expires
                    <span v-show="indexRequest.column === 'expires_at'">
                        <span v-show="reverseSort"><i class='mdi mdi-sort-ascending'></i></span>
                        <span v-show="!reverseSort"><i class='mdi mdi-sort-descending'></i></span>
                    </span>
                </th>
                <th @click="sortColumn('users.last_name')">Customer Name
                    <span v-show="indexRequest.column === 'users.last_name'">
                        <span v-show="reverseSort"><i class='mdi mdi-sort-ascending'></i></span>
                        <span v-show="!reverseSort"><i class='mdi mdi-sort-descending'></i></span>
                    </span>
                </th>
                <th @click="sortColumn('subtotal_price')">Subtotal
                    <span v-show="indexRequest.column === 'subtotal_price'">
                        <span v-show="reverseSort"><i class='mdi mdi-sort-ascending'></i></span>
                        <span v-show="!reverseSort"><i class='mdi mdi-sort-descending'></i></span>
                    </span>
                </th>
                <th v-if="indexRequest.status !== 'owner'" @click="sortColumn('seller.last_name')">Seller Name
                    <span v-show="indexRequest.column === 'seller.last_name'">
                        <span v-show="reverseSort"><i class='mdi mdi-sort-ascending'></i></span>
                        <span v-show="!reverseSort"><i class='mdi mdi-sort-descending'></i></span>
                    </span>
                </th>
            </thead>
            <tbody>
                <tr v-for="(invoice, index) in invoices" :key="index">
                    <td><input type="checkbox" :id="invoice.uid" :value="invoice.uid" v-model="invoicesToUpdate" @click="checkinvoicesLength()"/></td>
                    <td><a :href="`/invoices/${invoice.receipt_id}`">{{ invoice.uid }} </a></td>
                    <td>{{ invoice.created_at | cpStandardDate}}</td>
                    <td>{{ invoice.expires_at | cpStandardDate(true)}}</td>
                    <td>{{ invoice.customer_last_name }}, {{ invoice.customer_first_name }}</td>
                    <td>{{ invoice.subtotal_price | currency }}</td>
                    <td v-if="indexRequest.status !== 'owner'">{{invoice.seller_last_name}}, {{ invoice.seller_first_name }}</td>
                </tr>
            </tbody>
        </table>
        <section class="cp-table-mobile" >
          <div v-for="invoice in invoices">
            <div><span>Receipt ID: </span><a :href="`/invoices/${invoice.receipt_id}`">{{ invoice.uid }}</a></div>
            <div><span>Date Created: </span><span>{{ invoice.created_at | cpStandardDate}}</span></div>
            <div><span>Date Expires: </span><span>{{ invoice.expires_at | cpStandardDate(true)}}</span></div>
            <div><span>Name: </span><span>{{ invoice.customer_last_name }}, {{ invoice.customer_first_name }} </span></div>
            <div><span>Subtotal: </span><span>{{ invoice.subtotal_price | currency}}</span></div>
            <div v-if="indexRequest.status !== 'owner'"><span>Seller ID: </span><span>{{ invoice.store_owner_user_id }}</span></div>
          </div>
        </section>
        <div class="align-center">
            <div class="no-results" v-if="noResults">
                <span>No results for this timeframe</span>
            </div>
            <img class="loading" :src="$getGlobal('loading_icon').value" v-if="loading">
            <cp-pagination :pagination="pagination" :callback="getInvoices" :offset="2"></cp-pagination>
        </div>
    </div>
    <cp-order-download-modal v-if="orderDownloadModal" @close="orderDownloadModal = false"
      :title="downloadTitle" :start-date="indexRequest.start_date"
      :end-date="indexRequest.end_date" :type="downloadType"
      :sort-column="indexRequest.column" :sort-order="indexRequest.order"
      :search-term="indexRequest.search_term"
      :status="indexRequest.status"></cp-order-download-modal>
  </div>
</template>

<script>
const Auth = require('auth')
const Invoices = require('../../resources/invoice.js')
const moment = require('moment')
const _ = require('lodash')

module.exports = {
  data: function () {
    return {
      Auth: Auth,
      orderDownloadModal: false,
      downloadTitle: 'Invoice Download',
      downloadType: null,
      noResults: false,
      loading: false,
      reverseSort: false,
      statusUpdate: null,
      invoices: [],
      invoiceActive: {
        invoices: true
      },
      invoicesToUpdate: [],
      status: 'none',
      pagination: {
        per_page: 100
      },
      asc: false,
      dates: {
        start_date: moment().subtract(31, 'days').format('YYYY-MM-DD'),
        end_date: moment().format('YYYY-MM-DD')
      },
      indexRequest: {
        start_date: moment().subtract(31, 'days').format('YYYY-MM-DD'),
        end_date: moment().format('YYYY-MM-DD'),
        order: 'DESC',
        column: 'created_at',
        per_page: 100,
        search_term: '',
        status: 'owner'
      },
      disableSelectBox: true
    }
  },
  computed: {
    invoicesActiveJson: function () {
      return JSON.stringify(this.invoiceActive)
    }
  },
  mounted: function () {
    this.prepareIndexRequest('invoices')
    this.getInvoices()
  },
  methods: {
    checkinvoicesLength: _.debounce(function () {
      if (this.invoicesToUpdate.length >= 1) {
        this.disableSelectBox = false
      } else {
        this.disableSelectBox = true
      }
    }, 100),
    getInvoices: function () {
      this.prepareIndexRequest('invoices')

      Invoices.index(this.indexRequest)
        .then((response) => {
          if (response.total === 0) {
            this.showNoResultsMessage()
          }
          this.handleinvoiceResponse(response)
        })
      this.invoicesToUpdate = []
    },
    invoiceStatus: function (name) {
      if (name) {
        switch (name) {
          case 'ALL':
            this.indexRequest.status = ''
            this.getInvoices()
            break
          case 'Corporate Invoices':
            this.indexRequest.status = 'owner'
            this.getInvoices()
            break
          case this.$getGlobal('title_rep').value + ' Invoices':
            this.indexRequest.status = 'rep'
            this.getInvoices()
            break
          default:
            this.indexRequest.status = 'owner'
            this.getInvoices()
        }
      }
    },
    handleinvoiceResponse: function (response) {
      this.loading = false
      if (response.error) {
        this.$toast(response.message, {error: true})
      } else {
        this.invoices = response.orders.data
        response.orders.per_page = parseInt(response.orders.per_page)
        this.pagination = response.orders
      }
    },
    prepareIndexRequest: function (endpoint) {
      this.invoices = {}
      this.loading = true
      this.indexRequest.page = this.pagination.current_page
      if (!moment(this.indexRequest.start_date).isValid() || !moment(this.indexRequest.end_date).isValid()) {
        return this.$toast('Invalid Date.', {error: true, dismiss: false})
      }
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
      this.getInvoices()
    },
    updateInvoiceStatuses: function () {
      let request = {
        invoices: this.invoicesToUpdate,
        status: this.statusUpdate
      }
      var invoices = this.invoices
      this.invoicesToUpdate.forEach(function (invoiceId) {
        invoices = $.grep(invoices, function (e) {
          return e.uid !== invoiceId
        })
      })
      this.invoices = invoices
      Invoices.updateInvoiceStatus(request)
        .then((response) => {
          this.handleUpdateResponse(response)
          this.disableSelectBox = true
          this.statusUpdate = null
          return this.$toast('Invoice has been cancelled and deleted!', {error: false, dismiss: false})
        })
    },
    handleUpdateResponse: function (response) {
      if (response.error) {
        this.$toast(response.message, {error: true})
      } else {
        this.$toast('Successfully updated!')
        this.status = 1
      }
      this.invoicesToUpdate = []
    },
    showNoResultsMessage: function () {
      this.noResults = true
      setTimeout(function () {
        this.noResults = false
      }.bind(this), 3000)
    },
    showDownloadModal (type) {
      this.downloadType = type
      this.orderDownloadModal = true
    }
  },
  components: {
    'CpShippo': require('../shipping/CpShippo.vue'),
    CpTableControls: require('../../cp-components-common/tables/CpTableControls.vue'),
    CpTabs: require('../../cp-components-common/navigation/CpTabs.vue'),
    CpOrderDownloadModal: require('../orders/CpOrderDownloadModal.vue')
  }
}
</script>

<style lang="scss">
    @import "resources/assets/sass/var.scss";
    .invoice-index-wrapper {
      .cp-download-buttons{
        padding-bottom: 16px;
      }
      .disable-select-box{
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
        .invoice-index-inputs {
            margin-top: 5px;
        }
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
        -webkit-border-radius: 0px;
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
