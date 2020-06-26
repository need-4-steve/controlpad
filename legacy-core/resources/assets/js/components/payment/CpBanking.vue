<template lang="html">
  <div class="banking-information-wrapper">
    <!-- BANKING BOX -->
    <div class="cp-box-standard">
      <div class="cp-box-heading">
        <h5 >Financial Information</h5>
        <h6 v-show="bank && !bank.validated" class="verification">PENDING VERIFICATION</h6>
        <h6 v-show="bank && bank.validated" class="verification">VERIFIED</h6>
      </div>
      <div class="cp-box-body">
        <div class="line-wrapper action-wrapper">
          <h5>Banking Information</h5>
          <button class="action-btn" @click="bankModal = true" v-show="!bank">Add Bank Information</button>
          <button class="action-btn" v-show="bank && !bank.validated" @click="verifyModal = true, scrollTop()">Verify Deposit</button>
          <button class="action-btn" @click="bankModal = true, bankUpdate = true, scrollTop()" v-show="bank && bank.number">Update</button>
        </div>
        <div v-if="bank">
          <div class="line-wrapper">
            <span>Bank Name</span>
            <p class="inactive">{{bank.bankName}}</p>
          </div>
          <div class="line-wrapper">
            <span>Routing Number</span>
            <p class="inactive">{{bank.routing}}</p>
          </div>
          <div  class="line-wrapper">
            <span>Account Type</span>
            <p class="inactive">{{bank.type}}</p>
          </div>
          <div class="line-wrapper">
            <span>Account Name</span>
            <p class="inactive">{{bank.name}}</p>
          </div>
          <div class="line-wrapper">
            <span>Account Number</span>
            <p class="inactive">{{bank.number}}</p>
          </div>
        </div>
      </div>
    </div>
    <!-- BANKING MODAL  -->
    <section class="cp-modal-standard" v-show="bankModal" @click="bankModal = false, newbankRequest.authorization = false">
        <div class="cp-modal-body" @click.stop.prevent>
          <div class="cp-modal-header">
            <h5 v-show="bank">Change Bank Information</h5>
            <h5 v-show="!bank">Add Bank Information</h5>
          </div>
          <div class="cp-form-standard">
              <cp-input
              label="Account Holder"
              type="text"
              :error="validationErrors['name']"
              v-model="newbankRequest.name"></cp-input>
              <cp-select
              label="Account Type"
              :error="validationErrors['type']"
               v-model="newbankRequest.type"
               :options="[
               {name: 'checking', value: 'checking'},
               {name: 'savings', value: 'savings'}]"></cp-select>
              <cp-input
              label="Bank Name"
              type="text"
              :error="validationErrors['name']"
              v-model="newbankRequest.bankName"></cp-input>
              <cp-input
              label="Routing Number"
              type="number"
              :error="validationErrors['name']"
              v-model="newbankRequest.routing"></cp-input>
              <cp-input
              label="Account Number"
              type="number"
              :error="validationErrors['name']"
              v-model="newbankRequest.number"></cp-input>
            </div>
            <div class="banking-image">
              <img src="https://s3-us-west-2.amazonaws.com/controlpad/routing_number.jpg">
            </div>
            <div class="agreement-wrapper">
              <div>{{ agreement.content }}</div>
            </div>
            <div class="agree-wrapper">
                <input id="checkbox" name="checkbox" type="checkbox" v-model="newbankRequest.authorization" @click="newbankRequest.authorization = true" v-if="!newbankRequest.authorization">
                <label for="bankAuthorization" v-if="!newbankRequest.authorization"><strong>I Accept</strong></label>
                <label for="bankAuthorization" v-if="newbankRequest.authorization"><strong>Accepted</strong></label>
            </div>
            <div class="cp-modal-controls">
                <button class="right cp-button-standard" @click="updateBank()">Update Account</button>
                <button class="left cp-button-standard" @click="bankModal = false, newbankRequest.authorization=false">Cancel</button>
            </div>
        </div>
    </section>
    <!-- VERIFY BANKING INFORMATION -->
    <section class="cp-modal-standard" v-show="verifyModal" @click="verifyModal = false, verificationRequest.amount1 = '', verificationRequest.amount2 = '' ">
        <div class="cp-modal-body" @click.stop.prevent>
          <div class="cp-modal-header">
            <h5>Verify Deposits</h5>
          </div>
            <div>
                <p>Two to three days after your banking information is saved, you will receive two small deposits in your bank account. To verify that you are the owner of this bank account, enter the amounts received.</p>
              </div>
              <div class="cp-form-standard">
                <cp-input label="First Deposit" :error="validationErrors['verificationRequest.amount1']" type="number" name="" v-model="verificationRequest.amount1" autofocus></cp-input>
                <cp-input label="Second Deposit" :error="validationErrors['verificationRequest.amount1']" type="number" name="" v-model="verificationRequest.amount2"></cp-input>
              </div>
              <div class="cp-modal-controls">
                <button class="left cp-button-standard" @click="verifyModal = false, verificationRequest.amount1 = '', verificationRequest.amount2 = '' ">Cancel</button>
                <button class="right cp-button-standard" @click="verifyDeposit">Verify Bank Account</button>
              </div>
        </div>
    </section>
  </div>
