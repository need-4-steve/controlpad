<template>
    <div class="eInvoice-wrapper">
        <section class="eInvoice-header">
            <ul class="nav-list">
                <li><span :class="{ active: activeNav === 1 }">1</span><p>Order Details</p></li>
                <li><span :class="{ active: activeNav === 2 }">2</span><p>Shipping</p></li>
                <li><span :class="{ active: activeNav === 3 }">3</span><p>Payment</p></li>
                <li><span :class="{ active: activeNav === 4 }">4</span><p>Confirmation</p></li>
            </ul>
        </section>
        <p v-if="activeNav === -1"><strong class="errorText">{{ pageError }}</strong></p>
        <div class="step-one" v-show="activeNav === 1">
          <section class="eInvoice-table">
              <table class="cart-table">
                      <thead>
                          <th></th>
                          <th>Product</th>
                          <th>Variant</th>
                          <th>Option</th>
                          <th>Quantity</th>
                          <th>Price</th>
                      </thead>
                  <tbody class="cart-body">
                      <tr v-for="line in checkout.lines">
                          <td class="preview" data-label="image">
                              <span v-if="line.items[0].img_url">
                                  <img :src="line.items[0].img_url" class="preview">
                              </span>
                          </td>
                          <td data-label="Title">
                              <span>{{line.items[0].product_name}}</span>
                          </td>
                          <td data-label="variant">
                              <span>{{line.items[0].variant_name}}</span>
                          </td>
                          <td data-label="Size">
                              <label class="qty">Size:</label>
                              <span>{{line.items[0].option}}</span>
                          </td>
                          <td data-label="Quantity">
                          <label class="qty">Quantity:</label>
                          <span>{{line.quantity}}</span>
                          </td>
                          <td data-label="Price">
                              <span>{{line.price * line.quantity | currency}}</span>
                          </td>
                      </tr>
                  </tbody>
              </table>
          </section>
          <section class="eInvoice-subtotal">
                  <div class="line-wrapper">
                      <label>Subtotal</label>
                      <span>{{checkout.subtotal | currency}}</span>
                  </div>
                  <div class="line-wrapper" v-if="checkout.discount">
                    <label>Discount</label>
                    <span>{{-checkout.discount | currency}}</span>
                  </div>
                  <div class="line-wrapper">
                      <label>Shipping</label>
                      <span>{{checkout.shipping | currency}}</span>
                  </div>
                  <div class="line-wrapper" v-if="this.$getGlobal('tax_calculation').show">
                      <label>Sales Tax</label>
                      <span v-if="checkout.tax > 0">{{checkout.tax | currency}}</span>
                      <span v-else>TBD</span>
                  </div>
                  <div class="line-wrapper">
                      <label>Total Price</label>
                      <span>{{checkout.total | currency}}</span>
                  </div>
          </section>
          <section class="eInvoice-total">
                  <div class="btn-wrapper">
                      <button class="cp-button-standard" @click="showAddressForm()">Continue</button>
                  </div>
          </section>
        </div>

        <section class="step-two" v-show="activeNav === 2">
            <cp-shipping-info ref="shippingInfoForm"></cp-shipping-info>
            <div class="nav-btn">
                <button @click="activeNav = 1" class="cp-button-standard">Previous</button>
                <button @click="updateAddresses()" class="cp-button-standard">Continue</button>
            </div>
        </section>
        <section class="step-three" v-show="activeNav === 3">
            <cp-payment ref="paymentForm" :checkout="checkout"></cp-payment>
            <div class="apply-coupon-wrapper payment-page-wrapper" v-if="(checkout.inventory_user_pid == $getGlobal('company_pid').value) ? $getGlobal('corp_coupons').show : $getGlobal('reseller_coupons').show">
              <div v-if="checkout.couponable">
                <cp-input
                class=""
                type="text"
                placeholder="Do you have a coupon code?"
                v-model="couponCode"></cp-input>
                <button :disabled="processing" class="cp-button-standard" @click="applyCoupon()">Apply</button>
              </div>
              <div class="coupon-applied-section">
                <span v-if="checkout.discount">A discount of <i>{{checkout.discount | currency}}</i> was applied to your order.</span>
              </div>
            </div>
            <div class="nav-btn">
                <button class="btn" @click="activeNav = 2">Previous</button>
                <button class="cp-button-standard" @click="createOrder()" v-if="!processing">Submit Payment</button>
                <button class="cp-button-standard" v-if="processing">Processing..</button>
            </div>
            <div class="push-right">By clicking Submit Payment above, you agree to the following <a href="/return-policy" class="agree" target="_blank">terms</a></div>
        </section>
        <section class="step-four" v-show="activeNav === 4">
            <cp-confirmation :order="order"></cp-confirmation>
        </section>
    </div>
</template>
<script>
const Checkout = require('../../resources/CheckoutAPIv0.js')
const Orders = require('../../resources/OrdersAPIv0.js')
const moment = require('moment')

