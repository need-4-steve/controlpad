<template>
    <div class="autoship-details-wrapper">
        <div>
        <h3>ID: {{subscription.id}}</h3>
        <table class="cp-table-standard">
            <thead>
                <tr>
                <th>Image</th>
                <th>Product</th>
                <th>Variant</th>
                <th>Option</th>
                <th>Quantity</th>
                <th>Price</th>
                </tr>
            </thead>
            <tbody v-for="(line, index) in subscription.lines" :key="index">
                <tr v-for="(item, index) in line.items" :key="index">
                    <td> <img :src="item.img_url" alt=""></td>
                    <td> {{item.product_name}}</td>
                    <td> {{item.variant_name}}</td>
                    <td> {{item.option}}</td>
                    <td> {{line.quantity}}</td>
                    <td> {{line.price | currency}}</td>
                </tr>
            </tbody>
        </table>
        <div class="subscription-price">
            <span>
                <table v-if="cardToken.card_type" class="cp-table-inverse">
                    <thead>
                        <th>Payment Method</th>
                        <th>Expiration</th>
                    </thead>
                    <tr>
                        <td>{{cardToken.card_type}} ending in {{cardToken.card_digits.slice(-4)}}</td>
                        <td>{{cardToken.expiration | expiration}}</td>
                    </tr>
                </table>
            <a v-else-if="Auth.hasAnyRole('Rep')" class="cp-button-standard" href="/my-settings"> Add Payment Method</a>
            <span v-else>Credit Card not on file</span>
            </span>
            <table class="cp-table-inverse autoship-totals">
                <tr><td>Subtotal: </td><td>{{subscription.subtotal | currency}}</td></tr>
                <tr><td>Discount: </td><td>{{subscription.discount | currency}} ({{subscription.percent_discount}}%)</td></tr>
                <tr><td>Tax:</td><td> TBD</td></tr>
                <tr><td>Shipping:</td><td> TBD</td></tr>
                <tr><td>Total:</td><td> {{(subscription.subtotal - subscription.discount) | currency}}</td></tr>
            </table>
        </div>
        <cp-confirm
            :message="'Are you sure you want to cancel this ' + $getGlobal('autoship_display_name').value +'?'"
            v-model="showConfirm"
            :show="showConfirm"
            :callback="disableSubscription"
            :config-options="{ buttonTextOne: 'Yes', buttonTextTwo: 'No'}"
            :params="{id:subscriptionId}"></cp-confirm>
        <div v-if="Auth.hasAnyRole('Rep')" class="align-center disable-button">
            <button v-if="!subscription.disabled_at" class="cp-button-standard" @click="confirmAndDisable(subscription.id)">Cancel {{ $getGlobal('autoship_display_name').value }}</button>
            <span v-else>Canceled on {{subscription.disabled_at | cpStandardDate }}</span>
        </div>
    </div>
    </div>
</template>

<script>
const Autoship = require('../../resources/AutoshipAPIv0.js')
const Auth = require('auth')

module.exports = {
  data: () => ({
    Auth: Auth,
    subscriptionId: null,
    showConfirm: false,
    user_id: null
  }),
  filters: {
    expiration (expiration) {
        expiration = expiration.split('')
        expiration.splice(2, 0, '/')
        expiration = expiration.join('')
        return expiration
    }
  },
  props: ['subscription', 'cardToken', 'callback'],
  mounted () {
  },
  methods: {
    confirmAndDisable (id) {
      this.subscriptionId = id
      this.showConfirm = true
    },
    disableSubscription () {
      Autoship.disableSubscription(this.subscription.id)
        .then((response) => {
            if (response.error) {
                this.$toast(response.message, {dismiss: false, error: true})
            } else {
                this.$toast('Subscription successfully canceled.', {dismiss: false})
                this.$emit('disabled')
            }
        })
    }
  }
}
</script>

<style lang="scss">
.autoship-details-wrapper{
    .subscription-price {
        display: flex;
        justify-content: space-between;
        align-items: flex-end;
        padding-bottom: 20px;
        span {
            display: flex;
            justify-content: space-between;
            margin: 4px;
            margin-right: 60px;
            h3 {
                margin: 0;
            }
        }
        .credit-card {
            margin-top: 35px;
            padding-left: 20px;
        }
        div {
            width: 230px;
        }
    }
    .cp-table-inverse {
        border-style: solid;
        border-width: 2px;
        th {
            background-color: $cp-grey;
        }
    }
    .autoship-totals {
        width: 200px
    }
    .disable-button {
        padding-bottom: 15px;
    }
    tbody {
        img {
            width: 70px;
        }
    }
}
</style>
