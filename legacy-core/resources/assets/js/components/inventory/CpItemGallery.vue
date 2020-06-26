<template>
  <div id="item-gallery-wrapper">
    <table class="cp-table-standard" v-if="!loading">
      <thead>
        <th>{{ this.optionLabel }}</th>
        <th>Quantity Available</th>
        <th v-if="$getGlobal('rep_edit_inventory').show && Auth.hasAnyRole('Rep') || Auth.hasAnyRole('Admin', 'Superadmin')"> +/- Quantity<cp-tooltip :options="{ content: inventoryTooltip }"></cp-tooltip></th>
        <th v-else>Quantity</th>
        <th v-if="$getGlobal('rep_edit_inventory').show && Auth.hasAnyRole('Rep') || Auth.hasAnyRole('Admin', 'Superadmin')"> <!--placeholder for Edit Quantity Button--></th>
        <th>Wholesale Price</th>
        <th v-if="Auth.hasAnyRole('Admin', 'Superadmin')">Premium Price</th>
        <th v-if="Auth.hasAnyRole('Rep') && $getGlobal('rep_custom_prices').show">Your Price</th>
        <th v-if="Auth.hasAnyRole('Rep') && $getGlobal('replicated_site').show">Hide in Store</th>
        <th>SKU</th>
      </thead>
      <tbody v-for="item in items" :key="item.key">
        <tr>
          <td>{{ item.option }}</td>
          <td> {{ item.quantity_available }}</td>
          <td v-if="$getGlobal('rep_edit_inventory').show && Auth.hasAnyRole('Rep')"><input type="number" @keyup.enter="updateInventory(item, item.update_quantity, 'quantity')" v-model="item.update_quantity"/></td>
          <td v-else-if="ownerId === item.user_id && $getGlobal('reseller_create_product').show || Auth.hasAnyRole('Superadmin', 'Admin')"><input type="number" @keyup.enter="updateInventory(item, item.update_quantity, 'quantity')" v-model="item.update_quantity"/></td>
          <td v-else>{{ item.quantity_available }}</td>
          <td v-if="$getGlobal('rep_edit_inventory').show && Auth.hasAnyRole('Rep') || Auth.hasAnyRole('Admin', 'Superadmin')"><button class="cp-button-standard" :disabled="!item.update_quantity" @click="updateInventory(item, item.update_quantity, 'quantity')">Update</button></td>
          <td>{{ item.wholesale_price}}</td>
          <td v-if="Auth.hasAnyRole('Admin', 'Superadmin')">{{ item.premium_price}}</td>
          <td v-if="Auth.hasAnyRole('Rep') && $getGlobal('rep_custom_prices').show"><input type="number" v-model="item.inventory_price" @keyup="updateInventory(item, item.inventory_price, 'price')"></input></td>
          <td v-if="Auth.hasAnyRole('Rep') && $getGlobal('replicated_site').show"> <input type="checkbox" v-model="item.disable" @change="updateInventory(item, item.disable, 'disable')"></input></td>
          <td>{{ item.sku }}</td>
        </tr>
      </tbody>
    </table>
    <div class="align-center">
        <img class="loading" :src="$getGlobal('loading_icon').value" v-if="loading">
    </div>
  </div>
</template>
<script>
const Inventory = require('../../resources/InventoryAPIv0.js')
const Auth = require('auth')
const _ = require('lodash')

module.exports = {
  data: () => ({
    Auth: Auth,
    quantity: null,
    items: {},
    option: '',
    loading: false,
    inventoryTooltip: 'Either the enter key or button will update inventory. Add a "-" if you want to reduce inventory.'
  }),
  mounted () {
    this.getVariantWithItems()
  },
  methods: {
    getVariantWithItems () {
      this.loading = true
      let request = {}
      if (this.userId === null || this.userId === undefined) {
        request['user_id'] = Auth.getOwnerId()
      } else {
        request['user_id'] = this.userId
      }
      Inventory.getVariant(request, this.variantId)
        .then((response) => {
          this.loading = false
          if (response.error) {
            return this.$toast(response.message)
          }
          this.items = response.items
        })
    },
    updateInventory: _.debounce(function (item, inventoryUpdate, type) {
      let id = null
      if (this.userId !== undefined) {
        id = this.userId
      } else {
        id = Auth.getOwnerId()
      }
      if (type === 'quantity') {
        if (inventoryUpdate === undefined || inventoryUpdate === '' || inventoryUpdate === null) {
          this.$toast('Invalid quantity entered. Please check input and try again.')
          return
        }
        var request = {
          user_id: id,
          quantity: parseInt(inventoryUpdate)
        }
      } else if (type === 'price') {
        request = {
          user_id: id,
          inventory_price: inventoryUpdate
        }
      } else {
        request = {
          user_id: id,
          disable: inventoryUpdate
        }
      }
      Inventory.updateInventory(request, item.id)
        .then((response) => {
          if (!response.error) {
            item.quantity_available = response.quantity_available
            item.update_quantity = null
            return this.$toast(this.optionLabel + ' ' + item.option + ' has been updated.', { dismiss: false })
          } else {
            return this.$toast(response.message, { error: true, dismiss: true })
          }
        })
    }, 500)
  },
  props: ['variantId', 'optionLabel', 'ownerId', 'userId']
}
</script>
<style lang="scss" scoped>
</style>
