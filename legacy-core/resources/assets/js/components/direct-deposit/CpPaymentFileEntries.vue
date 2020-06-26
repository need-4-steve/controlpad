<template>
  <div class="bottom-table">
    <table class="table">
      <thead>
        <th>Payment ID</th>
        <th>Amount</th>
        <th>{{$getGlobal('title_rep').value}} ID</th>
        <th>{{$getGlobal('title_rep').value}} NAME</th>
        <th>Type</th>
        <th>Paid At</th>
      </thead>
      <tbody>
          <tr v-for="batch in batches">
              <td>{{batch.id}}</td>
              <td>{{batch.amount | currency}}</td>
              <td v-if="batch.user">{{batch.user.repId}}</td>
              <td v-else>n/a</td>
              <td v-if="batch.user">{{batch.user.name}}</td>
              <td v-else>n/a</td>
              <td>{{batch.type}}</td>
              <td v-if="batch.paidAt">{{batch.paidAt | cpStandardDate}}</td>
              <td v-else>n/a</td>
          </tr>
      </tbody>
    </table>
    <div class="align-center">
        <img class="loading" :src="$getGlobal('loading_icon').value" v-if="loading">
        <cp-pagination :pagination="pagination" :callback="getBatches" :offset="2"></cp-pagination>
    </div>
  </div>
</template>
<script>
const DirectDeposit = require('../../resources/direct-deposit.js');

module.exports = {
  props: {
    fileId: {
      type: String,
      required: true
    }
  },
  data: function () {
    return {
      batches: {},
      loading: true,
      pagination: {
        current_page: 1,
        last_page: 0
      },
      asc: false,
      indexRequest: {
        page: 1,
        order: 'ASC',
        column: 'name',
        per_page: 25,
        search_term: '',
        userId: '',
        transactionId: '',
        amount: '',
        cardHolder: '',
        paymentFileId: '',
        term: ''
      },
      reverseSort: false
    }
  },
  created: function () {
    this.getBatches()
  },
  methods: {
    getBatches: function () {
      this.loading = true
      this.batches = {}
      this.indexRequest.page = this.pagination.current_page
      this.indexRequest.paymentFileId = this.fileId

      DirectDeposit.batchDetails(this.indexRequest)
        .then((response) => {
          if (response.error) {
            return this.$toast('errorMessage', response.message)
          }
          this.loading = false
          this.batches = response.data
          this.pagination.last_page = response.last_page
        })
    },
  }
}
</script>
