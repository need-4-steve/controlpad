<template lang="html">
    <div class="order-detail-wrapper">
        <div class="order-detail-action-buttons">
            <button v-if="order.buyer_id == Auth.getAuthId() && order.inventory_received_at === null" class="cp-button-standard" type="button" name="button" @click="showConfirm = true">Receive Inventory</button>
            <button v-if="Auth.hasAnyRole('Admin', 'Superadmin') && !order.token" class="cp-button-standard" @click="printOrder('picklist-single')">Print Picklist</button>
            <button v-if="!order.token" class="cp-button-standard" @click="printOrder('order')">Print Order</button>
            <button v-if="order.token" class="cp-button-standard" @click="printOrder('invoice')">Print</button>
            <a v-if="$getGlobal('shipping_link').show && Auth.hasAnyRole('Rep') && order.buyer_id !== Auth.getAuthId()" v-bind:href="$getGlobal('shipping_link').value" target="_blank" ><button class="cp-button-standard">{{$getGlobal('shipping_link_text').value}}</button></a>
            <!-- reps should not be able to hold orders that they have purchased -->
            <span v-if="Auth.hasAnyRole('Admin', 'Superadmin') || order.store_owner_user_id === Auth.getAuthId() && order.type_id !== 9">
                <button class="cp-button-standard yellow" v-show="order.status === 'unfulfilled'" @click="order.status = 'hold', holdOrder()">Hold Order</button>
                <button class="cp-button-standard yellow" v-show="order.status === 'hold'" @click="order.status = 'unfulfilled', removeHold()">Remove Hold</button>
            </span>
        </div>
        <div class="order-detail">
            <div class="cp-box-standard">
                <div class="cp-box-heading">
                    Order Detail
                </div>
                <div class="cp-box-body">
                    <div v-if="order.token"><strong>Invoice ID: </strong>{{ order.uid }}</div>
                    <div v-else><strong>Receipt ID: </strong> {{ order.receipt_id }}</div>
                    <div><strong>Customer Name: </strong>{{ order.buyer_first_name }} {{ order.buyer_last_name }}</div>
                    <div><strong>Customer Email: </strong> {{ order.buyer_email }}</div>
                    <div v-if="order.seller_name"><strong>Purchased From: </strong> {{ order.seller_name}}</div>
                    <div><strong>Order Date: </strong>{{ order.created_at | cpStandardDate('time') }}</div>
                    <div><strong>Source of Sale: </strong>{{ order.source || 'Web' }}</div>
                    <template v-if="!order.token">
                      <div><strong >Payment Type: </strong>{{ formatPaymentType(order.payment_type) }}</div>
                      <div v-if="order.gateway_reference_id || order.payment_type === 'credit-card' || order.payment_type === 'card-token'"><strong >Gateway Transaction ID: </strong>{{ order.gateway_reference_id }}</div>
                      <div v-if="order.payment_type === 'cash'"><strong >Cash Type: </strong>{{ order.cash_type }}</div>
                    </template>
                    <div><strong>Subtotal Price: </strong>{{ order.subtotal_price | currency }}</div>
                    <div><strong>Shipping: </strong>{{ order.total_shipping | currency }}</div>
                    <div v-if="order.token"></div>
                    <div v-else><strong>Tax: </strong>{{ order.total_tax | currency }}</div>
                    <div v-if="order.total_discount"><strong>Discount: </strong>{{ order.total_discount | currency }}</div>
                    <div v-if="order.coupon"><strong>Coupon Code: </strong>{{ order.coupon.code }}</div>
                    <div v-if="order.token"></div>
                    <div v-else><strong>Total Price: </strong>{{ order.total_price | currency }}</div>
                    <div v-if="order.token" class="url-box">
                        <p>Share the E-Invoice URL by clicking the button below and pasting in a browser or email:</p>
                        <p><a class="url" :href="`http://${url + order.token}`" target="_blank">http://{{ url + order.truncated_token }}</a></p>
                        <button class="cp-button-standard copy-btn" type="button" name="button" :data-clipboard-text="url + order.token" @click="copyUrl()">Copy URL</button>
                    </div>
                </div>
            </div>
            <div class="cp-box-standard middle-box">
                <div class="cp-box-heading">
                    Shipping To
                </div>
                <div class="cp-box-body"  v-if="order.shipping_address !== null">
                    <div v-if='order.shipping_address.name'><strong>Name: </strong> {{ order.shipping_address.name}}</div>
                    <div v-else><strong>Name: </strong> {{ order.buyer_first_name }} {{ order.buyer_last_name }}</div>
                    <div><strong>Address: </strong>{{ order.shipping_address.line_1 }}</div>
                    <div v-if="order.shipping_address.line_2"><strong>Address 2: </strong>
                        {{ order.shipping_address.line_2 }}
                    </div>
                    <div><strong>City: </strong>{{ order.shipping_address.city }}</div>
                    <div><strong>State: </strong>{{ order.shipping_address.state }}</div>
                    <div><strong>Zip: </strong>{{ order.shipping_address.zip }}</div>
                    <div v-for="tracking in order.tracking"><strong>Tracking Number: </strong>
                      <a :href="tracking.url" target="_blank">{{ tracking.number }}</a>
                      <br />
                      <strong v-if="tracking.shipped_at">Shipped At: </strong> {{ tracking.shipped_at | cpStandardDate('time') }}
                    </div>
                </div>
            </div>
            <div class="cp-box-standard">
                <div class="cp-box-heading">
                    Billing To
                </div>
                <div class="cp-box-body"  v-if="order.billing_address !== null">
                    <div v-if='order.billing_address.name'><strong>Name: </strong> {{ order.billing_address.name }}</div>
                    <div v-else><strong>Name: </strong> {{ order.buyer_first_name }} {{ order.buyer_last_name }}</div>
                    <div><strong>Address: </strong>{{ order.billing_address.line_1 }}</div>
                    <div v-if="order.billing_address.line_2"><strong>Address 2: </strong>
                        {{ order.billing_address.line_2 }}
                    </div>
                    <div><strong>City: </strong>{{ order.billing_address.city }}</div>
                    <div><strong>State: </strong>{{ order.billing_address.state }}</div>
                    <div><strong>Zip: </strong>{{ order.billing_address.zip }}</div>
                </div>
            </div>
        </div>
        <cp-confirm
        :message="'You are about to transfer items from this order into your available inventory. This action cannot be undone. Would you like to continue?'"
        v-model="showConfirm"
        :show="showConfirm"
        :callback="confrimInventory"
        :params="{receipt_id: order.receipt_id}"></cp-confirm>
    </div>
