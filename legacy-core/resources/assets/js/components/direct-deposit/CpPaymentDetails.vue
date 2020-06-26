<template lang="html">
  <div>
    <div>
      <h3>Batch Details</h3>
      <table>
        <tr>
          <th>Batch Name: </th>
          <td>{{ batch.id }}</td>
        </tr>
        <tr>
          <th>Deposit Count: </th>
          <td>{{ batch.paymentCount }}</td>
        </tr>
        <tr>
          <th>Net Amount: </th>
          <td>{{ batch.netAmount | currency }}</td>
        </tr>
      </table>
    </div>
    <div>
    </br>
      <a class="cp-button-link" download :href="'/api/v1/payment/csv/detail/' + batch.id + '?page=' + request.page
        + '&per_page=' + request.per_page">Download CSV</a>
    </div>
      <h3>Payment Details </h3>
      <div>
          <table class="cp-table-standard">
                <thead>
                    <tr>
                        <th>Batch Name</th>
                        <th>{{$getGlobal('title_rep').value}} Name</th>
                        <th>{{$getGlobal('title_rep').value}} ID</th>
                        <th>Amount</th>
                        <th>Gateway Reference ID</th>

                    </tr>
                </thead>
              <tbody>
                  <tr v-for="payment in payments">
                      <td>{{payment.id}}</td>
                      <td>{{payment.name}}</td>
                      <td>{{payment.userId}}</td>
                      <td>{{ payment.amount | currency }}</td>
                      <td>{{payment.referenceId}}</td>
                  </tr>
              </tbody>
          </table>
      </div>
      <div class="flex-end">
          <button class="cp-button-standard cancel" @click="$emit('close', false)">Close</button>
      </div>
      <div class="align-center">
        <div class="no-results" v-if="noResults">
            <span>No results for this timeframe</span>
        </div>
        <img class="loading" :src="$getGlobal('loading_icon').value" v-if="loading">
        <cp-pagination :pagination="pagination" :callback="getBatchInfo" :offset="2"></cp-pagination>
      </div>
  </div>
</template>

<script>
const DirectDeposit = require('../../resources/direct-deposit.js')

module.exports = {
  data () {
    return {
      loading: false,
      pagination: {
        current_page: 1
      },
      payments: {},
      noResults: false
    }
  },
  props: {
    batch: {
      type: Object
    },
    request: {
      type: Object
    }

  },
  mounted () {
    this.getBatchInfo(this.batch)
  },
  methods: {
    getBatchInfo: function () {
      this.request.page = this.pagination.current_page
      DirectDeposit.paymentBatchId(this.batch.id, this.request)
        .then((response) => {
          if (!response.error) {
            if (response.total === 0) {
              this.noResults = true
            }
            this.payments = response.data
            this.pagination = response
          }
        })
    }
  }
}
</script>

<style lang="scss">
@import "resources/assets/sass/var.scss";

</style>
