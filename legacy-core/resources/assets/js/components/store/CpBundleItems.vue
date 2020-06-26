<template lang="html">
  <div v-if="variants" class="wholesale-product-wrapper">
    <div v-for="variant in variants">
      <h4>{{ getDisplayName(variant) }}</h4>
      <table v-if="!loading" class="item-table">
        <tr>
          <td>Size: </td>
          <td  v-for="(item, index) in variant.items">{{ item.option }}</td>
        </tr>
        <tr>
          <td>Quantity: </td>
          <td  v-for="(item, index) in variant.items">{{ (itemMap[item.id] ? itemMap[item.id] : 0) }}</td>
        </tr>
      </table>
    </div>
    <img v-if="loading" class="loading" :src="$getGlobal('loading_icon').value">
    <cp-input
      label="Quantity: "
      type="number"
      v-model="packQuantity"
      :error="itemValidationError[bundle.id]"></cp-input>
    <button
      v-if="!loading"
      class="cp-button-standard add-to-cart"
      @click="addToCart(bundle.id, packQuantity)">Add To Cart</button>
  </div>
</template>

<script>
const Inventory = require('../../resources/InventoryAPIv0.js')
const Checkout = require('../../resources/CheckoutAPIv0.js')
const CartUtility = require('../../libraries/CartUtility.js')
const _ = require('lodash')

module.exports = {
  data () {
    return {
      variants: null,
      itemMap: {},
      loading: true,
      itemValidationError: {},
      packQuantity: null
    }
  },
  props: ['bundle', 'cartType'],
  created () {
    this.bundle.items.forEach(i => this.itemMap[i.id] = i.quantity)
    this.getVariants()
  },
  methods: {
    getVariants () {
      this.loading = true
      let params = {
        item_ids: [],
        expands: ['product']
      }
      this.bundle.items.forEach(i => params.item_ids.push(i.id))
      Inventory.getVariants(params)
        .then((response) => {
          this.loading = false
          if (!response.error) {
            this.variants = response.data
          } else {
            // TODO notify error
          }
        })
    },
    getDisplayName (variant) {
      if (!variant.product) {
        return variant.name
      } else if (this.$isBlank(variant.name) || variant.name == variant.product.name) {
        return variant.product.name
      } else {
        return variant.product.name + '-' + variant.name
      }
    },
    addToCart (bundleId, quantity) {
      let pid = null
      let bundle = [{
        'bundle_id': bundleId,
        'quantity': parseInt(quantity)
      }]
      CartUtility.getCartPid(this.cartType).then(res => {
        pid = res

        Checkout.patchCartLines(bundle, pid).then(response => {
          this.addingToCart = false
          if (response.error && response.code === 422) {
            this.itemValidationError = response.message
          }
          if (!response.error) {
            this.$emit('added-to-cart', true)
            this.$toast('Successfully added items to cart', {dismiss: true})
          }
        }).catch(err => {
          console.error(err)
          this.addingToCart = false
        })
      }).catch(err => {
        console.error(new Error(err.message))
        this.addingToCart = false
      })
    }
  },
  components: {
    CpInput: require('../../cp-components-common/inputs/CpInput.vue')
  }
}
</script>

<style lang="scss">
.wholesale-product-wrapper {
  .loading {
    display: block;
    margin: 0 auto;
  }
}
</style>
