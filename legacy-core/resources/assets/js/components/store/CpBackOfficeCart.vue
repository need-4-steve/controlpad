<template lang="html">
    <p v-if="pageError"><strong class="errorText">{{ pageError }}</strong></p>
    <div class="" v-else-if="cart">
        <div class="cart-wrapper">
          <button class="cp-button-standard" v-if="proccessOption === false" @click="proccessOption = !proccessOption"><i class="mdi mdi-cart"></i></button>
           </div>
      <transition name="fade">
        <section>
            <div class="back-office-cart-wrapper" v-if="proccessOption">
                <cp-cart-items
                :cart="cart"
                :validation-errors="validationErrors"
                @delete-line-item="deleteLineItem"
                @update-line-item="updateQuantity"
                ></cp-cart-items>
            </div>
            <div class="checkout-options-wrapper" v-if="proccessOption">
              <h4 style="margin-left:5px;">How would you like to process your cart?</h4>
                  <div class="details-section">
                      <div class="payment-totals-section">
                          <div class="details-titles-section">
                            <div>Item Count: </div>
                            <div>Subtotal: </div>
                            <div v-if="editable">Discount: </div>
                            <div v-if="editable">Shipping: </div>
                            <div v-if="discount > 0 || shipping > 0">Cart Total: </div>
                            <div v-if="Auth.hasAnyRole('Admin', 'Superadmin')">Tax Exempt: </div>
                          </div>
                          <div class="details-totals-section">
                            <div>{{ itemCount }}</div>
                            <div>{{ subtotal | currency }}</div>
                            <div v-if="editable">-
                            <cp-input type="number" v-model="discount" :blur-method="checkDiscount"></cp-input></div>
                            <div v-if="editable"><cp-input type="number" v-model="shipping"></cp-input></div>
                            <div v-if="discount > 0 || shipping > 0">{{ total | currency }}</div>
                            <div v-if="Auth.hasAnyRole('Admin', 'Superadmin')"><input type="checkbox" v-model="taxExempt"></div>
                          </div>
                      </div>
                  </div>
                  <div v-show="cart.type === 'wholesale' && $getGlobal('autoship_enabled').show" class="autoship-checkbox" @click="autoshipCheckout = !autoshipCheckout"> Make Order as {{$getGlobal('autoship_display_name').value}} <input type="checkbox" v-model="autoshipCheckout"></input></div>
                <div class="wholesale-option-wrapper" v-show="cart.type === 'wholesale'">
                    <button class="cp-button-standard" @click="navigate('inventory-purchase')">Continue Shopping</button>
                    <button class="cp-button-standard" v-if="!autoshipCheckout" :disabled="cart.lines.length < 1 || creatingCheckout || loading > 0" @click="createCheckout()">Complete Order</button>
                    <button class="cp-button-standard" v-else :disabled="cart.lines.length < 1" @click="checkoutAutoship()">Complete Order</button>
                </div>
                <!-- THESE buttons set orderType on the front end -->
                <div class="retail-option-wrapper" v-show="cart.type != 'wholesale' && cart.type != 'custom-personal'">
                    <button class="cp-button-standard" @click="navigate('custom-order')">Continue Shopping</button>
                    <button class="cp-button-standard" @click="displayProcess('cp-send-order')" v-if="cart.type !== 'custom-personal'">Send Order</button>
                    <button class="cp-button-standard" @click="displayProcess('cp-credit-card')">Pay with Credit Card</button>
                    <button v-if="['custom-retail', 'custom-corp', 'rep-transfer'].includes(cart.type)" class="cp-button-standard" @click="displayProcess('cp-cash')">Cash</button>
                </div>
                <div class="wholesale-option-wrapper" v-show="cart.type === 'custom-personal'">
                    <button class="cp-button-standard" @click="navigate('inventory-personal-use')">Continue Shopping</button>
                    <button class="cp-button-standard" :disabled="creatingCheckout || loading > 0" @click="createCheckout()">Complete Order</button>
                </div>
            </div>
            <div class="back-office-cart-wrapper" v-if="errorCart">
              <div style="margin-bottom: 16px;" class="errorText">
                  ALERT: The following items were removed from cart due to a lack of availability.<br>
                  To continue checkout of the above items, click the 'Complete Order' button.
              </div>
              <cp-cart-error-items
              :cart="errorCart"
              ></cp-cart-error-items>
            </div>
            <div class="align-center">
                <img class="loading" :src="$getGlobal('loading_icon').value" v-if="loading > 0">
            </div>
        </section>
      </transition>

        <transition name="fade">
            <section>
                <div class="" v-if="!proccessOption">
                    <cp-custom-order-processing
                      :orderType="dynamicOrderType"
                      :discount="discount"
                      :shipping="shipping"
                      :taxExempt="taxExempt"
                      @cart="clearCart"
                      :cart="cart"></cp-custom-order-processing>
                </div>
            </section>
        </transition>
    </div>
