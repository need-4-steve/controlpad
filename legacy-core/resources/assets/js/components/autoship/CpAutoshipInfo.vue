<template lang="html">
  <section class="autoship-information-section cp-form-inverse">
    <div class="col custom-info">
      <h3>Information</h3>
      <div>
        {{buyer.first_name}} {{buyer.last_name}}
      </div>
      <div>
        {{buyer.email}}
      </div>
    </div>
    <div class="col">
      <h3>Shipping Address</h3>
      <div v-if="buyer.shipping_address">
        <div>
          {{buyer.shipping_address.name}}
        </div>
        <div>
          {{buyer.shipping_address.line_1}}
        </div>
        <div v-if="buyer.shipping_address.line_2">
          {{buyer.shipping_address.line_2}}
        </div>
        <div>
          {{buyer.shipping_address.city}} 
          {{buyer.shipping_address.state}} 
          {{buyer.shipping_address.zip}} 
        </div>
      </div>
    </div>
    <div class="col">
      <h3>Credit Card</h3>
      <div v-if="cardToken.card_digits">
        <div class="bold">Payment Method</div>
        <div>{{cardToken.card_type}} ending in {{cardToken.card_digits.slice(-4)}}</div>
        <div class="bold">Expiration</div>
        <div>{{cardToken.expiration | expiration}}</div>
      </div>
    </div>
  </section>
</template>

<script>
const Users = require('../../resources/UserApiv0.js')

module.exports = {
  data () {
    return {
      buyer: {},
      cardToken: {},
      sameAsBilling: false,
      validationErrors: {}
    }
  },
  mounted () {
  },
  filters: {
    expiration (expiration) {
        expiration = expiration.split('')
        expiration.splice(2, 0, '/')
        expiration = expiration.join('')
        return expiration
    }
  },
  props: {
  },
  methods: {
    pullBuyer (buyerPid) {
      Users.get(buyerPid, {addresses: true})
        .then((response) => {
          if (!response.error) {
            this.buyer = response
            this.pullCardToken()
          }
      })
    },
    pullCardToken() {
      Users.getCardToken(this.buyer.pid)
        .then((response) => {
          if (!response.error) {
            this.cardToken = response
          }
      })
    },
    validate () {
      if (!this.buyer.first_name) {
        this.$toast('first name required', { error: true, dismiss: false })
        return false
      }
      if (!this.buyer.last_name) {
        this.$toast('last name required', { error: true, dismiss: false })
        return false
      }
      if (!this.buyer.email) {
        this.$toast('email required', { error: true, dismiss: false })
        return false
      }
      let addressFields = ['line_1', 'city', 'state', 'zip']
      addressFields.forEach((field) => {
        if (!this.buyer.shipping_address[field]) {
          this.$toast('billing address ' + field + ' required', { error: true, dismiss: false })
          return false
        }
      })
      if (!this.cardToken.card_digits) {
        this.$toast('Card information not on file. Please Click to Change Info.', { error: true, dismiss: false })
        return false
      }
      return true
    },
  }
}
</script>

<style lang="scss">
  .autoship-information-section {
    display: flex;
    h3 {
      padding-bottom: 15px;
    }
    .bold {
      font-weight: bold;
    }
    .col {
      flex: 1;
      padding: 5px;
      padding-bottom: 15px;
    }
    .same-as-billing {
      width: 35px;
      height: 15px;
    }
  }

  @media (max-width: 768px) {
    .autoship-information-section {
      display: block;
    }
  }
  .cp-form-inverse {
      .custom-info {
          input {
              margin-top: 0px !important;
              margin-bottom: 0px !important;
          }
      }
  }
</style>
