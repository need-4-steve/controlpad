<template lang="html">
  <div class="payment-form-simple">
    <!-- card -->
    <cp-input
      label="Name on Card"
      name="nameoncard"
      autocomplete="cc-name"
      type="text"
      v-model="card.name"
      :error="validationErrors['card.name']"
      :disabled="disabled">
    </cp-input>
    <cp-input
      label="Credit Card Number"
      name="ccnumber"
      autocomplete="cc-number"
      :error="validationErrors['card.number']"
      :type="(disabled ? 'text' : 'number')"
      placeholder=""
      v-model="card.number"
      :disabled="disabled">
    </cp-input>
  <div class="cp-expiration-dates">
    <cp-select
      label="Month"
      name="cc-exp-month"
      autocomplete="cc-exp-month"
      :options="[
      { name: '01', value: 1 },
      { name: '02', value: 2 },
      { name: '03', value: 3 },
      { name: '04', value: 4 },
      { name: '05', value: 5 },
      { name: '06', value: 6 },
      { name: '07', value: 7 },
      { name: '08', value: 8 },
      { name: '09', value: 9 },
      { name: '10', value: 10 },
      { name: '11', value: 11 },
      { name: '12', value: 12 }
      ]"
      :error="validationErrors['card.month']"
      v-model="card.month"
      :disabled="disabled">
    </cp-select>
    <cp-select
      label="Year"
      name="cc-exp-year"
      autocomplete="cc-exp-year"
      type="number"
      v-model="card.year"
      :options="years"
      placeholder=" "
      :error="validationErrors['card.year']"
      :key-value="{ name: 'name', value: 'name'}"
      :disabled="disabled">
    </cp-select>
  </div>
  <div class="cvv">
    <cp-input
      label="CVV"
      name="cvv2"
      autocomplete="cc-csc"
      type="text"
      v-model="card.code"
      :error="validationErrors['card.code']"
      :disabled="disabled">
    </cp-input>
    <img src="https://s3-us-west-2.amazonaws.com/controlpad/CreditCard-Icons.png" class="credit-cards">
  </div>
</div>
</template>

<script>
const moment = require('moment')

module.exports = {
  data () {
    return {
      card: {
        name: null,
        number: null,
        month: null,
        year: null
      },
      validationErrors: {},
      years: [[]]
    }
  },
  props: {
    disabled: {
      type: Boolean,
      default () {
        return false
      }
    }
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
    },
    getCard: function () {
      return this.card
    },
    setCard: function (card) {
      this.card = card
    },
    setErrors: function (errors) {
      this.validationErrors = errors
    },
    validate: function () {
      let today = moment()
      let year = parseInt(today.format('YYYY'))
      let month = parseInt(today.format('M'))
      let cardYear = parseInt(this.card.year)
      let cardMonth = parseInt(this.card.month)
      let errors = {}
      if (this.$isBlank(this.card.name)) {
        errors['card.name'] = ['Required']
      }
      if (!this.isCardNumberValid()) {
        errors['card.number'] = ['Invalid']
      }
      if (isNaN(cardMonth) || cardMonth < 1 || cardMonth > 12) {
        errors['card.month'] = ['Must be between 1 and 12']
      } else if (isNaN(cardYear) || cardYear < year || (cardYear === year && cardMonth < month)) {
        errors['card.month'] = ['Expired']
      }
      if (!this.card.code || this.card.code.length < 3 || this.card.code.length > 4) {
        errors['card.code'] = ['Invalid']
      }
      if (Object.keys(errors).length > 0) {
        this.validationErrors = errors
        return false
      } else {
        this.validationErrors = {}
      }
      return true
    },
    isCardNumberValid: function () {
      // check sum
      if (!this.card.number || this.card.number.indexOf(' ') > -1 || isNaN(this.card.number) ||
          this.card.number.length < 13 || this.card.number.length > 16) {
        return false;
      }
      let length = this.card.number.length
      let checkSum = parseInt(this.card.number.substring(length - 1, length))
      let sum = 0
      let isOdd = true
      for (let i = length - 2; i >= 0; --i) {
        if (isOdd) {
          let res = 2 * parseInt(this.card.number.substring(i, i + 1))
          sum += res > 9 ? res - 9 : res
        } else {
          sum += parseInt(this.card.number.substring(i, i + 1))
        }
        isOdd = !isOdd
      }
      return ((sum + checkSum) % 10) === 0
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
  .cp-expiration-dates {
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