</template>

<script>
const Auth = require('auth')
const Checkout = require('../../resources/CheckoutAPIv0.js')
const CartUtility = require('../../libraries/CartUtility.js')
const Users = require('../../resources/UserApiv0.js')
const _ = require('lodash')

module.exports = {
  name: 'CpCart',
  routing: [
    {
      name: 'site.CpWholesaleCart',
      path: 'carts/:cartPid',
      meta: {
        title: 'Cart'
      },
      props: true
    }
  ],
  data () {
    return {
      Auth: Auth,
      autoshipCheckout: false,
      wholesaleCart: false,
      customerOrderCart: false,
      proccessOption: true,
      dynamicOrderType: '',
      loading: 0,
      bundles: [],
      cart: null,
      buyer: null,
      pageError: null,
      validationErrors: {},
      paymentOption: null,
      paymentComponent: '',
      paymentOptionProps: {},
      editable: false,
      taxExempt: false,
      discount: 0.00,
      shipping: 0.00,
      creatingCheckout: false,
      errorCart: null
    }
  },
  props: {
    cartPid: {
      type: String,
      required: true
    }
  },
  mounted () {
    this.getCart()
    this.autoshipCheckout = this.$getGlobal('autoship_default_purchase').value
  },
  methods: {
    navigate (navTo) {
      switch (navTo) {
        case 'custom-order':
          this.$router.push('/orders/custom')
          break
        case 'inventory-purchase':
          this.$router.push('/inventory/purchase')
          break
        case 'inventory-personal-use':
          this.$router.push('/inventory/personal')
          break
      }
    },
    pullBuyer () {
      if (this.cart.buyer_pid) {
        return Users.get(this.cart.buyer_pid, {addresses: true})
          .then((response) => {
            if (!response.error) {
              this.buyer = response
            }
        })
      } else {
        return Promise.resolve(null)
      }
    },
    createCheckout () {
      this.creatingCheckout = true
      this.pullBuyer().then((response) => {
        let checkoutRequest = {
          reserve_inv: true
        }
        if (this.buyer) {
          if (this.buyer.billing_address && this.buyer.billing_address.zip && this.buyer.billing_address.city &&
              this.buyer.billing_address.state && this.buyer.billing_address.line_1) {
            checkoutRequest.billing_address = this.buyer.billing_address
          }
          if (this.buyer.shipping_address && this.buyer.shipping_address.zip && this.buyer.shipping_address.city &&
              this.buyer.shipping_address.state && this.buyer.billing_address.line_1) {
            checkoutRequest.shipping_address = this.buyer.shipping_address
          }
        }
        Checkout.createFromCart(checkoutRequest, this.cartPid)
          .then((response) => {
            if (response.error) {
              if (response.code == 422 && response.message.result_code && response.message.result_code === 4) {
                this.$toast('Some inventory was missing, updating cart', {error: true});
                this.updateForMissingInventory(response.message.reservationResponse.errors)
              } else {
                this.$toast((response.message ? response.message : 'Unexpected error'), { error: true })
              }
            } else {
              this.$router.push({ name: 'CpCheckout', params: { checkoutPid: response.pid, checkoutProp: response, buyerProp: this.buyer}})
            }
            this.creatingCheckout = false
          })
        })
    },
    checkoutAutoship () {
      if (this.cart.lines.length === 0) {
        this.$toast('Cart Empty', { error: true, dismiss: false})
        return
      }
      this.$router.push({ name: 'CpAutoshipCheckout', params: {cart: this.cart}})
    },
    isEditable () {
      if (['custom-retail', 'custom-corp', 'rep-transfer'].includes(this.cart.type)) {
        this.editable = true
      }
    },
    displayProcess (paymentComponent) {
      if (this.cart.lines.length < 1) {
        this.$toast('You must select at least 1 product to make an order.', { error: true })
      } else if (paymentComponent === 'cp-personal-use' && this.taxExempt === true) {
        this.$toast('Personal Use cannot be tax exempt.', { error: true })
      } else {
        this.proccessOption = !this.proccessOption
        this.dynamicOrderType = paymentComponent
      }
    },
    checkDiscount () {
      if (this.discount === '') {
        this.discount = 0
      }
      if (this.discount < 0) {
        this.discount = Math.abs(this.discount)
      }
      if (this.discount > this.subtotal) {
        this.discount = this.subtotal.toFixed(2)
      }
    },
    getCart () {
      Checkout.getCart({expands: ['lines']}, this.cartPid)
        .then(response => {
          if (response.error) {
            if (response.code === 404) {
              this.pageError = 'Cart missing'
            } else {
              this.pageError = (response.message ? response.message : 'Unexpected Error')
            }
            return
          }
          this.cart = response
          this.isEditable()
        })
    },
    clearCart () {
      this.cart = {
        lines: [],
        type: null
      }
      this.shipping = 0.00,
      this.discount = 0.00
    },
    deleteLineItem (line) {
      this.loading++
      let product = line.items[0].product_name
      let lineIndex = this.cart.lines.indexOf(line);
      if(lineIndex !== -1) {
        this.cart.lines.splice(lineIndex, 1);
      }
      Checkout.deleteCartLine(line.pid)
        .then((response) => {
          this.loading--
          if (response.error) {
            this.$toast(response.message, { error: true })
            this.cart.lines.push(line)
            return response
          } else {
            this.$toast(`Deleted ${product}`)
          }
        })
    },
    updateLineItem (line) {
      this.loading++
      let product = line.items[0].product_name
      if (line.quantity <= 0) {
      line.quantity = 1
      }
      let qty = line.quantity
      let size = line.items[0].option
      Checkout.patchCartLine({quantity: qty}, line.pid)
        .then((response) => {
          this.loading--
          if (!response.error) {
            this.$toast(`Changed quantity of the ${product} ${size} to ${qty}.`)
            return response
          } else {
            this.$toast(response.message, { error: true })
          }
        })
    },
    updateForMissingInventory (errors) {
      this.errorCart = {lines: []}
      let cartLineMap = {}
      for (var i = 0; i < this.cart.lines.length; i++) {
        cartLineMap[this.cart.lines[i].pid] = i
      }
      let cartline = null
      let cartlineCopy = null
      let available = 0
      let cartUpdates = []
      let cartDeletes = []
      for (var i = 0; i < errors.length; i++) {
        if (errors[i].transaction.transaction_id === 'items') {
          for(var a = 0; a < errors[i].transaction.inventories.length; a++) {
            available = errors[i].transaction.inventories[a].available
            cartline = this.cart.lines[cartLineMap[errors[i].transaction.inventories[a].transaction_id]]
            // Find lines that are partially available
            if (cartline.quantity > available) {
              // copy cartline if partial|no availability
              cartlineCopy = JSON.parse(JSON.stringify(cartline))
              cartlineCopy.quantity = cartlineCopy.quantity - available
              cartline.quantity = available
              if (available > 0) {
                cartUpdates.push(cartline)
              } else {
                cartDeletes.push(cartline)
              }
              this.errorCart.lines.push(cartlineCopy)
            }
          }
        } else {
          // Bundle line
          cartline = this.cart.lines[cartLineMap[errors[i].transaction.transaction_id]]
          // Calculate the maximum bundle count left and create an error line for the remainder
          available = errors[i].transaction.inventories
          .map(el => Math.floor(el.available / el.quantity))
          .reduce((acc, cur) => cur > acc ? acc : cur)

          if (cartline.quantity > available) {
            cartlineCopy = JSON.parse(JSON.stringify(cartline))
            cartlineCopy.quantity = cartlineCopy.quantity - available
            cartline.quantity = available
            if (available > 0) {
              cartUpdates.push(cartline)
            } else {
              cartDeletes.push(cartline)
            }
            this.errorCart.lines.push(cartlineCopy)
          }
        }
      }
      cartUpdates.forEach(line => this.updateLineItem(line))
      cartDeletes.forEach(line => this.deleteLineItem(line))
    },
    updateQuantity: _.debounce(function (line) {
      this.updateLineItem(line)
    }, 500),
  },
  computed: {
    subtotal () {
      let total = 0
      for (var i = 0; i < this.cart.lines.length; i++) {
        total = total + this.cart.lines[i].price * this.cart.lines[i].quantity
      }
      return total
    },
    total () {
      return (parseFloat(this.subtotal ? this.subtotal : '0') - parseFloat(this.discount ? this.discount : '0') + parseFloat(this.shipping ? this.shipping : '0'))
    },
    itemCount () {
      let count = 0;
      for (var i = 0; i < this.cart.lines.length; i++) {
        count += this.cart.lines[i].quantity
      }
      return count
    }
  },
  components: {
    CpInput: require('../../cp-components-common/inputs/CpInput.vue'),
    CpCustomOrderProcessing: require('../store/CpCustomOrderProcessing.vue'),
    CpCartButton: require('../../components/cart/CpCartButton.vue'),
    CpInputMask: require('../../cp-components-common/inputs/CpInputMask.vue'),
    CpCartItems: require('../store/CpCartItems.vue'),
    CpCartErrorItems: require('../store/CpCartErrorItems.vue')
  }
}
</script>

