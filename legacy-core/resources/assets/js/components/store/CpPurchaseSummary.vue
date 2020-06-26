<template lang="html">
  <div class="purchase-summary-wrapper" v-if="order.pid || invoice">
    <div class="cp-panel-standard">
      <div class="header-text">
        <h3 v-if="message">{{message}}</h3>
        <h3 v-else-if="invoice.uid">Invoice sent successfully!</h3>
        <h3 v-else>Order placed successfully!</h3>
      </div>
      <div class="order-line-items-section">
        <table class="cp-table-inverse">
          <tr>
            <th>PRODUCT</th>
            <th>SIZE</th>
            <th>QUANTITY</th>
            <th>PRICE</th>
          </tr>
          <tr v-for="line in filteredLines">
            <td>{{line.item_id ? line.items[0].product_name : line.bundle_name}}</td>
            <td>{{line.item_id ? line.items[0].option : ''}}</td>
            <td>{{line.quantity}}</td>
            <td>{{ (line.price - (line.discount ? line.discount : 0)) * line.quantity | currency }}</td>
          </tr>
          <tr v-for="item in invoice.items">
            <td>{{item.variant}}</td>
            <td>{{item.option}}</td>
            <td>{{item.quantity}}</td>
            <td>{{item.quantity * item.price | currency }}</td>
          </tr>
        </table>
      </div>
      <div class="order-details-section">
        <div class="orders-details-header">
          <div>Subtotal: </div>
          <div>Discount: </div>
          <div>Shipping: </div>
          <div>Tax: </div>
          <div><b>Total: </b></div>
        </div>
        <div class="order-details" v-if="order.pid">
          <div>{{ order.subtotal_price | currency }}</div>
          <div>{{ -order.total_discount | currency}}</div>
          <div>{{ order.total_shipping | currency }}</div>
          <div>{{ order.total_tax | currency }}</div>
          <div><b>{{ order.total_price | currency }}</b></div>
        </div>
        <div class="order-details" v-else>
          <div>{{ invoice.subtotal_price | currency }}</div>
          <div>{{ -invoice.total_discount | currency}}</div>
          <div>{{ invoice.total_shipping | currency }}</div>
          <div>{{ 0.00 | currency }}</div>
          <div><b>{{ (invoice.subtotal_price - invoice.total_discount + invoice.total_shipping) | currency }}</b></div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
module.exports = {
  data () {
    return {}
  },
  props: {
    order: {
      type: Object,
      default () {
        return {}
      }
    },
    invoice: {
      type: Object,
      default () {
        return {}
      }
    },
    message: {
      type: String,
      default () {
        return null
      }
    }
  },
  computed: {
    filteredLines() {
      if (!this.order.pid) {
        return []
      }
      return this.order.lines.filter(line => {
        if (!line.item_id ^ !line.bundle_id) {
          return line
        }
      })
    }
  }
}
</script>

<style lang="scss">
.purchase-summary-wrapper {
  .header-text {
    width: 100%;
    text-align: center;
  }
  .order-line-items-section {
    max-width: 600px;
    margin: 0 auto;
  }
  .order-details-section {
    max-width: 400px;
    margin: 0 auto;
    margin-top: 15px;
    display: flex;
    font-size: 1.1em;
    .order-details-header {
      padding: 10px;
      flex: 1;
    }
    .order-details {
      padding: 5px;
      text-align: right;
      flex: 1;
    }
  }
}
</style>
