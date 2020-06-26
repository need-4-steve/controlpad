<template lang="html">
  <div class="payment-form-simple">
    <!-- payment billing address -->
    <div class="payment-form-billing-address" v-if="!hideAddress">
      <cp-input
      label="Billing Address Line 1"
      :setDisabled="disabled"
      :error="validationErrors['addresses.billing.address_1']"
       type="address_1"
       v-model="paymentData.addresses.billing.address_1"
       required></cp-input>
      <cp-input
      label="Billing Address Line 2"
      :setDisabled="disabled"
      :error="validationErrors['addresses.billing.address_2']"
      type="address_2"
      v-model="paymentData.addresses.billing.address_2"></cp-input>
      <cp-input
      label="City"
      :setDisabled="disabled"
      :error="validationErrors['addresses.billing.city']"
      type="city"
      v-model="paymentData.addresses.billing.city"
      required></cp-input>
      <cp-select
        label="State"
        type="text"
        :options="states"
        :key-value="{ name: 'name' , value: 'value' }"
        :disabled="disabled"
        :error="validationErrors['addresses.billing.state']"
        v-model="paymentData.addresses.state"
        required></cp-select>
      <cp-input
      label="Zip Code"
      :setDisabled="disabled"
      :error="validationErrors['addresses.billing.zip']"
      type="zip"
      name="paymentData.addresses.billing.zip"
      v-model="paymentData.addresses.billing.zip"
      class="payment"
      required></cp-input>
    </div>
    <!-- card -->
    <cp-input
    label="Name on Card"
    :setDisabled="disabled"
    :error="validationErrors['payment.name']"
    type="text"
    v-model="paymentData.payment.name"></cp-input>
    <cp-input
    label="Credit Card Number"
    :setDisabled="disabled"
    :error="validationErrors['payment.card_number']"
    type="number"
    placeholder=""
    v-model="paymentData.payment.card_number"></cp-input>
  <div class="expiration-dates">
      <cp-select
      label="Month"
      :options="[
      { name: '01', value: '01' },
      { name: '02', value: '02' },
      { name: '03', value: '03' },
      { name: '04', value: '04' },
      { name: '05', value: '05' },
      { name: '06', value: '06' },
      { name: '07', value: '07' },
      { name: '08', value: '08' },
      { name: '09', value: '09' },
      { name: '10', value: '10' },
      { name: '11', value: '11' },
      { name: '12', value: '12' }
      ]"
      v-model="paymentData.payment.month"
      :disabled="disabled"
      :error="validationErrors['payment.month']"></cp-select>
      <cp-select
      label="Year"
      v-model="paymentData.payment.year"
      :disabled="disabled"
      :error="validationErrors['payment.year']"
      :options="years"
      placeholder=" "
      :key-value="{ name: 'name', value: 'name'}"></cp-select>
    </div>
    <div class="cvv">
    <cp-input
    label="CVV"
    type="text"
    v-model="paymentData.payment.security"
    :setDisabled="disabled"
    :error="validationErrors['payment.security']"></cp-input>
    <img src="https://s3-us-west-2.amazonaws.com/controlpad/CreditCard-Icons.png" class="credit-cards">
  </div>
</div>
</template>

<script>
const { states } = require('../../resources/states.js')

module.exports = {
  data () {
    return {
      states: states,
      years: [[]]
    }
  },
  props: {
    paymentData: {
      type: Object,
      required: false
    },
    validationErrors: {
      type: Object,
      required: false,
      default () {
        return {}
      }
    },
    hideAddress: {
      type: Boolean,
      required: false
    },
    disabled: {
      type: Boolean,
      default () {
        return false
      }
    }
    // validationErrors: {}
  },
  mounted () {
    this.getYears()
  },
  methods: {
    getYears: function () {
      var currentYear = new Date().getFullYear()
      for (var i = 0; i <= 10; i++) {
        this.years.push({name: currentYear + i})
      }
    }
  },
  components: {
    CpInput: require('../../cp-components-common/inputs/CpInput.vue'),
    CpSelect: require('../../cp-components-common/inputs/CpSelect.vue')
  }
}
</script>

<style lang="scss">
.payment-form-simple {
    .cvv {
      letter-spacing: 2px;
        input {
            width: 50% !important;
            display: flex;
        }
    }
  .cp-select-standard {
      min-width: 0px;
  }
  .select-standard-wrapper {
      width: 100% !important;
  }
  .expiration-dates {
      box-sizing: border-box;
      display: flex;
      :first-child {
          padding-right: 2px;
      }
      :last-child {
          padding-left: 2px;
      }
      & > div {
         flex: 1;
     }
  }
}
</style>
