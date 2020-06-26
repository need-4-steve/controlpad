<template lang="html">
    <div>
      <div>
        <h3>Coupon Details</h3>
        <table>
          <tr>
            <th>Created at: </th>
            <td>{{ coupon.created_at }}</td>
          </tr>
          <tr>
            <th>Last applied: </th>
            <td v-if="lastUsed">{{ lastUsed }}</td>
            <td v-else>No record of use.</td>
          </tr>
          <tr v-if="coupon.expires_at !== null">
            <th>Expires at: </th>
            <td>{{ coupon.expires_at }}</td>
          </tr>
        </table>
      </div>
        <h3>Orders for {{coupon.title}} </h3>
        <div class="coupon-form-wrapper">
            <table class="cp-table-standard">
                <thead>
                    <tr>
                        <th>Order</th>
                        <th>Order Total</th>
                        <th>Date</th>
                        <th>Coupon Amount</th>
                    </tr>
                </thead>
                <tbody v-if="coupon.uses > 0">
                    <tr v-for="order in coupon.orders">
                        <td><a v-bind:href="'/orders/' + order.receipt_id">{{order.receipt_id}}</a></td>
                        <td>{{order.total_price | currency}}</td>
                        <td>{{order.created_at | cpStandardDate}}</td>
                        <td v-if="coupon.is_percent">{{ order.total_discount | currency }} </td>
                        <td v-else>{{ coupon.amount | currency }}</td>
                    </tr>
                </tbody>
                <tbody v-else>
                    <tr class="row">
                        <td class="cell">
                            <span class="overflow">This coupon has not been applied to an order.</span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

<script>
module.exports = {
  data () {
    return {
      lastUsed: null
    }
  },
  props: {
    coupon: {
      type: Object
    }
  },
  mounted () {
    let couponData = JSON.parse(JSON.stringify(this.coupon.orders))
    if (couponData.length > 0) {
      couponData.sort(function(a,b){
        return new Date(b.created_at) - new Date(a.created_at);
      });
      this.lastUsed = couponData[0].created_at
    }
  }
}
</script>

<style lang="scss">
@import "resources/assets/sass/var.scss";

.coupon-form-wrapper {
    .coupon-field {
        background: $cp-lighterGrey;
        border: none;
        width: 100%;
        height: 200px;
        &.smaller-field {
            height: 100px;
        }
    }
}
</style>
