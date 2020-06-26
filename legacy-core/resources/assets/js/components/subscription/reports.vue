<template lang="html">
    <div class="subscription-reports-wrapper">
        <br>
        <a class="cp-button-link" download :href="'/api/v1/subscriptions/csv-download-receipt?status=' + indexRequest.status
          + '&start_date=' + indexRequest.start_date
          + '&end_date=' + indexRequest.end_date
          + '&search_term=' + indexRequest.search_term">Download CSV</a>
        <a class="cp-button-link"  @click="sendemailcsvreport(indexRequest.status , indexRequest.start_date , indexRequest.end_date ,indexRequest.search_term)"
           href="javascript:void(0)" target="_blank">Send CSV in Mail</a>

        <cp-totals-banner :totals="[
   {title:'Total Transactions Amount', amount: report.amount},
   {title:'Number of Transactions', count: report.count},
   {title:'Auto Renew Subscriptions', count: report.autoCount}]"></cp-totals-banner>

        <cp-table-controls :date-picker="true"
                           :date-range="indexRequest"
                           :index-request="indexRequest"
                           :resource-info="pagination"
                           :get-records="getTransactionReport">
        </cp-table-controls>

        <table class="cp-table-standard desktop">
            <thead>
            <th @click="sortColumn('first_name')">
                Name
                <span v-show="indexRequest.column == 'first_name'">
                    <span v-show="reverseSort"><i class='mdi mdi-sort-ascending'></i></span>
                    <span v-show="!reverseSort"><i class='mdi mdi-sort-descending'></i></span>
                </span>
            </th>
            <th @click="sortColumn('title')">
                Title
                <span v-show="indexRequest.column == 'title'">
                    <span v-show="reverseSort"><i class='mdi mdi-sort-ascending'></i></span>
                    <span v-show="!reverseSort"><i class='mdi mdi-sort-descending'></i></span>
                </span>
            </th>
            <th@click ="sortColumn('created_at')">
                Date
                <span v-show="indexRequest.column == 'created_at'">
                    <span v-show="reverseSort"><i class='mdi mdi-sort-ascending'></i></span>
                    <span v-show="!reverseSort"><i class='mdi mdi-sort-descending'></i></span>
                </span>
                </th>
                <th @click="sortColumn('subtotal_price')">
                    Subtotal Price
                    <span v-show="indexRequest.column == 'subtotal_price'">
                        <span v-show="reverseSort"><i class='mdi mdi-sort-ascending'></i></span>
                        <span v-show="!reverseSort"><i class='mdi mdi-sort-descending'></i></span>
                    </span>
                </th>
                <th @click="sortColumn('total_tax')">
                    Total Tax
                    <span v-show="indexRequest.column == 'total_tax'">
                        <span v-show="reverseSort"><i class='mdi mdi-sort-ascending'></i></span>
                        <span v-show="!reverseSort"><i class='mdi mdi-sort-descending'></i></span>
                    </span>
                </th>
                <th @click="sortColumn('total_price')">
                    Total Price
                    <span v-show="indexRequest.column == 'total_price'">
                        <span v-show="reverseSort"><i class='mdi mdi-sort-ascending'></i></span>
                        <span v-show="!reverseSort"><i class='mdi mdi-sort-descending'></i></span>
                    </span>
                </th>
                </thead>
                <tbody v-if="receipts.length > 0">
                    <tr v-for="receipt in receipts">
                        <td>{{receipt.first_name + ' ' + receipt.last_name}}</td>
                        <td>{{ receipt.title }}</td>
                        <td>{{receipt.created_at | cpStandardDate}}</td>
                        <td>{{receipt.subtotal_price | currency}}</td>
                        <td>{{receipt.total_tax | currency}}</td>
                        <td>{{receipt.total_price | currency}}</td>
                    </tr>
                </tbody>
                <tbody v-else>
                    <tr class="row">
                        <td class="cell">
                            <span class="overflow">There are no users to display.</span>
                        </td>
                    </tr>
                </tbody>
        </table>
        <section class="cp-table-mobile">
            <div v-for="receipt in receipts">
                <div><span>Name: </span><span>{{receipt.first_name + ' ' + receipt.last_name}}</span></div>
                <div><span>Title: </span><span>{{ receipt.title }}</span></div>
                <div><span>Date: </span><span>{{receipt.ends_at | cpStandardDate}}</span></div>
                <div><span>Subtotal Price: </span><span>{{receipt.subtotal_price | currency}}</span></div>
                <div><span>Total Tax: </span><span>{{receipt.total_tax| currency}}</span></div>
                <div><span>Total Price: </span><span>{{receipt.total_price| currency}}</span></div>
            </div>
        </section>
        <div class="align-center">
            <div class="no-results" v-if="noResults">
                <span>No results for this timeframe</span>
            </div>
            <img class="loading" :src="$getGlobal('loading_icon').value" v-if="loading">
            <cp-pagination :pagination="pagination" :callback="getSubscriptionReceipts" :offset="2"></cp-pagination>
        </div>
        <!--  MODAL -->
        <transition>
            <section class="cp-modal-standard" v-if="showReceipt" @click="showReceipt = false">
                <div class="cp-modal-body">
                    <cp-show-receipt :user="userReceipt" :indexRequest="indexRequest"></cp-show-receipt>
                </div>
            </section>
        </transition>
    </div>
