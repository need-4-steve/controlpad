<template>
  <div class="sales-tax-ledger">
      <a class="cp-button-link" download :href="csvLink">Sales Tax Ledger CSV</a>
      <cp-tooltip :options="{ content: 'Max count of 100'}"></cp-tooltip>
      <!-- <br/>
      <br/> -->
      <cp-table-controls
        :date-picker="true"
        :date-range="indexRequest"
        :index-request="indexRequest"
        :resource-info="pagination"
        :search-box="false"
        :get-records="getTransactions">
      </cp-table-controls>
      <div class="index-table">
        <table class="cp-table-standard desktop">
          <thead>
            <tr>
              <th>Details</th>
              <th>Date</th>
              <th>Receipt ID</th>
              <th>Type</th>
              <th>Amount</th>
              <th>Sales Tax</th>
              <th>Net</th>
              <th v-if="Auth.hasAnyRole('Rep')">Balance</th>
            </tr>
          </thead>
          <tbody v-if="!loading">
            <tr v-for="transaction in transactions">
              <td><a v-if="transaction.transactionId" @click="getTransactionData(transaction.transactionId), selectedTransaction = transaction">View</a></td>
              <td>{{ transaction.date | cpStandardDate }}</td>
              <td v-if="transaction.receipt_id"><a :href="'/orders/' + transaction.receipt_id" >{{ transaction.receipt_id }}</a></td>
              <td v-else></td>
              <td v-if="transaction.transactionType">{{ format(transaction.transactionType) }}</td>
              <td v-else>{{ format(transaction.description) }}</td>
              <td v-if="transaction.withdraw < 0">{{ transaction.withdraw | currency('floor') }}</td>
              <td v-else>{{ transaction.amount | currency('floor') }}</td>
              <td>{{ transaction.salesTax | currency('floor') }}</td>
              <td>{{ transaction.net | currency('floor') }}</td>
              <td v-if="Auth.hasAnyRole('Rep')">{{ transaction.balance | currency('floor') }}</td>
            </tr>
          </tbody>
        </table>
      </div>
      <section class="cp-table-mobile" v-if="!loading">
       <div v-for="transaction in transactions">
         <div><span>Details: </span><span><a v-if="transaction.transactionId" @click="getTransactionData(transaction.transactionId), selectedTransaction = transaction">View</a></span></div>
         <div><span>Date: </span><span>{{ transaction.date | cpStandardDate }}</span></div>
         <div v-if="transaction.receipt_id"><span>Receipt ID: </span><span><a v-bind:href="'/orders/' + transaction.receipt_id">{{ transaction.receipt_id }}</a></span></div>
         <div v-else><span></span></div>
         <div v-if="transaction.transactionType"><span>Type: </span><span>{{ format(transaction.transactionType) }}</span></div>
         <div v-else><span>Type: </span><span>{{ format(transaction.description) }}</span></div>
         <div v-if="transaction.withdraw < 0"><span>Amount: </span><span>{{ transaction.withdraw | currency('floor') }}</span></div>
         <div v-else><span>Amount: </span><span>{{ transaction.amount | currency('floor') }}</span></div>
         <div><span>Fees: </span><span>{{ transaction.fees | currency('floor') }}</span></div>
         <div><span>Sales Tax: </span><span>{{ transaction.salesTax | currency('floor') }}</span></div>
         <div><span>Net: </span><span>{{ transaction.net | currency('floor') }}</span></div>
         <div v-if="Auth.hasAnyRole('Rep')"><span>Balance: </span><span>{{ transaction.balance | currency('floor') }}</span></div>
       </div>
      </section>
      <div class="align-center">
          <img class="loading" :src="$getGlobal('loading_icon').value" v-if="loading">
          <cp-pagination :pagination="pagination" :callback="getTransactions" :offset="2"></cp-pagination>
      </div>
    <transition name="fade">
      <section class="cp-modal-standard" v-if="transactionDetailsModal === true">
        <div class="cp-modal-body">
          <div class="cp-modal-header">
            <h3>Transaction Details</h3>
            <span @click="transactionDetailsModal = false"><i class="mdi mdi-close"></i> </span>
          </div>
          <div class="modal-left">
            <div v-show="transactionDetails.accountHolder">
              <strong>Account Holder</strong>
              <span class="column-right">
                {{ transactionDetails.accountHolder }}
              </span>
            </div>
            <div>
              <strong>Date</strong>
              <span class="column-right">
                {{ transactionDetails.date | cpStandardDate('time') }}
              </span>
           </div>
            <div>
              <strong>Receipt ID</strong>
              <span class="column-right">
                {{ selectedTransaction.receipt_id }}
              </span>
            </div>
            <div>
                <div v-show="selectedTransaction.gatewayReferenceId">
                   <strong>Gateway Transaction ID</strong>
                    <span class="column-right">
                        {{ selectedTransaction.gatewayReferenceId }}
                    </span>
                </div>
            </div>
            <div>
                <div>
                    <strong>Type</strong>
                    <span class="column-right">
                        {{ format(transactionDetails.transactionType) }}
                    </span>
                </div>
            </div>
            <div>
                <strong>Net</strong>
                <span class="column-right">
                    {{ selectedTransaction.net | currency('floor') }}
                </span>
              </div>
            </div>
          </div>
      </section>
    </transition>
