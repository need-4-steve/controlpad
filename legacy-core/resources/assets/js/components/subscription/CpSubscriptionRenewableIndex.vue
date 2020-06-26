<template lang="html">
  <div class="subscription-index-wrapper">
    <div>
        <div class="cp-modal-body">
          <div class="line-wrapper">
            <span class="bold">Subscription Price: </span>
              <p>$<input class="price-input" type="number" placeholder="Amount" v-model="renewAmount.price" required @input="updateSubscriptionInfo(renewAmount.price, renewAmount.date); calculating_price = true"></p>
          </div>
          <div class="line-wrapper" v-if="renewAmount.quantity > 1">
            <span class="bold">Count: </span>
              <p>{{renewAmount.quantity}}</p>
          </div>
          <div class="line-wrapper">
            <span class="bold">Subscription Tax</span>
              <p>{{renewAmount.tax | currency}}</p>
          </div>
          <div class="line-wrapper">
            <span class="bold">Total Subscription Charges</span>
              <p>{{renewAmount.total | currency}}</p>
          </div>
          <div class="line-wrapper">
            <span class="bold">Next Payment Due Date</span>
              <p><input  type="date" v-model="renewAmount.date" @change="updateSubscriptionInfo(renewAmount.price, renewAmount.date)"></p>
          </div>
          <div class="line-wrapper extra-padding">Your are charging {{user.first_name}} {{user.last_name}} {{renewAmount.total| currency}} and subscription will end on {{renewAmount.date | cpStandardDate(0, 0)}}.</span>
          </div>
        <div class="flex-end">
            <button class="cp-button-standard cancel" @click="$emit('close', false)">Cancel</button>
            <button v-if="!calculating_price" class="cp-button-standard" @click="paySubscription(renewAmount.subtotal_price, renewAmount.date)" :disabled="disableUpdate">Update Subscription</button>
            <button v-if="calculating_price" class="cp-button-standard"  disabled="true">Calculating Price...</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
const moment = require('moment')
const Subscription = require('../../resources/subscription.js')
const _ = require('lodash')

module.exports = {
  data: function () {
    return {
      calculating_price: false,
      moment: moment,
      renewAmount: {
        amount: '',
        date: moment().format('yyyy-MM-dd'),
        tax: '',
        tax_invoice_pid: null,
        months: '',
        total: ''
      },
      disableUpdate: false
    }
  },
  mounted: function () {
    this.amountOwed()
  },
  props: {
    user: {
      type: Object,
      required: true,
      default: {
        id: '',
        price: '',
        tax_invoice_pid: null
      }
    }
  },
  methods: {
    updateSubscriptionInfo: _.debounce(function (price, endsAt) {
      this.user.price = price
      this.user.ends_at = endsAt
      this.user.duration = this.renewAmount.duration
      this.user.quantity = this.renewAmount.quantity
      Subscription.getTaxAdmin(this.user)
        .then((response) => {
          this.renewAmount.tax = response.tax
          this.renewAmount.subtotal_price = parseFloat(price) * this.renewAmount.quantity
          this.renewAmount.total = this.renewAmount.subtotal_price + response.tax
          this.renewAmount.tax_invoice_pid = response.pid
          this.calculating_price = false
        })
    }, 1000),
    paySubscription: function (subtotal_price, endsAt) {
      this.disableUpdate = true
      this.user.subtotal_price = subtotal_price
      this.user.total_tax = this.renewAmount.tax
      this.user.tax_invoice_pid = this.renewAmount.tax_invoice_pid
      this.user.ends_at = endsAt
      Subscription.paySubcription(this.user)
        .then((response) => {
          if (response.error) {
            this.disableUpdate = false
            return this.$toast(response.message.message, { error: true, dismiss: false })
          }
          this.$emit('close', false)
          this.$toast('Successfully updated.', {error: false, dismiss: true})
          this.disableUpdate = false
        })
    },
    amountOwed: function () {
      Subscription.subscriptionRenewAmount(this.user.user_id)
        .then((response) => {
          if (!response.error) {
            this.renewAmount.date = response.data.expires_at
            this.renewAmount.quantity = response.data.quantity
            this.renewAmount.price = parseFloat(response.data.price).toFixed(2)
            this.renewAmount.subtotal_price = response.data.subtotal_price
            this.renewAmount.tax = response.data.total_tax
            this.renewAmount.tax_invoice_pid = response.data.tax_invoice_pid
            this.renewAmount.duration = response.data.duration
            this.renewAmount.months = response.data.months
            this.renewAmount.total = this.renewAmount.subtotal_price + response.data.total_tax
          }
        })
    }
  }
}
</script>

<style lang="sass">
  @import "resources/assets/sass/var.scss";
  .subscription-index-wrapper {
    .cp-button-standard {
      padding: 5px 10px;
      margin-right: 8px;
      &:hover {
          color: white;
      }
      &:visited {
          color: white;
      }
      &:focus {
          text-decoration: none;
      }
    }
      .extra-padding{
        padding-top: 32px !important;
      }
      .flex-end {
        padding-top: 20px;
      }
      .price-input{
        width: 64px;
      }
      .line-wrapper {
           display: flex;
           -webkit-display: flex;
           justify-content: space-between;
           -webkit-justify-content: space-between;
           padding: 5px 10px;
           border-bottom: solid 1px $cp-lighterGrey;
           width: 100%;
           min-height: 40px;
           span {
               padding: 5px;
           }
           &.sub {
               display: block;
           }
           &.credit {
               justify-content: center;
               -webkit-justify-content: center;
               img {
                   width: 75%;
               }
           }
           &.cvv {
               justify-content: flex-start;
               -webkit-justify-content: flex-start;
           }
           &.action-wrapper {
               justify-content: flex-start;
               -webkit-justify-content: flex-start;
               button {
                   margin-left: 20px;
               }
           }
           .toggle {
               display: inline-block;
               width: 55px;
               input {
                   padding: 0;
               }
           }
           .sub-line-wrapper {
               display: flex;
               -webkit-display: flex;
               justify-content: space-between;
               -webkit-justify-content: space-between;
           }
           p {
               margin: 0;
               width: 35%;
               text-align: left;
               &.inactive {
                   color: rgba(0,0,0,.4);
               }
           }
           .input {
               background: #fff;
               width: 100%;
               margin: 5px 2px;
               &.edit {
                   border: solid 1px $cp-lighterGrey;
                   background: $cp-lighterGrey;
               }
           }
           .input-wrapper {
               width: 100%;
               max-width: 70%;
           }
  }
    .bold {
      font-weight: bold;
    }
  }
</style>