<style lang="scss">
@import "resources/assets/sass/var.scss";

/* Enter and leave animations can use different */
/* durations and timing functions.              */
.slide-fade-enter-active {
  transition: all .3s ease;
}
.slide-fade-leave-active {
  transition: all .8s cubic-bezier(1.0, 0.5, 0.8, 1.0);
}
.cart-wrapper {
  width: 100%;
  display: flex;
  justify-content: flex-end;
}
.autoship-checkbox {
  text-align: right;
  padding-top: 10px;
  cursor: pointer;
  input {
    cursor: pointer;
  }
}
.checkout-options-wrapper {
    margin: 10px 0px 10px 0px;
    padding: 5px 20px 5px 20px;
    border-radius: 3px;
    background-color: $cp-lighterGrey;
    height: 10%;
    .wholesale-option-wrapper {
        display: flex;
        justify-content: flex-end;
        margin: 10px 0px;
        button {
            margin: 0px 5px 0px 5px;
        }
    }
    .retail-option-wrapper {
        display: flex;
        justify-content: flex-end;
        margin: 10px 0px;
        button {
            margin: 0px 5px 0px 5px;
        }
    }
    .last {
      margin-right: 0px !important;
    }
    .details-section {
      display:flex;
      justify-content: flex-end;
      .payment-totals-section {
        display: flex;
        justify-content: space-between;
        width: 50%;
        .details-titles-section {
          div {
            padding: 5px;
          }
        }
        .details-totals-section {
          display: flex;
          flex-direction: column;
          align-items: flex-end;
          input {
            text-align: center;
            width: 40px;
          }
          div {
            padding: 5px;
          }
        }
        }
      }
    .payment-totals-section {
      border-radius: 3px;
      height: 100%;
      display: flex;
      width: 100%;
      background-color: white;
      padding: 10px;
    }
    @media (max-width: 768px) {
      .retail-option-wrapper {
        .cp-button-link {
          display: block;
        }
          display: block;
          margin: 10px 5px;
          button {
              width: 100%;
              margin: 5px 5px 5px 5px;
          }
      }
      .wholesale-option-wrapper {
        .cp-button-link {
          display: block;
        }
          display: block;
          margin: 10px 5px;
          button {
              width: 100%;
              margin: 5px 5px 5px 5px;
          }
      }
      .details-section {
        display: flex;
        justify-content: center;
        .payment-totals-section {
          width: 100%;
        }

      }
    }
  }
  .custom-order-checkout-wrapper{
    .details-section{
      .payment-form-custom-order{
        .text-area-wrapper{
          textarea{
            box-sizing: border-box;
          }
        }
      }
    }
  }
</style>
