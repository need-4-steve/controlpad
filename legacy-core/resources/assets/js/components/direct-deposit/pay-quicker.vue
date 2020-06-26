<template>
    <div class="">
      <a class="cp-button-link" download :href="'/api/v1/payment/csv/payment?start_date=' + indexRequest.start_date
        + '&end_date=' + indexRequest.end_date
        + '&status=' + indexRequest.status
        + '&page=' + indexRequest.page
        + '&per_page=' + indexRequest.per_page">Download CSV</a>
      <cp-tabs
     :items="[
       { name: 'Open', active: true },
       { name: 'Queued', active: false },
       { name: 'Processing', active: false },
       { name: 'Closed', active: false },
       { name: 'All', active: false }
     ]"
     :callback="selectStatus"></cp-tabs>
     <cp-table-controls
       :date-picker="true"
       :date-range="indexRequest"
       :index-request="indexRequest"
       :resource-info="pagination"
       :get-records="getPaymentLists">
     </cp-table-controls>
        <table class="cp-table-standard top-table">
            <thead>
                <th>Batch Name</th>
                <th>Deposit Count</th>
                <th>Status</th>
                <th>Submitted Date</th>
                <th>Net Amount</th>
                <th><!-- submit button --></th>
            </thead>
            <tbody>
                <tr v-for="payment in payments">
                  <td><a @click="detailModal = true, batch=payment">{{payment.id}}</a></td>
                  <td>{{payment.paymentCount}}</td>
                  <td>{{payment.status}}</td>
                  <td>{{payment.submittedAt}}</td>
                  <td>{{payment.netAmount | currency}}</td>
                  <td v-if="payment.status == 'open'"><button class="cp-button-standard" @click="showConfirm = true, paymentId = payment.id">Close Batch</button></td>
                </tr>
            </tbody>
        </table>
        <cp-confirm
        :message="'Are you sure you want to close this batch?'"
        v-model="showConfirm"
        :show="showConfirm"
        :callback="submitPayment"
        :params="{}"></cp-confirm>
        <div class="align-center">
          <div class="no-results" v-if="noResults">
              <span>No results for this timeframe</span>
          </div>
          <img class="loading" :src="$getGlobal('loading_icon').value" v-if="loading">
          <cp-pagination :pagination="pagination" :callback="getPaymentLists" :offset="2"></cp-pagination>
        </div>
        <transition name=modal>
          <section class="cp-modal-standard" v-if="detailModal">
            <div class="cp-modal-body">
              <cp-payment-details :batch="batch" :request="indexRequest":modal-show="detailModal" @close="detailModal=false"></cp-payment-details>
            </div>
          </section>
      </transition>
    </div>
</template>
<script>
const DirectDeposit = require('../../resources/direct-deposit.js')
const moment = require('moment')

module.exports = {
  name: 'CpPaymentLists',
  routing: [
    {
      name: 'site.CpPaymentLists',
      path: 'direct-deposit/paymentList',
      meta: {
        title: 'Pay Quicker'
      },
      props: true
    }
  ],
  data: function () {
    return {
      payments: {},
      payment: {},
      paymentId: '',
      pagination: {
        current_page: 1
      },
      batch:{},
      noResults: false,
      selectedBatch: [],
      loading: false,
      detailModal: false,
      showConfirm: false,
      indexRequest: {
        page: 1,
        per_page:100,
        status: 'open',
        search_term: '',
        start_date: moment().subtract(10, 'days').format('YYYY-MM-DD'),
        end_date: moment().format('YYYY-MM-DD')
      }
    }
  },
  mounted () {
    this.getPaymentLists()
  },
  methods: {
    selectStatus (name) {
      switch (name) {
        case 'All':
          this.indexRequest.status = 'all'
          break
        case 'Open':
          this.indexRequest.status = 'open'
          break
        case 'Processing':
          this.indexRequest.status = 'processing'
          break
        case 'Closed':
          this.indexRequest.status = 'closed'
          break
        case 'Queued':
          this.indexRequest.status = 'queued'
          break
        default: }
      this.getPaymentLists()
    },
    getPaymentLists: function () {
      this.loading = true
      this.noResults = false
      this.payments = {}
      this.indexRequest.page = this.pagination.current_page
      DirectDeposit.paymentList(this.indexRequest)
        .then((response) => {
          if (response.error) {
          }
          if (response.total === 0) {
            this.noResults = true
          }
          this.loading = false
          this.payments = response.data
          this.pagination = response
        })
    },
    submitPayment: function () {
      DirectDeposit.submitPayment(this.paymentId)
        .then((response) => {
          if (!response.error) {
            this.getPaymentLists()
            return this.$toast('Batch has been submited', { dismiss: true })
          }
        })
    },
    getBatchInfo: function (batch) {
      this.detailModal = true
      this.payment = batch
      this.indexRequest.page = 1
      DirectDeposit.paymentBatchId(batch.id, this.indexRequest)
        .then((response) => {
          if (!response.error) {
            if (response.total === 0) {
              this.noResults = true
            }
            this.selectedBatch = response
          }
        })
    }
  },
  components: {
    'CpPaymentDetails': require('./CpPaymentDetails.vue'),
    'CpConfirm': require('../../cp-components-common/CpConfirm.vue'),
    CpTableControls: require('../../cp-components-common/tables/CpTableControls.vue'),
    CpTabs: require('../../cp-components-common/navigation/CpTabs.vue')
  }
}
</script>

<style lang="sass">
</style>