</div>
</template>

<script>
const Auth = require('auth')
const EWallet = require('../../../resources/ewallet.js')
const moment = require('moment')

module.exports = {
  data: function () {
    return {
      Auth: Auth,
      loading: false,
      transactions: {},
      transactionId: null,
      transactionDetails: {},
      transactionDetailsModal: false,
      pagination: {},
      report: {},
      selectedTransaction: {},
      indexRequest: {
        start_date: moment().subtract(10, 'days').format('YYYY-MM-DD'),
        end_date: moment().format('YYYY-MM-DD'),
        search_term: null,
        column: 'last_name',
        order: 'ASC',
        per_page: 15,
        page: 1
      }
    }
  },
  mounted: function () {
    Auth.hasAnyRole('Superadmin', 'Admin') ? this.indexRequest.per_page = '100' : this.indexRequest.per_page = '15'
    this.getTransactions()
  },
  methods: {
    getReport: function () {
      EWallet.salesTaxLedger()
        .then((response) => {
          this.report = response
          this.loading = false
        })
    },
    getTransactions: function () {
      this.loading = true
      let request = JSON.parse(JSON.stringify(this.indexRequest))
      request.page = this.pagination.current_page
      if (request.end_date < request.start_date) {
        request.end_date = request.start_date
      }
      EWallet.salesTaxLedger(request)
        .then((response) => {
          if (!response.error) {
            this.pagination = response
            this.transactions = response.data
            this.loading = false
            return response
          }
        })
    },
    getTransactionData: function (transactionId) {
      this.loading = true
      EWallet.transaction(transactionId)
        .then((response) => {
          if (!response.error) {
            this.transactionDetails = response
            this.loading = false
            this.transactionDetailsModal = true
          }
        })
    },
    format: function (string) {
      if (string) {
        string = string.replace('e-wallet-', '')
        string = string.replace('merchant', 'amount')
        return string.split('-').map(function (string) {
          return string.charAt(0).toUpperCase() + string.substring(1)
        }).join(' ')
      }
    }
  },
  events: {
  },
  computed: {
    csvLink () {
      return '/api/v1/ewallet/csv-sales-tax?order=' + this.indexRequest.order + '&start_date=' + this.indexRequest.start_date + '&end_date=' + this.indexRequest.end_date + '&per_page=100'
    }
  },
  components: {
    CpTableControls: require('../../../cp-components-common/tables/CpTableControls.vue'),
    CpWithdraw: require('../../ewallet/rep/CpWithdraw.vue'),
    CpTooltip: require('../../../custom-plugins/CpTooltip.vue')
  }
}
</script>

<style lang="scss">
  .sales-tax-ledger {
    .index-table {
      padding-top: 10px;
    }
    .column-right {
        float: right;
      text-align: right;
    }
    .column-left {
      width: 65%;
      float: left;
      padding-left: 20px
    }
    .modal-left {
      width: 100%;
      padding-right: 8px;
      padding-left: 8px;
      padding-bottom: 8px;
    }
    .exit {
      color: white;
    }
    .cp-modal-header {
      h2 {
        margin: 0px;
      }
      display: flex;
      justify-content: space-between;
    }
  }
</style>