module.exports = {
  routing: [
    { name: 'public.CpEInvoice', path: '/orders/invoice/:invoice_id', meta: { noauth: true }, props: true }
  ],
  data () {
    return {
      token: '',
      pageError: 'Unexpected error',
      activeNav: 0,
      processing: false,
      couponCode: null,
      order: {},
      checkout: {}
    }
  },
  mounted () {
    this.token = this.$pathParameterName()
    this.load()
  },
  methods: {
    load () {
      Checkout.getInvoice(this.token)
        .then((response) => {
          if (response.error) {
            if (response.code == 404) {
              this.pageError = 'Invoice not found'
            } else {
              this.pageError = 'Failed to load invoice. Please try again later.'
            }
            this.activeNav = -1
          } else if (response.order_id) {
            this.loadOrder(response.order_id)
          } else {
            this.createCheckout()
          }
        })
    },
    loadOrder (orderId) {
      // For use with already paid invoices on load
      Orders.get({}, orderId)
        .then((response) => {
          if (response.error) {
            this.pageError = 'Failed to load paid invoice receipt. Please try again later.'
            this.activeNav = -1
          } else {
            this.order = response
            this.activeNav = 4
          }
        })
    },
    createCheckout () {
      Checkout.createFromInvoice({}, this.token)
        .then((response) => {
          if (response.error) {
            this.pageError = 'Failed to load invoice. Please try again later.'
            this.activeNav = -1
            return
          }

          this.checkout = response
          this.$refs.shippingInfoForm.setCustomer(this.checkout.buyer)
          this.$refs.shippingInfoForm.setShippingAddress(this.checkout.shipping_address)
          this.$refs.shippingInfoForm.setBillingAddress(this.checkout.billing_address)
          this.activeNav = 1
        })
    },
    showAddressForm () {
      this.activeNav = 2
    },
    updateAddresses () {
      if (!this.$refs.shippingInfoForm.validate()) {
        return
      }
      let addresses = JSON.parse(JSON.stringify(this.$refs.shippingInfoForm.getAddresses()))
      let customer = this.$refs.shippingInfoForm.getCustomer()
      addresses.billingAddress.name = customer.first_name + ' ' + customer.last_name
      addresses.shippingAddress.name = customer.first_name + ' ' + customer.last_name

      request = {
        billing_address: addresses.billingAddress,
        shipping_address: addresses.shippingAddress
      }
      Checkout.update(request, this.checkout.pid)
        .then((response) => {
          if(response.error) {
            this.$toast((response.message ? response.message : 'Unexpected error. Please try again later.'), { error: true })
            return
          }
          this.checkout = response
          this.activeNav = 3
        })
    },
    applyCoupon () {
      this.processing = true
      if (!this.couponCode) {
        this.$toast('No coupon was entered.', {error: true})
        this.processing = false
      } else {
        Checkout.update({coupon_code: this.couponCode}, this.checkout.pid)
          .then((response) => {
            this.processing = false
            if (response.error) {
              this.$toast('The provided coupon code could not be validated.', { error: true})
              return
            }
            this.checkout = response
          })
      }
    },
    createOrder () {
      this.processing = true

      request = {
        source: (this.checkout.type === 'rep-transfer' ? 'Rep Transfer - Invoice' : 'Custom Order - Invoice'),
        payment: {
          amount: this.checkout.total
        },
        partial_reserve: true
      }
      let addresses = JSON.parse(JSON.stringify(this.$refs.shippingInfoForm.getAddresses()))
      request.buyer = this.$refs.shippingInfoForm.getCustomer()
      request.buyer.shipping_address = addresses.shippingAddress
      request.buyer.billing_address = addresses.billingAddress
      if (this.checkout.total > 0) {
        if (!this.$refs.paymentForm.validate()) {
          this.processing = false
          return
        }
        request.payment.type = 'card'
        request.payment.card = this.$refs.paymentForm.getCard()
      } else {
        request.payment.type = 'cash'
        request.payment.cash_type = 'Zero'
      }
      Checkout.process(request, this.checkout.pid)
        .then((response) => {
          if (response.error) {
            let message = null
            if (response.code == 422) {
              if (response.message.result_code) {
                message = response.message.message
                switch (response.message.result_code) {
                  case 3:
                    this.pageError = message + "\nPlease try again later."
                    this.activeNav = -1
                    break;
                  case 4:
                    window.alert('Some products were not available. Checkout updated to have remaining products and price.')
                    this.checkout = response.message.checkout
                    break;
                  case 5:
                    // message should be set to the transaction result already
                    break;
                  case 6:
                    this.activeNav = 2
                    break;
                  case 8:
                    window.alert('Coupon already used. Checkout updated to remove coupon.')
                    this.checkout = response.message.checkout
                    break;
                  default:
                    // Unexpected code, ignore for now
                }
              }
            } else {
              message = response.message
            }
            if (!message) {
              message = 'Unexpected error please try again later'
            }
            this.$toast(message, { error: true })
            this.processing = false
            return
          }
          this.order = response.order
          this.activeNav = 4
        })
    }
  },
  components: {
    CpPayment: require('../payment/CpPayment.vue'),
    CpShippingInfo: require('../orders/CpShippingInfo.vue'),
    CpConfirmation: require('../orders/CpConfirmation.vue')
  }
}
</script>
<style lang="scss">
@import "resources/assets/sass/var.scss";
    .eInvoice-wrapper {
        position: relative;
        max-width: 768px;
        margin: 0 auto;
        .push-right {
            width: 100%;
            text-align: right;
        }
        .discount-wrapper {
                max-width: 50%;
                padding-top: 10px;
                padding-left: 5px;
        }
        .eInvoice-header {
            padding-top: 75px;
            text-align: center;
            .nav-list {
                padding-left: 0;
                list-style-type: none;
                display: flex;
                -webkit-display: flex;
                justify-content: space-between;
                -webkit-justify-content: space-between;
                padding: 0 10px 20px;
                border-bottom: solid 1px #eee;
            }
            li {
                padding: 5px 0;
            }
            span {
                display: block;
                background: $cp-mainLight;
                color: #fff;
                border-radius: 50%;
                width: 25px;
                height: 25px;
                line-height: 25px;
                margin: 5px auto;
                &.active {
                    background: $cp-main;
                }
            }
        }
    .cvv {
        letter-spacing: 2px;
            input {
                width: 49% !important;
                display: flex;
            }
        }
        .eInvoice-table {
            margin-top: 50px;
            padding: 10px;
        }
        .eInvoice-subtotal {
            padding: 10px;
            border-top: solid 1px #eee;
            border-bottom: solid 1px #eee;
        }
            .btn-wrapper {
                float: right;
            }
        .nav-btn {
            display: flex;
            -webkit-display: flex;
            justify-content: space-between;
            -webkit-justify-content: space-between;
            margin: 25px 0;
            padding: 0 10px;
            button {
                min-width: 100px;
            }
        }
        .input-wrapper {
            label {
                display: block;
                font-size: 14px;
                font-weight: 300;
                margin-bottom: 0;
                &.product-name {
                    display: inline-block;
                }
            }
            input {
                height: 100%;
                width: 100%;
                height: 30px;
                background: $cp-lighterGrey;
                border: none;
                text-indent: 10px;
                margin: 5px 0;
            }
            &.duo {
                display: flex;
                -webkit-display: flex;
                justify-content: space-between;
                -webkit-justify-content: space-between;
                .half {
                    width: 48%;
                }
            }
            .sub-wrapper {
                display: flex;
                -webkit-display: flex;
                justify-content: space-between;
                -webkit-justify-content: space-between;
                input {
                    width: 48%;
                }
            }
        }
        .eInvoice-footer {
            margin-top: 100px;
            .footer-icon {
                margin: 20px auto;
                width: 40px;
            }
            text-align: center;
            p {
                font-size: 14px;
                margin: 15px auto;
            }
        }
        label {
            font-weight: 300;
            &.total {
                font-weight: 500;
            }
            &.qty {
                margin: 0 5px;
                display: none;
            }
        }
        .select-wrapper {
            position: relative;
            select {
                height: 100%;
                width: 100%;
                height: 30px;
                background: $cp-lighterGrey;
                border: none;
                text-indent: 10px;
                margin: 5px 0;
                -webkit-appearance: none;
                -webkit-border-radius: 0;
            }
              &:after {
                  position: absolute;
                  right: 5px;
                  top: 13px;
                font-family: "Linearicons";
                content: "\e93a";
                font-size: 10px;
                pointer-events: none;
            }
        }
        .btn-wrapper {
            margin-top: 10px;
            text-align: center;
        }
        .btn {
            background: $cp-main;
            color: #fff;
            text-align: center;
            padding: 5px 10px;
            &.green {
                background: #0CA200;
            }
            &.inactive {
                background: $cp-main;
            }
        }
        .line-wrapper {
            display: flex;
            -webkit-display: flex;
            justify-content: space-between;
            -webkit-justify-content: space-between;
        }
        .cart-table {
            width: 100%;
            .preview {
                width: 50px;
            }
            .qty {
                max-width: 50px;
                text-align: center;
            }
        }
    }
@media (max-width: 700px) {
    .eInvoice-wrapper {
        .eInvoice-header {
            padding-top: 25px;
            .nav-list {
                flex-direction: column;
                -webkit-flex-direction: column;
                padding: 0;
            }
            li {
                border-bottom: solid 1px #eee;
            }
        }
    }
}
@media (max-width: 500px) {
    .eInvoice-wrapper {
        .eInvoice-table {
            margin-top: 0;
        }
        .select-wrapper {
            width: 65px;
            margin: 0 auto;
        }
        label {
            &.qty {
                display: initial;
            }
        }
        table {
            &.cart-table {
                thead {
                    display: none;
                }
                td {
                    display: block;
                    width: 100%;
                    text-align: center;
                    margin: 10px 0;
                    &.preview {
                        width: 100%;
                        img {
                            height: 300px;
                            width: auto;
                            margin: 0 auto;
                        }
                    }
                }
            }
        }
    }
}
</style>