</template>

<script>
const Banking = require('../../resources/banking.js')
const Users = require('../../resources/users.js')
const Settings = require('../../resources/settings.js')

module.exports = {
  data () {
    return {
      verificationRequest: {
        amount1: '',
        amount2: ''
      },
      validationErrors: {},
      bankUpdate: false,
      bankModal: false,
      verifyModal: false,
      newbankRequest: {
        bankName: '',
        name: '',
        number: '',
        routing: '',
        type: '',
        validated: false,
        authorization: false
      },
      agreement: ''
    }
  },
  props: {
    bank: {
      default () {
        return null
      }
    },
    userId: {
      required: true
    }
  },
  mounted () {
    this.getBankingAuthorization()
  },
  methods: {
    verifyDeposit: function () {
      Banking.depositVerify(this.verificationRequest)
        .then((response) => {
          if (!response.error) {
            this.$toast('Account validated!', { error: false })
            this.bank.validated = true
            this.verifyModal = false
            this.getUser()
            this.verificationRequest = {
              amount1: '',
              amount2: ''
            }
          }
        })
    },
    addBank: function () {
      Banking.bankAdd()
        .then((response) => {
          if (response.error) {
            this.$toast(response.message, { error: true })
          }
        })
    },
    updateBank: function () {
      Banking.bankUpdate(this.newbankRequest)
        .then((response) => {
          if (response.error) {
            this.validationErrors = response.message
          } else {
            this.getUser()
            this.bankModal = false
            this.newbankRequest = {
              bankName: '',
              name: '',
              number: '',
              routing: '',
              type: '',
              validated: false,
              authorization: false
            }
          }
        })
    },
    getUser () {
      Users.userAccount(this.userId)
        .then((response) => {
          if (response.error) {

          } else {
            this.bank = response.bank
          }
        })
    },
    getBankingAuthorization () {
      Settings.getCustomPage('bank-account-authorization')
        .then((response) => {
          if (response.error) {

          } else {
            this.agreement = response
          }
        })
    },
    scrollTop () {
      window.scrollTo(0, 0)
    }
  },
  components: {
    CpInput: require('../../cp-components-common/inputs/CpInput.vue'),
    CpSelect: require('../../cp-components-common/inputs/CpSelect.vue')
  }
}
</script>

<style lang="scss">
.banking-information-wrapper {
    .banking-image {
      display:flex;
      justify-content:center;
      margin:5px;
    }
    .agreement-wrapper {
      margin: 5px;
    }
    .agree-wrapper{
      display:flex;
      justify-content: flex-end;
      margin: 5px;
      input[type=checkbox] {
        margin: 10px 0 0;
      }
    }
}
</style>
