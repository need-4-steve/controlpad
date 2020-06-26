<template>
  <div class="cp-ledger-scoped">
    <div>
      <button class="cp-button-link" download :disabled="downloading" @click="downloadCsv()">Ledger CSV</button>
      <cp-table-controls
        :date-picker="true"
        :date-range="indexRequest"
        :index-request="indexRequest"
        :resource-info="pagination"
        :search-box="false"
        :get-records="getTransactions">
      </cp-table-controls>
      <span class="column-right"> Note: All values are rounded. Please view transaction details for exact amounts.</span>
      <div class="index-table">
        <table class="cp-table-standard desktop">
          <thead>
            <tr>
              <th>Details</th>
              <th>Order Date</th>
              <th>Posted Date</th>
              <th>Receipt ID</th>
              <th>Type</th>
              <th>Amount</th>
              <th>Fees</th>
              <th>Sales Tax</th>
              <th>Net</th>
              <th v-if="Auth.hasAnyRole('Rep')">Balance</th>
            </tr>
          </thead>
          <tbody v-if="!loading">
            <tr v-for="transaction in transactions">
              <td><a v-if="transaction.transactionId" @click="getTransactionData(transaction.transactionId), selectedTransaction = transaction">View</a></td>
              <td v-if="transaction.order">{{ transaction.order.created_at | cpStandardDate }}</td>
              <td v-else></td>
              <td>{{ transaction.date | cpStandardDate }}</td>
              <td v-if="transaction.order"><a  v-bind:href="'/orders/' + transaction.order.receipt_id" >{{ transaction.order.receipt_id }}</a></td>
              <td v-else></td>
              <td v-if="transaction.transactionType">{{ formatType(transaction) }}</td>
              <td v-else>{{ transaction.description }}</td>
              <td v-if="transaction.withdraw < 0">{{ transaction.withdraw | currency }}</td>
              <td v-else-if="transaction.affiliate > 0">{{ (transaction.order ? transaction.order.subtotal - transaction.order.discount : transaction.transactionAmount) | currency }}</td>
              <td v-else>{{ transaction.amount | currency }} </td>
              <td>{{ transaction.fees | currency }}</td>
              <td>{{ transaction.salesTax | currency }}</td>
              <td>{{ transaction.net | currency }}</td>
              <td v-if="Auth.hasAnyRole('Rep')">{{ transaction.balance | currency('floor') }}</td>
            </tr>
          </tbody>
        </table>
      </div>

      <section class="cp-table-mobile" v-if="!loading">
       <div v-for="transaction in transactions">
         <div><span>Details: </span><span><a v-if="transaction.transactionId" @click="getTransactionData(transaction.transactionId), selectedTransaction = transaction">View</a></span></div>
         <div><span>Date: </span><span>{{ transaction.date | cpStandardDate }}</span></div>
         <div v-if="transaction.order"><span>Receipt ID: </span><span><a v-bind:href="'/orders/'+ transaction.order.receipt_id">{{ transaction.order.receipt_id }}</a></span></div>
         <div><span></span></div>
         <div v-if="transaction.transactionType"><span>Type: </span><span>{{ formatType(transaction) }}</span></div>
         <div v-else><span>Type: </span><span>{{ transaction.description }}</span></div>
         <div v-if="transaction.withdraw < 0"><span>Amount: </span><span>{{ transaction.withdraw | currency }}</span></div>
         <div v-else-if="transaction.affiliate > 0"><span>Amount: </span><span>{{ (transaction.order ? transaction.order.subtotal - transaction.order.discount : transaction.transactionAmount) | currency }}</span></div>
         <div v-else><span>Amount: </span><span>{{ transaction.amount | currency }}</span></div>
         <div><span>Fees: </span><span>{{ transaction.fees | currency }}</span></div>
         <div><span>Sales Tax: </span><span>{{ transaction.salesTax | currency }}</span></div>
         <div><span>Net: </span><span>{{ transaction.net | currency }}</span></div>
         <div v-if="Auth.hasAnyRole('Rep')"><span>Balance: </span><span>{{ transaction.balance | currency('floor') }}</span></div>
       </div>
      </section>

      <div class="align-center">
          <img class="loading" :src="$getGlobal('loading_icon').value" v-if="loading">
          <cp-pagination :pagination="pagination" :callback="getTransactions" :offset="2"></cp-pagination>
      </div>
    </div>

    <transition name='modal'>
      <section class="cp-modal-standard" v-if="transactionDetailsModal == true">
        <div class="cp-modal-body">
          <div class="cp-modal-header">
            <h2>Transaction Details</h2>
            <span @click="transactionDetailsModal = false"><i class="mdi mdi-close"></i> </span>
          </div>
            <div class="modal-left">
              <div v-show="transactionDetails.accountHolder">
                <strong>
                  Account Holder
                </strong>
                <span class="column-right">
                  {{ transactionDetails.accountHolder }}
                </span>
            </div>
              <div>
                <strong>
                  Date
                </strong>
                <span class="column-right">
                  {{ transactionDetails.date | cpStandardDate }}
                </span>
              </div>
              <div v-show="selectedTransaction.receipt_id">
                <strong>
                  Receipt ID
                </strong>
                <span class="column-right">
                  {{ selectedTransaction.receipt_id }}
               </span>
              </div>
              <div v-if="transactionDetails.transactionType">
                <strong>
                  Type
                </strong>
                <span class="column-right">
                  {{ formatType(transactionDetails) }}
                </span>
              </div>
              <br>
              <div v-if="selectedTransaction">
                <strong>
                  Amount
                </strong>
                <span class="column-right" v-if="selectedTransaction.amount">
                   {{ selectedTransaction.amount | currency }}
                </span>
              </div>

          <div v-for="details in transactionDetails.entries">
              <span v-show="details.description">
                      <strong v-if="details.description === 'Credit Card Sale'">
                          <span>
                              {{ format(details.description) + ' Fee'}}
                          </span>
                      </strong>
                      <strong v-else-if="details.description != 'Credit Card Sale'">
                          <span>
                              {{ format(details.description) }}
                          </span>
                      </strong>
                  <strong v-else>
                      <span >
                          {{ format(details.type) }}
                      </span>
                  </strong>
                      <span class="column-right" v-if="details.amount">
                          {{ details.amount | currency5 }}
                      </span>
                  </span>
              </div>
          <div>
            <strong>
               Sales Tax
            </strong>
            <span class="column-right" v-if="selectedTransaction.salesTax">
               {{ selectedTransaction.salesTax | currency5 }}
            </span>
          </div>
          <div>
              <strong>
                  Net
              </strong>
              <span class="column-right" v-if="selectedTransaction.net">
                  {{ selectedTransaction.net | currency5 }}
              </span>
          </div>
          <div>
            <strong>
              Balance
            </strong>
            <span class="column-right" v-if="selectedTransaction.balance">
              {{ selectedTransaction.balance | currency5 }}
            </span>
          </div>
          <div v-show="selectedTransaction.gatewayReferenceId">
            <strong>
              Gateway Transaction ID
            </strong>
            <span class="column-right">
              {{ selectedTransaction.gatewayReferenceId }}
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
const Payman = require('../../../resources/PaymanAPI.js')
const Users = require('../../../resources/users.js')
const moment = require('moment-timezone')

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
      downloading: false,
      downloadHelper: {
        params: {},
        timezone: null,
        transactions: []
      },
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
  props: {
    source: {
      type: String,
      default: 'Rep',
      required: false
    }
  },
  methods: {
    getTransactions: function () {
      this.loading = true
      let request = JSON.parse(JSON.stringify(this.indexRequest))
      request.page = this.pagination.current_page
      request.source = this.source
      if (request.end_date < request.start_date) {
        request.end_date = request.start_date
      }
      EWallet.ledger(request)
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
      EWallet.transaction(transactionId)
        .then((response) => {
          if (!response.error) {
            this.transactionDetails = response
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
    },
    formatType: function (transaction) {
      if (transaction.description === 'Rep Transfer') {
        return 'Rep Transfer'
      } else if (transaction.description === 'Affiliate') {
        return (transaction.order ? transaction.order.type : 'Affiliate Sale')
      }
      switch (transaction.transactionType) {
        case 'e-wallet-sale': // For now this is only inventory purchase
          return 'Inventory Purchase'
        case 'credit-card-sale':
          return 'Credit Card Sale'
        case 'debit-card-sale':
          return 'Debit Card Sale'
        case 'e-wallet-withdraw':
          return 'Withdraw'
        case 'e-wallet-deposit':
          return 'Deposit'
        case 'e-wallet-debit':
          return 'Debit'
        case 'e-wallet-payment-tax':
          return 'eWallet Tax Payment'
        default:
          let string = transaction.transactionType.replace('e-wallet', 'eWallet')
          return string.split('-').map(function (string) {
            return string.charAt(0).toUpperCase() + string.substring(1)
          }).join(' ')
      }
    },
    writeCsv: function () {
      // If this.downloadHelper.transaction is empty then show message
      if (this.downloadHelper.transactions.length == 0) {
        this.$toast('No items found for download.', { error: true })
        return false
      }
      let csvFile = "Date,Receipt ID,Type,Amount,Fees,Sales Tax,Net,Balance\n" // Header
      let transaction = null
      let timezone = (this.downloadHelper.timezone ? this.downloadHelper.timezone : moment.tz.guess())
      for (let i = 0; i < this.downloadHelper.transactions.length; i++) {
        transaction = this.downloadHelper.transactions[i]
        csvFile = csvFile
        + moment.tz(transaction.date, 'YYYY-MM-DD HH:mm:ss', 'utc').tz(timezone).format('D MMM YYYY h:mm:ss A z') + ','
        + transaction.orderId + ','
        + this.formatType(transaction) + ','
        + '"' + window.Vue.options.filters.currency(transaction.amount) + '",'
        + '"' + window.Vue.options.filters.currency(transaction.fees) + '",'
        + '"' + window.Vue.options.filters.currency(transaction.salesTax) + '",'
        + '"' + window.Vue.options.filters.currency(transaction.net) + '",'
        + '"' + window.Vue.options.filters.currency(transaction.balance, true) + "\",\n"
      }
      // Download text as file
      var uri = window.URL.createObjectURL(new window.Blob([csvFile], {
        type: 'text/plain'
      }))
      var a = document.createElement('a')
      document.body.appendChild(a)
      a.style = 'display: none'
      a.href = uri
      a.download = 'LedgerBalance.csv'
      a.click()
      window.URL.revokeObjectURL(uri)
      a.parentNode.removeChild(a)
      this.$toast('Finished.', { error: false })
    },
    csvPaginateLoop: function (response, start) {
      if (start === false) {
        // This is the loop check
        if (response.error) {
          this.$toast('Unexpected error. Please try again later.', { error: true })
          downloading = false
          return false
        }
        this.downloadHelper.transactions = this.downloadHelper.transactions.concat(response.data) // add transactions to array
        if (this.downloadHelper.params.page >= response.totalPage || response.data.length == 0) {
          this.writeCsv()
          downloading = false
          return false
        }
        this.downloadHelper.params.page += 1 // next page
      }
      Payman.getEWalletLedger(this.downloadHelper.params)
        .then((response) => {
          this.csvPaginateLoop(response, false)
        })
    },
    downloadCsv: function () {
      downloading = true
      this.downloadHelper.transactions = []
      this.downloadHelper.params = {
        teamId: 'rep',
        userId: Auth.getOwnerId().toString(),
        startDate: moment(this.indexRequest.start_date, 'YYYY-MM-DD').startOf('day').utc().format('YYYY-MM-DD HH:mm:ss.SSS'),
        endDate: moment(this.indexRequest.end_date, 'YYYY-MM-DD').endOf('day').utc().format('YYYY-MM-DD HH:mm:ss.SSS'),
        page: 1,
        count: 50
      }
      Users.userSettings(Auth.getOwnerId()).then((response) => {
        if (response.timezone) {
          this.downloadHelper.timezone = response.timezone
        }
        // Start loop
        this.csvPaginateLoop(null, true)
      })
    }
  },
  filters: {
    currency5: function (value) {
      var currency = '$'
      if (value < 0) {
        value *= -1
        currency = '-$'
      }
      var parts = value.toFixed(5).toString().split('.')
      return currency + parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ',') + '.' + parts[1]
    }
  },
  computed: {
    csvLink () {
      return '/api/v1/ewallet/csv-ledger?order=' + this.indexRequest.order + '&start_date=' + this.indexRequest.start_date + '&end_date=' + this.indexRequest.end_date + '&per_page=100&source=' + this.source
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
    .cp-ledger-scoped {
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
