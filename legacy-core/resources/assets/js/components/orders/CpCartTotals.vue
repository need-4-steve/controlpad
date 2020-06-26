<template>
    <div>
        <div class="cart-totals-wrapper">
            <h3>Cart Totals</h3>
            <div class="line-wrapper">
                <label>Cart Subtotal</label>
                <div>
                    <span>{{ request.cart.msrp_subtotal | currency }}</span>
                </div>
            </div>
            <div class="line-wrapper discount">
                <label>Discount</label>
                <div>
                    <span v-if="editTotals">-<input type="number" v-model="request.cart.discount" @change="discountLimit()"></span>
                    <span v-else>-{{ request.cart.discount | currency}}</span>
                </div>
            </div>
            <div class="line-wrapper order-total">
                <label>Shipping <span class="small">(Default Rate: <span>{{ defaultRate | currency }}</span>)</span></label>
                <div>
                    <span v-if="editTotals">$
                        <input type="number" v-model="request.cart.total_shipping" @change="calculateTotals()"style="max-width: 50px;">
                    </span>
                    <span v-else>{{ request.cart.total_shipping | currency }}</span>

                </div>
            </div>
            <div class="line-wrapper order-total" v-if="Auth.hasAnyRole('Admin', 'Superadmin') && editTotals">
                <span>
                  <input type="checkbox" v-model="request.cart.tax_not_charged" @change="calculateTotals()">
                <span><label>Do not calculate or charge sales tax on this order</label></span></span>
            </div>
            <div v-if="!editTotals">
                <div v-if="this.$getGlobal('tax_calculation').show">
                    <div class="line-wrapper order-total"  v-if="request.cart.tax_not_charged">
                        <label v-if="request.cart.tax_not_charged = true">Tax (Not calculated on order)</label>
                        <span>{{ request.cart.total_tax | currency }}</span>
                    </div>
                    <div class="line-wrapper order-total"  v-else>
                        <label>Tax</label>
                        <span> {{ request.cart.total_tax | currency }}</span>
                    </div>
                </div>
                <div class="line-wrapper order-total">
                    <label>Order Total</label>
                    <span>{{ request.cart.total_price | currency }}</span>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
const Shipping = require('../../resources/shipping.js')
const Auth = require('auth')

module.exports = {
  data () {
    return {
      defaultRate: 0,
      Auth: Auth
    }
  },
  props: {
    request: {
      required: true,
      type: Object
    },
    editTotals: {
      type: Boolean,
      default: false
    },
    calculateTotals: {
      type: Function,
      required: false
    }
  },
  mounted () {
    this.getShippingCost()
  },
  methods: {
    discountLimit () {
      this.request.cart.msrp_subtotal = Math.round(this.request.cart.msrp_subtotal * 100) / 100
      if (this.request.cart.discount > this.request.cart.msrp_subtotal) {
        this.request.cart.discount = 0
        this.$toast('You cannot have a negative discount', {error: true})
      }
      this.$emit('input', this.request)
      this.calculateTotals()
    },
    getShippingCost: function () {
        Shipping.getShippingCostByAuth({ total_price: this.request.cart.msrp_subtotal })
            .then((response) => {
                if (response.error) {
                    this.$toast('There was an error calculating the shipping cost.', {error: true});
                } else {
                    this.defaultRate = parseFloat(response.amount);
                }
            });
    }
  },
  events: {
      'get_shipping_rates': function() {
          this.getShippingCost()
      }
  }
}
</script>
<style lang="scss">

</style>
