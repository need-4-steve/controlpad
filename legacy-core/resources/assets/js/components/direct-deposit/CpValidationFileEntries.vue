<template>
  <div class="bottom-table">
    <table class="table">
      <thead>
        <th>{{$getGlobal('title_rep').value}} ID</th>
        <th>Name</th>
        <th>Amount 1</th>
        <th>Amount 2</th>
        <th>Account NAME</th>
        <th>Paid At</th>
        <th>Created At</th>
      </thead>
      <tbody>
        <tr v-for="batch in batches">
          <td>{{batch.userId}}</td>
          <td>{{batch.userName}}</td>
          <td>{{batch.amount1 | currency}}</td>
          <td>{{batch.amount2 | currency}}</td>
          <td>{{ (batch.userAccount ? batch.userAccount.name : 'n/a')}}</td>
          <td>{{batch.submittedAt | cpStandardDate}}</td>
          <td>{{batch.createdAt | cpStandardDate}}</td>
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
      let request = {
        page: this.pagination.current_page,
        per_page: this.indexRequest.per_page,
        payment_file_id: this.fileId
      }
      DirectDeposit.getValidations(request)
        .then((response) => {
          if (response.error) {
            return this.$toast('errorMessage', response)
          }
          this.loading = false
          this.batches = response.data
          this.pagination.last_page = response.last_page
        })
    },
  }
}
</script>
