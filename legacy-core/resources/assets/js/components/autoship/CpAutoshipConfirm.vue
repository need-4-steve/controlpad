<template lang="html">
  <section class="confirm-autoship">
    <div>
      <div class="payment-flex-container">
        <div class="order-summary-section">
          <table class="summary cp-table-inverse">
            <tr>
              <th>PRODUCT</th>
              <th>OPTION</th>
              <th>QUANTITY</th>
              <th>TOTAL PRICE</th>
            </tr>
            <tr v-for="line in cart.lines">
              <td>{{line.item_id ? line.items[0].product_name : line.bundle_name}}</td>
              <td>{{line.item_id ? line.items[0].option : ''}}</td>
              <td>{{line.quantity}}</td>
              <td v-if="line.discount">{{ (line.quantity - line.discount) * line.price | currency }}</td>
              <td v-if="!line.discount">{{ line.quantity * line.price | currency }}</td>
            </tr>
          </table>
          <div class="payment-totals-section">
            <div class="total-line">
              <span>Subtotal</span>
              <span class="">
                {{ cart.subtotal | currency }}
              </span>
            </div>
            <div class="total-line" v-if="cart.discount > 0">
              <span> Discount</span>
              <span class="">
                {{ -cart.discount | currency }}
              </span>
            </div>
            <div class="total-line">
              <span>Estimated Tax</span>
              <span class="">
                {{ cart.tax | currency }}
              </span>
            </div>
            <div class="total-line">
              <span>Estimated Shipping</span>
              <span class="">
                {{ cart.shipping | currency }}
              </span>
            </div>
            <div class="total-line">
              <span>Total</span>
              <span class="">
                {{ cart.total | currency }}
              </span>
            </div>
          </div>
        <div class="payment-method-section cp-form-inverse">
          <small>By clicking Place Order below, you agree to the following <a href="/return-policy" class="agree" target="_blank">terms.</a> Click <a :href="$getGlobal('return_policy').value" target="_blank">here</a> for the return policy</small>
        </div>
      </div>
    </div>
    </div>
  </section>
</template>

<script>

module.exports = {
  data () {
    return {
      couponCode: '',
      couponErrors: {},
      cart: {}
    }
  },
  props: {
  },
  methods: {
    validate () {
      return this.$refs.ccForm.validate()
    },
    getCard () {
      return this.$refs.ccForm.getCard()
    }
  },
  components: {
    CpCreditCardForm: require('../payment/CpCreditCardForm.vue'),
    CpInput: require('../../cp-components-common/inputs/CpInput.vue')
  }
}
</script>

<style lang="scss">
.confirm-autoship {
  .summary {
  }
  .total-line {
    display: flex;
    margin-left: auto;
    max-width: 350px;
    span {
      flex: 1;
    }
  }
  .payment-totals-section {
    text-align: right;
    padding: 10px;
  }
  .place-order-button {
    float: right;
  }
  .payment-method-section {
    text-align: right;
    padding-bottom: 10px;
  }
  @media (max-width: 768px) {
    .payment-flex-container {
      .summary {
        font-size: 12px;
      }
      display: block;
      .col:first-child {
        flex: auto;
        width: auto;
      }
      .col:last-child {
        width: auto;
        flex: initial;
      }
      .col {
        flex: 1;
        padding: 10px;
      }
    }
  }
}
</style>
