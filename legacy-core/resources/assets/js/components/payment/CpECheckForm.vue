<template lang="html">
  <div>
    <cp-select
      label="Account Type"
      v-model="account.type"
      :error="validationErrors['type']"
      :options="[
        {name: 'Checking', value: 'checking'},
        {name: 'Savings', value: 'savings'}
      ]"
      :disabled="disabled">
    </cp-select>
    <cp-input
      label="Bank Routing Number"
      type="text"
      v-model="account.routing"
      :error="validationErrors['routing']"
      :disabled="disabled">
    </cp-input>
    <cp-input
      label="Account Number"
      :error="validationErrors['number']"
      type="text"
      placeholder=""
      v-model="account.number"
      :disabled="disabled">
    </cp-input>
    <cp-input
      label="Name on Account"
      type="text"
      v-model="account.name"
      :error="validationErrors['name']"
      :disabled="disabled">
    </cp-input>
    <cp-input
      label="Name of Bank"
      type="text"
      v-model="account.bank_name"
      :error="validationErrors['bank_name']"
      :disabled="disabled">
    </cp-input>
    <cp-input
      label="Email"
      type="email"
      v-model="account.email"
      :error="validationErrors['email']"
      :disabled="disabled">
    </cp-input>
    <cp-input
      label="Phone Number"
      type="text"
      v-model="account.phone_number"
      :error="validationErrors['phone_number']"
      :disabled="disabled">
    </cp-input>
  </div>
</div>
</template>

<script>
const Payman = require('../../resources/PaymanAPI.js')

module.exports = {
  data () {
    return {
      account: {
        name: '',
        routing: '',
        number: '',
        bank_name: '',
        email: '',
        phone_number: ''
      },
      validationErrors: {}
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
  methods: {
    getAccount: function () {
      return this.account
    },
    setAccount: function (account) {
      this.account = account
    },
    setErrors: function (errors) {
      this.validationErrors = errors
    },
    validate: function () {
      // Make sure fields are not blank
      if (this.$isBlank(this.account.routing)) {
        errors['account.routing'] = ['Required']
      } else if (!Payman.isRoutingNumberValid(this.account.routing)) {
        errors['account.routing'] = ['Invalid']
      }
      if (this.$isBlank(this.account.name)) {
        errors['account.name'] = ['Required']
      }
      if (this.$isBlank(this.account.bank_name)) {
        errors['account.bank_name'] = ['Required']
      }
      if (this.$isBlank(this.account.number)) {
        errors['account.number'] = ['Required']
      }
      if (this.$isBlank(this.account.email)) {
        errors['account.email'] = ['Required']
      } else {
        // TODO Validate email?
      }
      if (this.$isBlank(this.account.phone_number)) {
        errors['account.phone_number'] = ['Required']
      } else {
        // TODO Validate phone_number
      }

      if (Object.keys(errors).length > 0) {
        this.validationErrors = errors
        return false
      } else {
        this.validationErrors = {}
      }
      return true
    }
  },
  components: {
    CpInput: require('../../cp-components-common/inputs/CpInput.vue'),
  }
}
</script>
