<template>
  <div class="bank-search-wrapper">
    <div>
      <cp-rep-search-typeahead
        class="select-rep"
        label="Select a user"
        @rep-selected="repSelected"></cp-rep-search-typeahead>
      <div v-if="selectedRep" class="cp-box-standard">
        <div class="cp-box-heading">
            {{ 'Current Bank Account: ' + selectedRep.full_name }}
        </div>
        <div v-if="bankAccount" class="cp-box-body">
          <div><strong>Account Name: </strong>{{ bankAccount.name }}</div>
          <div><strong>Bank Name: </strong>{{ bankAccount.bankName }}</div>
          <div><strong>Routing Number: </strong>{{ bankAccount.routing }}</div>
          <div><strong>Account Number: </strong>{{ bankAccount.number }}</div>
          <div><strong>Account Type: </strong>{{ bankAccount.type }}</div>
          <div><strong>Validated: </strong>{{ bankAccount.validated ? 'Yes' : 'No' }}</div>
          <div><strong>Last Update: </strong>{{ bankAccount.updatedAt | cpStandardDate }}</div>
        </div>
        <div v-else>No account set</div>
      </div>
    </div>
    <div v-if="currentValidation && bankAccount && (currentValidation.accountHash == bankAccount.hash)" class="cp-box-standard">
      <div class="cp-box-heading">
          Current Validation
      </div>
      <div class="cp-box-body">
        <div><strong>Amount 1: </strong>{{ currentValidation.amount1 }}</div>
        <div><strong>Amount 2: </strong>{{ currentValidation.amount2 }}</div>
        <div><strong>Created: </strong>{{ currentValidation.createdAt | cpStandardDate }}</div>
        <div><strong>Submitted: </strong>{{ currentValidation.submittedAt | cpStandardDate }}</div>
      </div>
    </div>
  </div>
</template>

<script>
const Payman = require('../../resources/PaymanAPI.js')

module.exports = {
  name: 'CpBankAccountSearch',
  routing: [
    {
      name: 'site.CpBankAccountSearch',
      path: 'bank-account-search',
      meta: {
        title: 'Bank Account Search'
      },
      props: true
    }
  ],
  data () {
    return {
      selectedRep: null,
      bankAccount: null,
      currentValidation: null
    }
  },
  methods: {
    repSelected (rep) {
      this.selectedRep = rep
      this.clearBankInfo()
      this.getAccount()
      this.getCurrentValidation()
    },
    clearBankInfo () {
      this.bankAccount = null
      this.currentValidation = null
    },
    getAccount () {
      Payman.getBankAccount(this.selectedRep.id)
      .then((response) => {
        if (response.error) {
          // TODO error
          return
        }
        this.bankAccount = response
      })
    },
    getCurrentValidation () {
      // Find the most recent validation record and check it against the current account to see if they go together
      Payman.getValidations({userId: this.selectedRep.id, sortBy: '-id', page: '1', count: '1'})
        .then((response) => {
          if (response.error) {
            // TODO error
            return
          }
          if (response.count > 0) {
            this.currentValidation = response.data[0]
          }
        })
    }
  },
  components: {
    CpRepSearchTypeahead: require('../../cp-components-common/inputs/CpRepSearchTypeahead.vue')
  }
}
</script>

<style lang="scss">
  .bank-search-wrapper {
      min-width: 300px;
  }
  .select-rep {
      max-width: 300px;
  }
</style>
