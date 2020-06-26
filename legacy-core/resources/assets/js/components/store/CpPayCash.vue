<template lang="html">
    <div class="cp-form-inverse">
        <label for="">Select a Cash type</label>
             <cp-select
             :options="[
             { name: 'Apple Pay', value: 'Apple Pay' },
             { name: 'Cash/Check', value: 'Cash/Check' },
             { name: 'Google Wallet', value: 'Google Wallet' },
             { name: 'PayPal', value: 'PayPal' },
             { name: 'Square', value: 'Square' },
             { name: 'Square Cash', value: 'Square Cash' },
             { name: 'Venmo', value: 'Venmo' },
             { name: 'Zelle', value: 'Zelle' },
             { name: 'Other', value: 'Other' }
             ]"
             @input="onSelect()"
             v-model="cashType"
             :error="validationErrors['cashType']"></cp-select>
        </div>
</template>

<script>
module.exports = {
  data: function () {
    return {
      cashType: '',
      validationErrors: {}
    }
  },
  methods: {
    getCashType () {
      return this.cashType
    },
    validate () {
      if (!this.cashType) {
        this.validationErrors = {cashType: ['required']}
        return false
      }
      this.validationErrors = {}
      return true
    },
    onSelect () {
      this.validationErrors = {}
    }
  },
  components: {
    CpSelect: require('../../cp-components-common/inputs/CpSelect.vue')
  }
}
</script>

<style lang="scss">
@import "resources/assets/sass/var.scss";

</style>
