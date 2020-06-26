<template lang="html">
<div class="splash-form-wrapper">
  <div class="splash-get-paid-form">
    <div class="registration-title">
      <h2>Get Paid</h2>
      <h4>We need some basic information to pay you</h4>
      <h4><strong>NOTE:</strong> This information must be entered as it appears on your tax return.</h4>
    </div>
    <form class="cp-form-registration" @submit.prevent>
        <div class="col">
          <h5>General Account Information</h5>
          <cp-select
            label="Select your business type"
            :options="[
              { name: 'Sole Proprietor', value: '0' },
              { name: 'Business', value: '1' },
              { name: 'Limited Liability Company', value: '2' },
              { name: 'Partnership', value: '3' },
              { name: 'Association', value: '4' },
              { name: 'Non-profit Organiztion', value: '5' },
              { name: 'Government Organization', value: '6' }
            ]"
            :error="validationErrors['type']"
            :value.sync="ppa.type"></cp-select>
              <!-- all others -->
              <cp-input v-if="ppa.type !== '0'"
                label="Business Name"
                type="text"
                :error="validationErrors['name']"
                :value.sync="ppa.name"></cp-input>
              <cp-input-mask v-if="ppa.type !== '0'"
                label="Business Phone"
                type="text"
                mask="###-###-####"
                :masked="true"
                :error="validationErrors['phone']"
                :value.sync="ppa.phone"></cp-input-mask>
              <cp-input v-if="ppa.type !== '0'"
                label="Date Established"
                type="date"
                :error="validationErrors['established']"
                :value.sync="ppa.established"></cp-input>
              <cp-input-mask
                v-if="ppa.type !== '0'"
                label="EIN"
                mask="##-#######"
                :masked="true"
                type="text"
                :error="validationErrors['ein']"
                :value.sync="ppa.ein"></cp-input-mask>
                <p v-if="ppa.type !== '0'"><i class="mdi mdi-help-circle" @click="eInInfo()"></i>What is an EIN</p>
                <cp-input
                label="DBA (Doing Business As)"
                type="text"
                :error="validationErrors['name']"
                :value.sync="ppa.dba"></cp-input>
              <h5>Business Address</h5>
              <address-form
              :address.sync="ppa.address"
              :validation-errors="validationErrors"
              :address-type="'address'"
              :hide-name-field="true"></address-form>

              <h5>Tell us where to send your money</h5>
              <cp-input
                label="Routing Number"
                type="text"
                :error="validationErrors['account.routing']"
                :value.sync="ppa.account.routing"></cp-input>
              <cp-input
                label="Account Number"
                type="text"
                :error="validationErrors['account.number']"
                :value.sync="ppa.account.number"></cp-input>
              <cp-select
                label="Account Type"
                :options="[
                  { name: 'Checking', value: 'checking' },
                  { name: 'Savings', value: 'savings' }
                ]"
                :error="validationErrors['account.type']"
                :value.sync="ppa.account.type"></cp-select>

        </div>
        <div class="col">
            <h5>Business Owner</h5>
            <cp-input
            label="First Name"
            type="text"
            :error="validationErrors['owner.last_name']"
            :value.sync="ppa.owner.first_name"></cp-input>
            <cp-input
            label="Last Name"
            type="text"
            :error="validationErrors['owner.first_name']"
            :value.sync="ppa.owner.last_name"></cp-input>
            <cp-input
            label="Date of Birth"
            type="date"
            :error="validationErrors['owner.dob']"
            :value.sync="ppa.owner.dob"></cp-input>
            <cp-input-mask
            label="Phone"
            type="text"
            mask="###-###-####"
            :masked="true"
            :error="validationErrors['owner.phone']"
            :value.sync="ppa.owner.phone"></cp-input-mask>
            <cp-input
            label="Email"
            type="text"
            :error="validationErrors['owner.email']"
            :value.sync="ppa.owner.email"></cp-input>
            <cp-input v-if="ppa.type !== '0'"
            label="Percentage of Ownership"
            type="number"
            placeholder="%"
            :error="validationErrors['ownership']"
            :value.sync="ppa.owner.ownership"></cp-input>
            <cp-input
            label="SSN"
            type="text"
            :error="validationErrors['owner.ssn']"
            :value.sync="ppa.owner.ssn"></cp-input>

            <div v-if="ppa.type !== '0'">
            <h5>Business Owner's Address</h5>
            <address-form :address.sync="ppa.owner.address" :validation-errors="validationErrors" :address-type="'owner.address'" :hide-name-field="true"></address-form>
            </div>
          </div>
    </form>
  </div>
</div>
</template>

<script>
// PPA stands for payment process account
module.exports = {
  data: function () {
    return {
      einInfo: false,
      accountInfo: false
    }
  },
  props: {
    userInfo: {
      required: true
    },
    validationErrors: {
      default () {
        return {
          user: this.userInfo
        }
      }
    },
    ppa: {   // PAYMENT PROCESS ACCOUNT
      type: Object,
      twoWay: true,
      default () {
        return {
          type: null,
          businessAddress: {},
          businessAccount: {}
        }
      }
    }
  },
  methods: {
    eInInfo: function () {
      this.$toast("The Employer Identification Number (EIN), also known as the Federal Employer Identification Number (FEIN). If you don't have one please select Sole Proprietor above.", {dismiss: false})
    }
  },
  components: {
    CpInput: require('../../cp-components-common/inputs/CpInput.vue'),
    CpInputMask: require('../../cp-components-common/inputs/CpInputMask.vue'),
    CpSelect: require('../../cp-components-common/inputs/CpSelect.vue'),
    AddressForm: require('../addresses/AddressForm.vue')
  }
}
</script>

<style lang="scss" scoped>

.splash-get-paid-form {
  .registration-title {
    text-align: center;
  }
}
@media (max-width: 768px) {
  .cp-select-standard select{
    width: 100% !important;
  }

  .cp-form-registration {
    flex-direction: column;
    .col {
      padding: 0px !important;
    }
  }

}

</style>
