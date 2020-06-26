<template lang="html">
    <div class="sales-index-wrapper">
        <cp-table-controls
        :date-picker="true"
        :date-range="dates"
        :index-request="indexRequest"
        :resource-info="pagination"
        :get-records="getSalesAndTotals"></cp-table-controls>

        <!-- data table  -->

        <table class="cp-table-standard desktop">
            <thead>
                
                <th @click="sortColumn('created_at')">Date of Sale
                    <span v-show="indexRequest.column == 'created_at'">
                        <span v-show="!reverseSort"><i class='mdi mdi-sort-ascending'></i></span>
                        <span v-show="reverseSort"><i class='mdi mdi-sort-descending'></i></span>
                    </span>
                </th>

                <th @click="sortColumn('receipt_id')">Receipt ID
                    <span v-show="indexRequest.column == 'receipt_id'">
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
                        <span v-show="reverseSort"><i class='mdi mdi-sort-descending'></i></span>
                        <span v-show="!reverseSort"><i class='mdi mdi-sort-ascending'></i></span>
                    </span>
                </th>
                <th @click="sortColumn('total_tax')">Tax
                    <span v-show="indexRequest.column == 'total_tax'">
                        <span v-show="reverseSort"><i class='mdi mdi-sort-descending'></i></span>
                        <span v-show="!reverseSort"><i class='mdi mdi-sort-ascending'></i></span>
                    </span>
                </th>
                <th @click="sortColumn('total_shipping')">Shipping
                    <span v-show="indexRequest.column == 'total_shipping'">
                        <span v-show="reverseSort"><i class='mdi mdi-sort-descending'></i></span>
                        <span v-show="!reverseSort"><i class='mdi mdi-sort-ascending'></i></span>
                    </span>
                </th>
                <th @click="sortColumn('total_price')">Total
                    <span v-show="indexRequest.column == 'total_price'">
                        <span v-show="reverseSort"><i class='mdi mdi-sort-descending'></i></span>
                        <span v-show="!reverseSort"><i class='mdi mdi-sort-ascending'></i></span>
                    </span>
                </th>
                <th @click="sortColumn('sponsor_id')">Sponsor ID
                    <span v-show="indexRequest.column == 'sponsor_id'">
                        <span v-show="reverseSort"><i class='mdi mdi-sort-descending'></i></span>
                        <span v-show="!reverseSort"><i class='mdi mdi-sort-ascending'></i></span>
                    </span>
                </th>
                <th @click="sortColumn('sponsorname')">Sponsor Name
                    <span v-show="indexRequest.column == 'seller_name'">
                        <span v-show="reverseSort"><i class='mdi mdi-sort-descending'></i></span>
                        <span v-show="!reverseSort"><i class='mdi mdi-sort-ascending'></i></span>
                    </span>
                </th>
            </thead>
            <tbody>
                <tr v-for="sale in sales" >
                    <td>{{ sale.created_at | cpStandardDate}}</td>  
                    <td v-if="sale.type_id !== 6 && sale.type_id !== 7 || Auth.hasAnyRole('Superadmin','Admin')"><a v-bind:href="'/orders/' + sale.receipt_id">{{ sale.receipt_id }}</a></td>
                    <td v-else>{{ sale.receipt_id }}</td>
                    <td>{{ sale.customer_last_name }}, {{ sale.customer_first_name }}</td>
                    <td v-if="sale.type_id !== 6  && sale.type_id !== 7 || Auth.hasAnyRole('Superadmin','Admin')">{{ sale.subtotal_price | currency }}</td>
                    <td v-else>{{fulfilledAmount(sale.lines) | currency}}</td>
                    <td>{{ sale.total_tax | currency }}</td>
                    <td>{{ sale.total_shipping | currency }}</td>
                    <td>{{ sale.total_price | currency }}</td>
                    <td>{{ sale.store_owner_user_id }}</td>
                    <td>{{ sale.seller_name }}</td>
                </tr>
            </tbody>
        </table>
        <section class="cp-table-mobile">
            <div v-for="sale in sales">
                <div>
                    <span>Date of Sale: </span><span>{{ sale.created_at | cpStandardDate}}</span>
                </div>
                <div>
                    <span v-if="sale.type_id !== 6 && sale.type_id !== 7 || Auth.hasAnyRole('Superadmin','Admin')">Receipt Id:<a v-bind:href="'/orders/' + sale.receipt_id">{{ sale.receipt_id }}</a></span><span v-else>{{ sale.receipt_id }}></span>
                </div>
                <div>
                    <span>Customer Name: </span>
                    <span>{{ sale.customer_last_name }}, {{ sale.customer_first_name }}</span>
                </div>
                <div>
                    <span> Subtotal: </span>
                    <span v-if="sale.type_id !== 6  && sale.type_id !== 7 || Auth.hasAnyRole('Superadmin','Admin')">{{ sale.subtotal_price | currency }}</span>
                    <span v-else>{{fulfilledAmount(sale.lines) | currency}}></span>
                </div>
                <div>
                    <span>Tax: </span>
                    <span>{{ sale.total_tax | currency }}</span>
                </div>
                <div>
                    <span>Shipping: </span><span>{{ sale.total_shipping | currency }}</span>
                </div>
                <div>
                    <span>Total: </span><span>{{ sale.total_price | currency }}</span>
                </div>
                <div>
                    <span>Sponsor ID: </span>
                    <span>{{ sale.store_owner_user_id }}</span>
                </div>

                <div>
                    <span>Sponser Name: </span><span>{{ sale.seller_name }}</span>
                </div>
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
  data: function () {
    return {
      noResults: false,
      loading: false,
      sales: [],
      pagination: {
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
        page: 1,
        name: 'customer'
      },
      reverseSort: true,
      Auth: Auth,
      salesTotals: {}
    }
  },
  props: {
    dates: {}
  },
  computed: {},
  mounted: function () {
    Auth.hasAnyRole('Superadmin', 'Admin') ? this.indexRequest.per_page = '100' : this.indexRequest.per_page = '15'
    this.getSales()
    this.getCorpTotal()
  },
  methods: {
    getCorpTotal () {
      Sales.getCorpTotal(this.indexRequest)
        .then((response) => {
          this.salesTotals = response
        })
    },
    getSalesAndTotals () {
      this.getSales()
      this.getCorpTotal()
    },
    getSales: function () {
      this.loading = true
      this.indexRequest.start_date = moment(this.dates.start_date).format('YYYY-MM-DD')
      this.indexRequest.end_date = moment(this.dates.end_date).format('YYYY-MM-DD')
      this.indexRequest.page = this.pagination.current_page
      this.sales = {}
      Sales.getCustIndex(this.indexRequest)
        .then((response) => {
          if (response.error) {
            return this.$toast(response.message, { error: true })
          }
          if (response.total === 0) {
            this.noResults = true
            setTimeout(function () {
              this.noResults = false
            }.bind(this), 3000)
          }
          this.loading = false
          this.sales = response.data
          response.per_page = parseInt(response.per_page)
          this.pagination = response
        })
    },
    
    fulfilledAmount: function (lines) {
      var amount = 0
      lines.forEach(function (line) {
        amount = amount + line.price
      })
      this.saleAmount = amount
      return this.saleAmount
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
      this.getSales()
    },
    printOrder: function (orderId) {
      new CpOrdersFile(null, ['pdf'], 'order', {orderId: orderId}).run()
    }
  },
  components: {
    CpTotalsBanner: require('../reports/CpTotalsBanner.vue'),
    CpTableControls: require('../../cp-components-common/tables/CpTableControls.vue')
  }
}
</script>

<style lang="sass">
    @import "resources/assets/sass/var.scss";
    .sales-index-wrapper {
        .space-between {
            margin-bottom: 10px;
        }
        .action-btn-wrapper {
            margin-bottom: 10px;
        }
    }
</style>
