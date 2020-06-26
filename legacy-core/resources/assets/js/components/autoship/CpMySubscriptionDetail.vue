<template lang="html">
    <div class="subscription-detail-wrapper">
        <div class="subscription-detail">
            <div class="cp-box-standard">
                <div class="cp-box-heading">
                    Subscription Detail
                </div>
                <div class="cp-box-body" v-if="subscription != null">
                    <div><strong>Subscription ID: </strong>{{ subscription.id }}</div>
                    <div v-if="subscription.buyer"><strong>Customer Name: </strong>{{ subscription.buyer.first_name }} {{ subscription.buyer.last_name }}</div>
                    <div v-if="subscription.buyer"><strong>Customer Email: </strong> {{ subscription.buyer.email }}</div>
                    <div v-if="subscription.seller"><strong>Purchased From: </strong> {{ subscription.seller.first_name + ' ' + subscription.seller.last_name }}</div>
                    <div v-if="subscription.seller"><strong>Seller Email: </strong> {{ subscription.seller.email }}</div>
                    <div><strong>Order Date: </strong>{{ subscription.created_at | cpStandardDate('time') }}</div>
                    <div><strong>Payment Type: </strong>{{ 'Credit Card' }}</div>
                    <div><strong>Subtotal Price: </strong>{{ subscription.subtotal | currency }}</div>
                    <div><strong>Discount: </strong>{{ subscription.discount | currency }}</div>
                    <div><strong>Total Price: </strong>{{ subscription.total_price | currency }}<cp-tooltip :options="{ content: 'Shipping and Tax will be calculated during purchase.'}"/></div>
                </div>
            </div>
            <div class="cp-box-standard middle-box" v-if="subscription.buyer != null">
                <div class="cp-box-heading">
                    Shipping To
                </div>
                <div class="cp-box-body"  v-if="subscription.buyer.shipping_address !== null">
                    <div v-if='subscription.buyer.shipping_address.name'><strong>Name: </strong> {{ subscription.buyer.shipping_address.name}}</div>
                    <div v-else><strong>Name: </strong> {{ subscription.buyer.first_name }} {{ subscription.buyer.last_name }}</div>
                    <div><strong>Address: </strong>{{ subscription.buyer.shipping_address.line_1 }}</div>
                    <div v-if="subscription.buyer.shipping_address.line_2"><strong>Address 2: </strong>
                        {{ subscription.buyer.shipping_address.line_2 }}
                    </div>
                    <div><strong>City: </strong>{{ subscription.buyer.shipping_address.city }}</div>
                    <div><strong>State: </strong>{{ subscription.buyer.shipping_address.state }}</div>
                    <div><strong>Zip: </strong>{{ subscription.buyer.shipping_address.zip }}</div>
                </div>
            </div>
            <div class="cp-box-standard"  v-if="subscription.buyer != null">
                <div class="cp-box-heading">
                    Billing To
                </div>
                <div class="cp-box-body"  v-if="subscription.buyer.billing_address !== null">
                    <div v-if='subscription.buyer.billing_address.name'><strong>Name: </strong> {{ subscription.buyer.billing_address.name }}</div>
                    <div v-else><strong>Name: </strong> {{ subscription.buyer.first_name }} {{ subscription.buyer.last_name }}</div>
                    <div><strong>Address: </strong>{{ subscription.buyer.billing_address.line_1 }}</div>
                    <div v-if="subscription.buyer.billing_address.line_2"><strong>Address 2: </strong>
                        {{ subscription.buyer.billing_address.line_2 }}
                    </div>
                    <div><strong>City: </strong>{{ subscription.buyer.billing_address.city }}</div>
                    <div><strong>State: </strong>{{ subscription.buyer.billing_address.state }}</div>
                    <div><strong>Zip: </strong>{{ subscription.buyer.billing_address.zip }}</div>
                </div>
            </div>
        </div>
        <div class="cp-box-standard">
            <div class="cp-box-heading">
                Manage Subscription
            </div>
            <div class="cp-box-body">
              <div>Status: {{ subscription.disabled_at == null ? 'Active' : 'Cancelled' }}</div>
              <div>Next Order Date: {{ subscription.next_billing_at | cpStandardDate }}</div>
              <div v-if="subscription.disabled_at == null"><button class="cp-button-standard" @click="showConfirm = true">Cancel Subscription</button></div>
              <div v-else>Cancelled On: {{ subscription.disabled_at | cpStandardDate }}</div>
            </div>
        </div>
        <div>
          <table v-if="subscription.lines" class="cp-table-standard orderlines-detail">
              <thead>
                  <th>Product</th>
                  <th>Variant</th>
                  <th>Option</th>
                  <th>Quantity</th>
                  <th>Price</th>
                  <th>SKU</th>
              </thead>
              <tbody>
                  <template>
                    <tr v-for="line in subscription.lines">
                      <td>{{ line.item_id ? line.items[0].product_name : line.bundle_name }}</td>
                      <td>{{ line.item_id ? line.items[0].variant_name : '' }}</td>
                      <td>{{ line.item_id ? line.items[0].option : '' }}</td>
                      <td>{{ line.quantity }}</td>
                      <td>{{ (line.price - (line.discount ? line.discount : 0)) * line.quantity | currency }}</td>
                      <td>{{ line.item_id ? line.items[0].sku : '' }}</td>
                    </tr>
                  </template>
              </tbody>
          </table>
          <br>
        </div>

        <cp-confirm
        :message="'You are about to cancel your subscription. Would you like to continue?'"
        v-model="showConfirm"
        :show="showConfirm"
        :callback="confirmCancel"
        :params="{id: subscription.id}"></cp-confirm>
    </div>
</template>

<script>
const Autoship = require('../../resources/AutoshipAPIv0.js')
const Auth = require('auth')

module.exports = {
  name: 'CpMySubscriptionDetail',
  routing: [
    {
      name: 'site.CpMySubscriptionDetail',
      path: 'my-subscriptions/:subscriptionID',
      meta: {
        title: 'My Subscription',
        nosubscription: true,
        type: 'self'
      },
      props: true
    },
    {
      name: 'site.CpCustomerSubscriptionDetail',
      path: 'customer-subscriptions/:subscriptionID',
      meta: {
        title: 'Customer Subscription',
        nosubscription: true,
        type: 'customer'
      },
      props: true
    }
  ],
  created () {
    this.getSubscription()
    this.displayType = this.$route.meta.type
  },
  data: function () {
    return {
      subscription: {},
      showConfirm: false,
      displayType: 'self'
    }
  },
  props: {
    subscriptionID: {
      type: String,
      required: true
    }
  },
  methods: {
    getSubscription () {
      Autoship.getSubscription(this.subscriptionID)
      .then((response) => {
        this.subscription = response
        this.calculateTotal()
      })
    },
    confirmCancel () {
      Autoship.disableSubscription(this.subscription.id)
      .then((response) => {
        this.subscription = response
        this.calculateTotal()
      })
    },
    calculateTotal () {
      this.subscription.total_price = this.subscription.subtotal - this.subscription.discount
    }
  },
  components: {
    CpTooltip: require('../../custom-plugins/CpTooltip.vue')
  }
}
</script>

<style lang="scss">
    @import "resources/assets/sass/var.scss";
        .subscription-detail-wrapper {
          .subscription-detail-action-buttons {
              margin-bottom: 10px;
          }
            .subscription-detail {
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
          .subscription-detail-wrapper {
            .subscription-detail {
                display: block !important;
            }
          }
        }
</style>