</template>

<script>
const Orders = require('../../resources/orders.js')
const Clipboard = require('clipboard')
const Auth = require('auth')
const CpOrdersFile = require('../../libraries/CpOrdersFile.js')

module.exports = {
  data: function () {
    return {
      downloadTitle: null,
      downloadType: null,
      downloadId: null,
      Auth: Auth,
      url: window.location.hostname + '/orders/invoice/',
      status: '',
      orders: [],
      showConfirm: false,
    }
  },
  props: {
    order: {
      type: Object,
      required: true
    },
    // hideHold: {
    //   type: Boolean,
    //   required: true
    // }
  },
  methods: {
    confrimInventory: function (params) {
      Orders.transferInventory(params.receipt_id)
      .then((response) => {
        if (response.error) {
          this.$toast(response.message, {error: true})
        } else {
          this.showConfirm = false
          this.order.inventory_received_at = response
          this.$toast('Inventory has been received.')
        }
      })
    },
    copyUrl: function () {
      var thisClipboard = this
      var clipboard = new Clipboard('.cp-button-standard')
      clipboard.on('success', (e) => {
        thisClipboard.$toast('URL Copied to Clipboard!', {success: true, dismiss: false})
      })
      clipboard.on('error', (e) => {
        thisClipboard.$toast('URL did not copy to clipboard!', {error: true, dismiss: false})
      })
    },
    printOrder (type) {
      var orderId
      if (type === 'invoice') {
        orderId = this.order.uid
      } else {
        orderId = this.order.id
      }
      new CpOrdersFile(null, ['pdf'], type, {orderId: orderId}).run()
    },
    formatPaymentType(paymentType) {
      switch(paymentType) {
        case 'cash':
          return 'Cash'
        case 'e-wallet':
          return 'eWallet'
        case 'credit-card':
        case 'card-token':
          return 'Credit Card'
        default:
          return 'Unknown'
      }
    },
    holdOrder: function () {
      this.holdObject = {
        status: this.order.status,
        orders: [this.order.receipt_id]
      }
      Orders.updateStatus(this.holdObject)
      .then((response) => {
        if (response.error) {
          this.$toast(response.message, {error: true})
        } else {
          this.$toast('Successfully placed order on hold')
        }
      })
    },
    removeHold: function () {
      this.holdObject = {
        status: this.order.status,
        orders: [this.order.receipt_id]
      }
      Orders.updateStatus(this.holdObject)
        .then((response) => {
          if (response.error) {
            this.$toast(response.message, {error: true})
          } else {
            this.$toast('Successfully removed hold')
          }
        })
    }
  },
  components: {}
}
</script>

<style lang="scss">
    @import "resources/assets/sass/var.scss";
        .order-detail-wrapper {
          .order-detail-action-buttons {
              margin-bottom: 10px;
          }
            .order-detail {
                display: flex;
                font-size: 0;
                border: 1px solid $cp-lightGrey;
                margin-bottom: 15px;
                .cp-box-standard {
                    flex: 1;
                    font-size: 15px;
                    margin: 0;
                    .cp-box-body {
                        border: none;
                        div {
                            padding: 5px;
                        }
                    }
                    &.middle-box {
                        flex: 1;
                        border-right: 1px solid $cp-lightGrey;
                        border-left: 1px solid $cp-lightGrey;
                    }
                }
                strong {
                    font-weight: 400;
                }
            }
            .url {
                color: #0000EE;
                text-decoration: underline;
                font-size: 12px;
            }
            .url:visited {
                color: #551A8B;
                text-decoration: underline;
                font-size: 12px;
            }
            .url:hover {
                color: #0000EE;
                text-decoration: underline;
                font-size: 12px;
            }
            .url-box {
                border-top: 1px solid black;
            } & p {
                font-size: 14px;
            }
            .copy-btn {
                font-size: 12px;
                padding: 5px;
            }
        }
        @media (max-width: 768px) {
          .order-detail-wrapper {
            .order-detail {
                display: block !important;
            }
          }
        }
</style>
