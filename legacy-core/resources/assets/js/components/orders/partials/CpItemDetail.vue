<template>
  <div>
    <table v-if="order.lines" class="cp-table-standard orderlines-detail">
        <thead>
            <th>Product</th>
            <th>Variant</th>
            <th>Option</th>
            <th>Quantity</th>
            <th>Price</th>
            <th>Discount</th>
            <th v-if="Auth.hasAnyRole('rep') && $getGlobal('allow_reps_events').value || Auth.hasAnyRole('admin', 'superadmin')" >Event Id</th>
            <th>SKU</th>
        </thead>
        <tbody>
            <template v-if="order.uid">
              <tr v-for="line in order.lines">
                  <td>{{ line.name || line.product.name }}</td>
                  <td>{{ line.variant.name || line.variant }}</td>
                  <td>{{ line.option }}</td>
                  <td v-if="line.quantity || line.quantity == 0">{{ line.quantity }}</td>
                  <td v-else>{{line.pivot.quantity}}</td>
                  <td v-if="line.price">{{ line.price | currency }}</td>
                  <td v-else-if="line.msrp">{{ line.msrp.price | currency }}</td>
                  <td v-else></td>
                  <td v-if="line.discount_amount">{{ line.discount_amount | currency }}</td>
                  <td v-else>N/A</td>
                  <td v-if="Auth.hasAnyRole('rep') && $getGlobal('allow_reps_events').value ||  Auth.hasAnyRole('superadmin', 'admin')"> {{ line.event_id }}</td>
                  <td>{{ line.manufacturer_sku }}</td>
              </tr>
            </template>
            <template v-else>
              <tr v-for="line in filteredLines">
                <td>{{ line.item_id ? line.items[0].product_name : line.bundle_name }}</td>
                <td>{{ line.item_id ? line.items[0].variant_name : '' }}</td>
                <td>{{ line.item_id ? line.items[0].option : '' }}</td>
                <td>{{ line.quantity }}</td>
                <td>{{ (line.price - (line.discount ? line.discount : 0)) * line.quantity | currency }}</td>
                <td>{{ line.discount }}</td>
                <td v-if="Auth.hasAnyRole('rep') && $getGlobal('allow_reps_events').value ||  Auth.hasAnyRole('superadmin', 'admin')"> {{ line.event_id }}</td>
                <td>{{ line.manufacturer_sku }}</td>
              </tr>
            </template>
        </tbody>
    </table>
    <br>
  </div>
</template>

<script>
const Auth = require('auth')
module.exports = {
  data: function () {
    return {
      Auth,
      fulfilledByCorpShow: false
    }
  },
  props: {
    order: {
      type: Object,
      required: true
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
        return false
      })
    }
  }
}
</script>

<style lang="scss">
</style>
