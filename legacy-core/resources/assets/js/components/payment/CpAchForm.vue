<template lang="html">
  <div class="payment-form-simple">
    <form class="cp-form-standard credit-card-form" >
      <cp-input
      label="Account Holder Name"
      type="text"
      :value="paymentData.account_name"
      ></cp-input>
      <span v-show="validationErrors['account_name']" class="cp-warning-message">{{ validationErrors['account_name'] }}</span>
      <cp-input
      type="number"
      label="Account Number"
      :value="paymentData.account_number"
      ></cp-input>
      <span v-show="validationErrors['account_number']" class="cp-warning-message">{{ validationErrors['account_number'] }}</span>
      <cp-input
      type="number"
      label="Routing Number"
      :value="paymentData.routing_number"
      ></cp-input>
      <span v-show="validationErrors['routing_number']" class="cp-warning-message">{{ validationErrors['routing_number'] }}</span>
    </form>
  </div>
</template>

<script>
module.exports = {
  data () {
    return {
    }
  },
  props: {
    paymentData: {
      type: Object,
      required: true
    },
    validationErrors: {
      type: Object,
      required: false
    }
  },
  ready () {
  },
  methods: {
  },
  components: {
    CpInput: require('../../cp-components-common/inputs/CpInput.vue')
  }
}
</script>

<style lang="scss">
</style>
