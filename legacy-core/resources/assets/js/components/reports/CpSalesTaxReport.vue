<template lang="html">
  <div class="sales-tax-wrapper">
    <br>
    <a class='cp-button-link' download :href="'/api/v1/report/csv/tax?search_term=' + indexRequest.search_term + '&column=' + indexRequest.column + '&order=' + indexRequest.order + '&start_date=' + indexRequest.start_date + '&end_date=' + indexRequest.end_date">Sales Tax CSV</a>

      <!-- totals banner -->
      <cp-totals-banner
      totals-title="All Sales Tax Collected"
      :totals="[
      {title:'Corp Tax Collected', amount: taxTotals.corporate_taxes},
      {title:'FBC Tax Collected', amount: taxTotals.fbc_taxes},
      {title:'Rep Tax Collected', amount: taxTotals.rep_taxes}]"></cp-totals-banner>
      <!--Date Picker -->
      <cp-table-controls
      :date-picker="true"
      :date-range="dates"
      :index-request="indexRequest"
      :resource-info="pagination"
      :get-records="getSalesAndTaxTotals"></cp-table-controls>
      <!-- data table  -->
      <table class="cp-table-standard desktop">
          <thead>
              <th>PDF</th>
              <th @click="sortColumn('receipt_id')">Receipt ID
                  <span v-show="indexRequest.column == 'receipt_id'">
                      <span v-show="reverseSort"><i class='mdi mdi-sort-ascending'></i></span>
                      <span v-show="!reverseSort"><i class='mdi mdi-sort-descending'></i></span>
                  </span>
              </th>
              <th @click="sortColumn('created_at')">Date of Sale
                  <span v-show="indexRequest.column == 'created_at'">
                      <span v-show="reverseSort"><i class='mdi mdi-sort-ascending'></i></span>
                      <span v-show="!reverseSort"><i class='mdi mdi-sort-descending'></i></span>
                  </span>
              </th>
              <th @click="sortColumn('last_name')">Customer Name
                  <span v-show="indexRequest.column == 'last_name'">
                      <span v-show="reverseSort"><i class='mdi mdi-sort-ascending'></i></span>
                      <span v-show="!reverseSort"><i class='mdi mdi-sort-descending'></i></span>
                  </span>
              </th>
              <th @click="sortColumn('subtotal_price')">Subtotal
                  <span v-show="indexRequest.column == 'subtotal_price'">
                      <span v-show="reverseSort"><i class='mdi mdi-sort-ascending'></i></span>
                      <span v-show="!reverseSort"><i class='mdi mdi-sort-descending'></i></span>
                  </span>
              </th>
              <th @click="sortColumn('total_tax')">Tax
                  <span v-show="indexRequest.column == 'total_tax'">
                      <span v-show="reverseSort"><i class='mdi mdi-sort-ascending'></i></span>
                      <span v-show="!reverseSort"><i class='mdi mdi-sort-descending'></i></span>
                  </span>
              </th>
              <th @click="sortColumn('total_shipping')">Shipping
                  <span v-show="indexRequest.column == 'total_shipping'">
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
              <th @click="sortColumn('cash')">Cash Sale
                  <span v-show="indexRequest.column == 'cash'">
                      <span v-show="reverseSort"><i class='mdi mdi-sort-ascending'></i></span>
                      <span v-show="!reverseSort"><i class='mdi mdi-sort-descending'></i></span>
                  </span>
              </th>
              <th @click="sortColumn('type_id')">Order Type
                  <span v-show="indexRequest.column == 'type_id'">
                      <span v-show="reverseSort"><i class='mdi mdi-sort-ascending'></i></span>
                      <span v-show="!reverseSort"><i class='mdi mdi-sort-descending'></i></span>
                  </span>
              </th>
          </thead>
          <tbody>
              <tr v-for="sale in sales" >
                  <td v-if="sale.type_id !== 6 && sale.type_id !== 7 || Auth.hasAnyRole('Superadmin','Admin')">
                    <a @click="printOrder(sale.id)"><i class='mdi mdi-file'></i></a></td>
                  <td v-else></td>
                  <td v-if="sale.type_id !== 6 && sale.type_id !== 7 || Auth.hasAnyRole('Superadmin','Admin')"><a v-bind:href="'/orders/' + sale.receipt_id">{{ sale.receipt_id }}</a></td>
                  <td v-else>{{ sale.receipt_id }}</td>
                  <td>{{ sale.created_at | cpStandardDate}}</td>
                  <td>{{ sale.customer_last_name }}, {{ sale.customer_first_name }}</td>
                  <td v-if="sale.type_id !== 6  && sale.type_id !== 7 || Auth.hasAnyRole('Superadmin','Admin')">{{ sale.subtotal_price | currency }}</td>
                  <td v-else>{{fulfilledAmount(sale.lines) | currency}}</td>
                  <td>{{ sale.total_tax | currency }}</td>
                  <td>{{ sale.total_shipping | currency }}</td>
                  <td>{{ sale.total_price | currency }}</td>
                  <td>
                      <span v-if="sale.cash === 1">Yes</span>
                      <span v-if="sale.cash === 0">No</span>
                  </td>
                  <td>{{ sale.order_type.name }}</td>
              </tr>
          </tbody>
      </table>

      <section class="cp-table-mobile">
              <div v-for="sale in sales">
                <div v-if="sale.type_id !== 6 && sale.type_id !== 7 || Auth.hasAnyRole('Superadmin','Admin')"><span>PDF: </span><span><a @click="printOrder(sale.id)"><i class='mdi mdi-file'></i></a></span></div>
                <div v-else></div>
                <div v-if="sale.type_id !== 6 && sale.type_id !== 7 || Auth.hasAnyRole('Superadmin','Admin')"><span>Receipt ID: </span><span><a v-bind:href="'/orders/' + sale.receipt_id">{{ sale.receipt_id }}</a></span></div>
                <div v-else>{{ sale.receipt_id }}></div>
                <div><span>Date of Sale: </span><span>{{ sale.created_at | cpStandardDate}}</span></div>
                <div><span>Customer Name: </span><span>{{ sale.customer_last_name }}, {{ sale.customer_first_name }}</span></div>
                <div v-if="sale.type_id !== 6  && sale.type_id !== 7 || Auth.hasAnyRole('Superadmin','Admin')"><span>Subtotal: </span><span>{{ sale.subtotal_price | currency }}</span></div>
                <div v-else>{{fulfilledAmount(sale.lines) | currency}} ></div>
                <div><span>Tax: </span><span>{{ sale.total_tax | currency }}</span></div>
                <div><span>Shipping: </span><span>{{ sale.total_shipping | currency }}</span></div>
                <div><span>Total: </span><span>{{ sale.total_price | currency }}</span></div>
                <div><span>Cash Sale: </span>
                  <span v-if="sale.cash === 1">Yes</span>
                  <span v-if="sale.cash === 0">No</span></div>
                <div><span>Order Type: </span><span>{{ sale.order_type.name }}</span></div>
              </div>
      </section>
      <div class="align-center">
        <div class="no-results" v-if="noResults">
          <span>No results for this timeframe</span>
        </div>
        <img class="loading" :src="$getGlobal('loading_icon').value" v-if="loading">
        <cp-pagination
        :pagination="pagination"
        :callback="getSales"
        :offset="2"></cp-pagination>
      </div>
  </div>
