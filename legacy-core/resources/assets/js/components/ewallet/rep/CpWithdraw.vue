<template>
  <div class="withdraw">
    <span>
      <button class="cp-button-standard" @click="checkSource(), getPaidModal = !getPaidModal">Get Paid</button>
    </span>
    <transition name='fade'>
      <section class="cp-modal-standard" v-if="getPaidModal === true">
        <div class="cp-modal-body">
          <div class="cp-modal-header">
            <h2>Account Withdraw</h2>
            <span @click="getPaidModal = !getPaidModal"><i class="mdi mdi-close"></i> </span>
          </div>
          <div v-show="step == 0">
            <div class="cp-modal-body">
              <div class="payment-information">
                <input type="radio" id='withdraw' value='Rep' v-model="source"></input>
                <label>Current Balance</label>
                {{ availableFunds | currency('floor') }}
              </div>
              <div class="payment-information">
                <input type="radio" id='withdraw' value='Company' v-model="source"></input>
                <label>Commissions Balance</label>
                {{ availableCommissions | currency('floor') }}
              </div>
            </div>
            <div class="modal-footer-single">
              <button type="button" class="cp-button-standard" @click="step = 1">Next</button>
            </div>
          </div>

          <div v-show="step == 1">
            <div class="cp-modal-body">
              <div class="payment-information">
                <label v-if="source == 'Rep'">
                  Available Funds {{ availableFunds | currency('floor') }}
                </label>
                <label v-if="source == 'Company'">
                  Available Funds {{ availableCommissions | currency('floor') }}
                </label>
              </div>
              <div class="payment-information">
                <label>Enter Amount</label>
                <span>$<input class='cp-input-standard amount-input' type="number" v-model="total" v-on:keyup="checkAmount"></input></span>
              </div>
            </div>
            <div class="modal-footer-single">
              <button v-if="total >= 5" type="button" class="cp-button-standard" @click="step = 2, title = 'Please Confirm'">Next</button>
              <button v-else type="button" class="cp-button-standard" disabled>Next</button>
            </div>
          </div>

          <div v-show="step == 2">
            <div class="cp-modal-body">
              <p>You're about to transfer {{ total | currency('floor') }} to your bank account.</p>
            </div>
            <div class="modal-footer-single">
              <button type="button" class="cp-button-standard" @click="withdraw">Confirm</button>
            </div>
          </div>

          <div v-show="step == 3" class="align-center">
            <div class="cp-modal-body">
              <p>{{ message }}</p>
            </div>
            <div class="modal-footer-single">
              <button class="cp-button-standard" @click="getPaidModal = !getPaidModal">OK</button>
            </div>
          </div>
        </div>
      </section>
    </transition>
  </div>
</template>

<script>
const EWallet = require('../../../resources/ewallet.js')

module.exports = {
  data () {
    return {
      total: '5.00',
      message: null,
      step: 0,
      title: 'Get Paid',
      getPaidModal: false,
      source: 'Rep'
    }
  },
  props: {
    callBack: {
      type: Function,
      required: true
    },
    availableFunds: {
      required: true
    },
    availableCommissions: {
      required: true
    },
    balanceWithdraw: {
      type: Boolean,
      required: true
    },
    commissionWithdraw: {
      type: Boolean,
      required: true
    }
  },
  computed: {},
  mounted () {
  },
  methods: {
    withdraw: function () {
      EWallet.withdraw({total: this.total, source: this.source})
      .then((response) => {
        this.response = response
        this.step = 3
        this.title = 'Payment Results'
        if (response.success) {
          this.callBack()
             this.message = "Success! $" + parseFloat(this.total).toFixed(2) + " is being transferred to your account on file. We will process this as soon as possible. Thank you!"
        } else {
          this.message = this.response.description
        }
      })
    },
    checkAmount: function () {
      if (this.total > this.availableFunds) {
        this.total = Math.floor(this.availableFunds * 100) / 100
      }
    },
    checkSource: function () {
      this.step = 1
      if (this.balanceWithdraw) {
        this.source = 'Rep'
      } else if (this.commissionWithdraw) {
        this.source = 'Company'
      }
      if (this.balanceWithdraw && this.commissionWithdraw) {
        this.step = 0
      }
      title = 'Get Paid'
    }
  },
  components: {
    CpTotalsBanner: require('../../reports/CpTotalsBanner.vue')
  }
}

</script>

<style lang="scss">
  .withdraw{
    .amount-input {
        max-width: 100px;
        text-align: left;
    }
    .left-algin {
      text-align: left;
    }
    .payment-information {
      padding-left: 15px;
      text-align:left;
    }
    .exit {
      color: white;
    }
    .cp-modal-header {
      h2 {
        margin: 0px;
      }
      display: flex;
      justify-content: space-between;
    }
    .modal-footer {
      display: flex;
      justify-content: space-between;
      margin: 5px;
    }
    .modal-footer-single {
      display: flex;
      justify-content: flex-end;
      margin: 5px;
    }
  }
</style>
