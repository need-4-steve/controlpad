<template lang="html">
    <div class="tax-owed-wrapper">
        <div class="index-table">
            <cp-table-controls
            :date-picker="true"
            :date-range="indexRequest"
            :index-request="indexRequest"
            :resource-info="pagination"
            :search-box="false"
            :get-records="getEwalletRepDetails"></cp-table-controls>
          <table class="cp-table-standard desktop">
            <thead>
              <tr>
                <th>Date</th>
                <th>Receipt ID</th>
                <th>Type</th>
                <th>Amount</th>
                <th>Sales Tax</th>
                <th>Net</th>
                <th>Balance</th>
              </tr>
            </thead>
            <tbody v-if="!loading">
              <tr v-for="transaction in repTransactionDetails">
                <td>{{ transaction.date | cpStandardDate }}</td>
                <td v-if="transaction.receipt_id"><a v-bind:href="'/orders/' + transaction.receipt_id" >{{ transaction.receipt_id }}</a></td>
                <td v-else></td>
                <td v-if="transaction.transactionType">{{ format(transaction.transactionType) }}</td>
                <td v-else>{{ format(transaction.description) }}</td>
                <td v-if="transaction.withdraw < 0">{{ transaction.withdraw | currency('floor') }}</td>
                <td v-else>{{ transaction.amount | currency('floor') }}</td>
                <td>{{ transaction.salesTax | currency('floor') }}</td>
                <td>{{ transaction.net | currency('floor') }}</td>
                <td>{{ transaction.balance | currency('floor') }}</td>
              </tr>
            </tbody>
          </table>
        </div>
        <div class="align-center">
            <div class="no-results" v-show="noResults">
                <span>No results for this timeframe</span>
            </div>
            <img class="loading" :src="$getGlobal('loading_icon').value" v-if="loading">
            <cp-pagination :pagination="pagination" :callback="getEwalletRepDetails" :offset="2"></cp-pagination>
        </div>
    </div>
</template>

<script>
const Auth = require('auth')
const EWallet = require('../../resources/ewallet.js')
const moment = require('moment')

module.exports = {
  data () {
    return {
      Auth: Auth,
      noResults: false,
      loading: false,
      totalOwed: null,
      pagination: {
        per_page: 15
      },
      repTransactionDetails: {},
      indexRequest: {
        user_id: this.userId,
        start_date: moment().subtract(10, 'days').format('YYYY-MM-DD'),
        end_date: moment().format('YYYY-MM-DD'),
        column: 'last_name',
        order: 'ASC',
        per_page: 15,
        page: 1
      }
    }
  },
  props: {
    userId: {
      required: true
    }
  },
  mounted () {
    Auth.hasAnyRole('Superadmin', 'Admin') ? this.indexRequest.per_page = '100' : this.indexRequest.per_page = '15'
    this.getEwalletRepDetails()
  },
  methods: {
    getEwalletRepDetails () {
      this.loading = true
      this.indexRequest.page = this.pagination.current_page
      EWallet.salesTaxLedger(this.indexRequest)
          .then((response) => {
            if (!response.error) {
              this.pagination = response
              this.repTransactionDetails = response.data
              this.loading = false
              return response
            }
          })
    },
    format (string) {
      if (string) {
        string = string.replace('e-wallet-', '')
        string = string.replace('merchant', 'amount')
        return string.split('-').map(function (string) {
          return string.charAt(0).toUpperCase() + string.substring(1)
        }).join(' ')
      }
    }
  },
  components: {
    CpTableControls: require('../../cp-components-common/tables/CpTableControls.vue')
  }
}
</script>

<style lang="scss">
    @import "resources/assets/sass/var.scss";
    .tax-owed-wrapper {
        .action-btn-wrapper {
            margin-bottom: 10px;
        }
    }
</style>
