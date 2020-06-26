<template lang="html">
    <div class="order-detail-wrapper">
        <div class="flex-end">
            <a class="cp-button-standard print-btn" @click="printOrder()">Print</a>
        </div>
        <div class="order-detail">
            <div class="cp-box-standard">
                <div class="cp-box-heading">
                    Return Detail
                </div>
                <div class="cp-box-body" v-if="this.returns">
                    <div><strong>Receipt ID </strong><a :href="`/orders/${ returns.order.receipt_id }`" >{{ returns.order.receipt_id }}</a></div>
                    <div><strong>Return Ticket ID: </strong>{{returns.id}}</div>
                    <div><strong>Customer Name: </strong>{{returns.first_name}} {{returns.last_name}}</div>
                    <div><strong>Customer Email: </strong>{{returns.email}}</div>
                    <div><strong>Purchased From: </strong>{{returns.order.store_owner.full_name}}</div>
                    <div><strong>Returned Date: </strong>{{ returns.created_at | cpStandardDate('time') }}</div>
                    <div><strong>Order Shipping Charged: </strong>{{ returns.order.total_shipping | currency }}</div>
                    <div><strong>Order Tax Charged: </strong>{{ returns.order.total_tax | currency }}</div>
                    <div><strong>Order Subtotal: </strong>{{ returns.order.subtotal_price | currency }}</div>
                    <div v-if="returns.order.total_discount && returns.order.total_discount > 0"><strong>Orders Total Discount: </strong>{{ returns.order.total_discount | currency }}</div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
const CpOrdersFile = require('../../../libraries/CpOrdersFile.js')

module.exports = {
  data: function () {
    return {
      order: {}
    }
  },
  props: {
    returns: {
      type: Object,
      required: true
    }
  },
  computed: {},
  mounted: function () {
  },
  methods: {
    printOrder: function (orderId) {
      new CpOrdersFile(null, ['pdf'], 'order', {orderId: this.returns.order.id}).run()
    }
  },
  components: {
  }
}
</script>

<style lang="scss">
    // @import "resources/assets/sass/var.scss";
        .order-detail-wrapper {
            .order-detail {
                display: -ms-flex;
                display: -webkit-flex;
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
                        border-right: 1px solid $cp-lightGrey;
                        border-left: 1px solid $cp-lightGrey;
                    }
                }
                strong {
                    font-weight: 400;
                }
            }
            .url {
                user-select: none;
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

</style>
