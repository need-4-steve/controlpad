<template lang="html">
      <div class="order-view" v-if="!loading">
          <cp-order-detail :order="order" :hide-hold="hideHold" ></cp-order-detail>
          <cp-return-detail v-if="allowReturns && showReturns" :order="order"></cp-return-detail>
          <cp-item-detail v-if="!showReturns" :order="order"></cp-item-detail>
        <div class="button-wrapper">
          <button v-if="allowReturns && !order.token && !showReturns" v-show="order.status === 'fulfilled'" class="cp-button-standard pull-right" @click="showReturns = true">Returns</button>
          <button v-if="allowReturns && showReturns" class="cp-button-standard pull-right back" @click="showReturns = false">Cancel</button>
        </div>
      </div>
</template>

<script>
const Auth = require('auth')
const Orders = require('../../resources/OrdersAPIv0.js')
const Invoice = require('../../resources/invoice.js')

module.exports = {
  name: 'CpOrder',
  routing: [
    {
      name: 'site.CpOrder',
      path: 'orders/:receipt_id',
      alias: 'invoices/:receipt_id',
      meta: {
        title: 'Order',
        nosubscription: !Auth.hasAnyRole('Rep')
      },
      props: true
    }
  ],
  data: function () {
    return {
      loading: true,
      authUserId: '',
      showReturns: false,
      order: {
        customer: {},
        shipping_address: {},
        billing_address: {}
      },
      fulfilledByCorpShow: false
    }
  },
  props: {
    receipt_id: {
      type: String,
      required: true
    },
    typeOfOrder: {
      type: String,
      default: function () {
        let path = this.$route.path
        if (!path.includes('orders')) {
          return 'invoice'
        }
        return 'order'
      }
    },
    hideHold: {
      type: Boolean
    }
  },
  computed: {
    allowReturns () {
      return (Auth.hasAnyRole('Admin', 'Superadmin')
        || (Auth.hasAnyRole('Rep') && Auth.getClaims().sellerType === 'Reseller' && this.$getGlobal('reseller_returns').show)
        || (Auth.hasAnyRole('Rep') && Auth.getClaims().sellerType === 'Affiliate' && this.$getGlobal('affiliate_returns').show))
    }
  },
  mounted: function () {
    this.getOrder()
  },
  methods: {
    getOrder: function () {
      this.loading = true
      if (this.typeOfOrder === 'order') {
        Orders.getByReceiptId({orderlines: 1, tracking: 1}, this.receipt_id)
        .then((response) => {
          this.order = response
          this.loading = false
        })
      } else {
        Invoice.getOrderByID(this.receipt_id)
            .then((response) => {
              response.buyer_first_name = response.customer.first_name
              response.buyer_last_name = response.customer.last_name
              response.buyer_email = response.customer.email
              if (response.store_owner) {
                response.seller_name = response.store_owner.full_name
              }
              if (response.shipping_address) {
                response.shipping_address.line_1 = response.shipping_address.address_1
                response.shipping_address.line_2 = response.shipping_address.address_2
              }
              if (response.billing_address) {
                response.billing_address.line_1 = response.billing_address.address_1
                response.billing_address.line_2 = response.billing_address.address_2
              }
              this.order = response
              this.order.lines = response.invoice_items
              this.order.truncated_token = response.token.substring(6, 0) + '...'
              this.loading = false
            })
      }
    }
  },
  components: {
    'CpOrderDetail': require('./CpOrderDetail.vue'),
    'CpReturnDetail': require('./partials/CpReturnDetail.vue'),
    'CpItemDetail': require('./partials/CpItemDetail.vue')
  }
}
</script>

<style lang="sass">
@import "resources/assets/sass/var.scss";
.order-view {
    width: 100%;
    .orderlines-detail {
        tr:nth-child(even) {
            background: initial;
        }
        tr {
            border-bottom: 0.5px solid $cp-lightGrey;
        }
    }
    .print-btn {
        margin: 0px 15px 10px 0px;
    }
    .yellow {
        margin: 0px 15px 10px 0px;
    }
    .button-wrapper {
        .back {
            margin-right: 10px;
        }
    }
}
</style>