</template>

<script>
const moment = require('moment')
const Subscription = require('../../resources/subscription.js')

module.exports = {
  name: 'CpSubscriptionReports',
  routing: [
    {
      name: 'site.CpSubscriptionReports',
      path: 'reports/subscriptions',
      meta: {
        title: 'Subscription Reports'
      },
      props: true
    }
  ],
  data: function () {
    return {
      receipts: {},
      pagination: {
        per_page: 15
      },
      reverseSort: false,
      noResults: false,
      loading: false,
      showReceipt: false,
      indexRequest: {
        start_date: moment().subtract(1, 'months').format('YYYY-MM-DD'),
        end_date: moment().format('YYYY-MM-DD'),
        per_page: 15,
        search_term: '',
        page: 1
      },
      report: {
        count: '',
        amount: '',
        autoCount: ''
      },
      users: {},
      userReceipt: {}
    }
  },
  mounted: function () {
    this.getTransactionReport()
  },
    methods: {
      sendemailcsvreport: function (status, start_date, end_date, search_term) {
        const url = '/api/v1/subscriptions/csv-sendmail-receipt?status=' + status + '&start_date=' + start_date + '&end_date=' + end_date + '&search_term=' + search_term;
        const token = (window.localStorage.getItem('jwt_token') || '')
        const headers = new window.Headers()
        const options = { headers }
        options.credentials = 'include'
        headers.append('Authorization', `Bearer ${token}`)
        let filename = 'filename.ext'
        this.loading = true
        window.fetch(url, options)
            .then(res => {
                alert("mail sent successfully");
                this.loading = false;
            })
    },
    getTransactionReport: function () {
      Subscription.transactionReport(this.indexRequest)
        .then((response) => {
          if (response.error) {
            return this.$toast(response.message)
          }
          this.report.count = response.count
          this.report.amount = response.amount
          this.report.autoCount = response.autoCount
        })
      this.getSubscriptionReceipts()
    },
    getSubscriptionReceipts: function () {
      this.indexRequest.page = this.pagination.current_page
      Subscription.subscriptionReceipts(this.indexRequest)
        .then((response) => {
          if (!response.error) {
            this.receipts = response.data
            this.pagination = response
          }
        })
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
      this.getSubscriptionReceipts()
    }
  },
  events: {
  },
  components: {
    CpTableControls: require('../../cp-components-common/tables/CpTableControls.vue'),
    CpTotalsBanner: require('../subscription/CpTotalsBanner.vue'),
    CpTabs: require('../../cp-components-common/navigation/CpTabs.vue'),
    'CpShowReceipt': require('./CpShowReceipt.vue')
  }
}
</script>

<style lang="sass">
  .subscription-reports-wrapper {
  }
</style>