</template>

<script>
const Sales = require('../../resources/sales.js')
const moment = require('moment')
const Auth = require('auth')
const CpOrdersFile = require('../../libraries/CpOrdersFile.js')

module.exports = {
  data () {
    return {
      noResults: false,
      loading: false,
      sales: null,
      Auth: Auth,
      pagination: {
        per_page: 15
      },
      indexRequest: {
        start_date: moment().subtract(10, 'days').format('YYYY-MM-DD'),
        end_date: moment().format('YYYY-MM-DD'),
        order: 'DESC',
        column: 'created_at',
        per_page: 15,
        search_term: '',
        page: 1,
        name: 'customer'
      },
      reverseSort: false,
      taxTotals: {}
    }
  },
  props: {
    dates: {}
  },
  mounted: function () {
    Auth.hasAnyRole('Superadmin', 'Admin') ? this.indexRequest.per_page = '100' : this.indexRequest.per_page = '15'
    this.getSales()
    this.getTaxTotals()
  },
  methods: {
    getSalesAndTaxTotals () {
      this.getSales()
      this.getTaxTotals()
    },
    getTaxTotals () {
      Sales.getSalesTaxTotals(this.indexRequest)
        .then((response) => {
          this.taxTotals = response
        })
    },
    fulfilledAmount (lines) {
      var amount = 0
      lines.forEach(function (line) {
        amount = amount + line.price
      })
      this.saleAmount = amount
      return this.saleAmount
    },
    sortColumn (column) {
      this.reverseSort = !this.reverseSort
      this.indexRequest.column = column
      this.asc = !this.asc
      if (this.asc === true) {
        this.indexRequest.order = 'asc'
      } else {
        this.indexRequest.order = 'desc'
      }
      this.getSales()
    },
    getSales () {
      this.loading = true
      this.indexRequest.start_date = moment(this.dates.start_date).format('YYYY-MM-DD')
      this.indexRequest.end_date = moment(this.dates.end_date).format('YYYY-MM-DD')
      this.indexRequest.page = this.pagination.current_page
      this.sales = {}
      Sales.getSalesByOrderType('all', this.indexRequest)
        .then((response) => {
          if (!response.error) {
            this.sales = response.data
            response.per_page = parseInt(response.per_page)
            this.pagination = response
          }
          if (response.total === 0) {
            this.noResults = true
            setTimeout(() => {
              this.noResults = false
            }, 3000)
          }
          this.loading = false
        })
    },
    printOrder: function (orderId) {
      new CpOrdersFile(null, ['pdf'], 'order', {orderId: orderId}).run()
    }
  },
  events: {
    'search_term': function (term) {
      this.indexRequest.search_term = term
      this.pagination.current_page = 1
      this.getSales()
      this.getTaxTotals()
    }
  },
  components: {
    CpTotalsBanner: require('../reports/CpTotalsBanner.vue'),
    CpTableControls: require('../../cp-components-common/tables/CpTableControls.vue')
  }
}
</script>

<style lang="scss">
@import "resources/assets/sass/var.scss";
.sales-tax-wrapper {
    .space-between {
        margin-bottom: 10px;
    }
    .action-btn-wrapper {
        margin-bottom: 10px;
    }
}
</style>
