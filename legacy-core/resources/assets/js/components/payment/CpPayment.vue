<template lang="html">
  <section class="payment-page-wrapper">
      <div class="order-summary">
        <h3>ORDER SUMMARY</h3>
        <div class="space-between">
          <span>Subtotal</span>
          <span>{{checkout.subtotal | currency}}</span>
        </div>
        <div class="space-between" v-if="checkout.discount">
          <span>Discount</span>
          <span>{{-checkout.discount | currency}}</span>
        </div>
        <div class="space-between">
          <span>Shipping</span>
          <span>{{checkout.shipping | currency}}</span>
        </div>
        <div class="space-between" v-if="this.$getGlobal('tax_calculation').show">
          <span>Tax</span>
          <span>{{checkout.tax | currency}}</span>
        </div>
        <div class="space-between">
          <span>Total</span>
          <span>{{checkout.total | currency}}</span>
        </div>
      </div>
      <div class="payment-detail-wrapper">
        <cp-credit-card-form ref="ccForm" class="cp-form-inverse border-pad" :disabled="checkout.total == 0"></cp-credit-card-form>
        </div>
    </div>
  </section>
</template>

<script>

module.exports = {
  data  () {
    return {

    }
  },
  props:  {
    checkout: {
        type: Object,
        required: true
    }
  },
  methods: {
    preventDefault (event) {
      event.stopPropagation()
    },
    getCard () {
      return this.$refs.ccForm.getCard()
    },
    validate () {
      return this.$refs.ccForm.validate()
    }
  },
  components: {
    CpCreditCardForm: require('../payment/CpCreditCardForm.vue')
  }
}
</script>

<style lang="scss" scoped>
@import "resources/assets/sass/var.scss";
.payment-page-wrapper {
  background-color: $cp-lighterGrey;
  padding: 20px 10px;
    width: 100%;
    margin: 0 auto;
    .order-summary {
      float: right;
      width: 100%;
      max-width: 275px;
      &:after {
        display: table;
        content: "";
        clear: both;
      }
      h3 {
        padding: 10px 15px;
        text-align: center;
      }
    }
    .expiration-dates {
    box-sizing: border-box;
    display: flex;
    :first-child {
        padding-right: 2px;
        width:49%;
    }
    :last-child {
        padding-left: 2px;
        width:49%;
    }
    & > div {
       flex: 1;
   }
}
  .payment-detail-wrapper {
    width: 98%;
    max-width: 400px;
    margin: 0 auto;
    float: left;
    &:after {
      display: table;
      content: "";
      clear: both;
    }
    h3 {
        text-align: center;
    }
  }
  .border-pad {
      padding: 5px;
  }
  .payment-info {
    padding: 20px 15px;
    .input-wrapper, .select-wrapper {
      input, select {
        background: #fff;
      }
    }
    img {
      float: right;
      padding: 20px 10px;
    }
  }
  .space-between {
    display: flex;
    -webkit-display: flex;
    justify-content: space-between;
    -webkit-justify-content: space-between;
    padding: 15px 20px;
    border-bottom: solid 1px #eee;
    span:nth-child(2) {
      color: #000;
    }
  }
  label {
    font-size: 12px;
    font-weight: 300;
    min-height: 17px;
  }
  .btn-wrapper {
    text-align: center;
  }
  .btn {
    margin: 20px auto;
  }
  &:after {
    display: table;
    content: "";
    clear: both;
  }
  .input-wrapper, .select-wrapper {
      &.duo {
        display: flex;
        -webkit-display: flex;
        justify-content: space-between;
        -webkit-justify-content: space-between;
        .half {
          width: 48%;
        }
        &:after {
          content: "";
        }
        .quarter {
          width: 23%;
          position: relative;
          span {
            position: absolute;
            top: 22px;
            left: 20px;
          }
        }
      }
      input, select {
        background: #fff;
        width: 100%;
        border: none;
        height: 30px;
        text-indent: 3px;
        margin: 5px 0;
        font-weight: 300;
      }
      select {
        text-indent: 5px;
      }
  }
  .lnr-credit-card {
    font-size: 28px;
    line-height: 40px;
  }
}
@media (max-width: 700px) {
  .payment-page-wrapper {
    padding: 20px 0;
    .order-summary {
      float: none;
      max-width: 100%;
      h3 {
        padding: 20px;
      }
    }
    .space-between {
      max-width: 300px;
      margin: 0 auto;
    }
    .payment-detail-wrapper {
      float: none;
      margin-top: 25px;
    }
    .select-wrapper {
      width: auto !important;
    }
  }
}
</style>
