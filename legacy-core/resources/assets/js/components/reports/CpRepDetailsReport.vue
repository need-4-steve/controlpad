<template lang="html">
    <div class="sales-index-wrapper">
        <div class="space-between">
            <div class="input-position">
                <i class="mdi mdi-calendar-range icon"></i> <input type="date" data-date-inline-picker="false" data-date-open-on-focus="true" v-model="dates.start_date" @change="getReptoCustomer()">
                <i class="mdi mdi-calendar-range icon"></i> <input type="date" data-date-inline-picker="false" data-date-open-on-focus="true" v-model="dates.end_date" @change="getReptoCustomer()">
            </div>
        </div>
        <table class="cp-table-standard">
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
                <th @click="sortColumn('customer_last_name')">Customer Name
                    <span v-show="indexRequest.column == 'customer_last_name'">
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
                    </td>
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
        <div class="align-center">
            <div class="no-results" v-if="noResults">
                <span>No results for this timeframe</span>
            </div>
            <img class="loading" :src="$getGlobal('loading_icon').value" v-if="loading">
            <cp-pagination :pagination="pagination" :callback="getReptoCustomer" :offset="2"></cp-pagination>
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
      Auth: Auth
    }
  },
  props: {
    userId: {
      required: true
    }
  },
  mounted: function () {
    this.getReptoCustomer()
  },
  methods: {
    getReptoCustomer () {
      this.loading = true
      this.indexRequest.start_date = moment(this.dates.start_date).format('YYYY-MM-DD')
      this.indexRequest.end_date = moment(this.dates.end_date).format('YYYY-MM-DD')
      this.indexRequest.page = this.pagination.current_page
      this.sales = {}
      Sales.getRep(this.userId, this.indexRequest)
      .then((response) => {
        if (response.error) {
          return
        }
        if (response.total === 0) {
          this.noResults = true
          setTimeout(() => {
            this.noResults = false
          }, 3000)
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
      this.getReptoCustomer()
    },
    printOrder: function (orderId) {
      new CpOrdersFile(null, ['pdf'], 'order', {orderId: orderId}).run()
    }
  },
  events: {
    'search_term': function (term) {
      this.indexRequest.search_term = term
      this.pagination.current_page = 1
      this.getReptoCustomer()
    }
  }
}
</script>

<style lang="sass">
    @import "resources/assets/sass/var.scss";
    .sales-index-wrapper {
        .action-btn-wrapper {
            margin-bottom: 10px;
        }
    }
</style>
