<template lang="html">
  <div class="four-column-grid">
    <div class="col">
      <h4>PRODUCT ID</h4>
      <hr />
      <cp-input
        label="Manufacturer SKU"
        type="text"
        :error="validationErrors['items.'+itemIndex+'.manufacturer_sku']"
        :value="item.manufacturer_sku"
        v-model="item.manufacturer_sku"></cp-input>
      <cp-input
        label="Custom SKU"
        type="text"
        :error="validationErrors['items.'+itemIndex+'.custom_sku']"
        :value="item.custom_sku"
        v-model="item.custom_sku"></cp-input>
      <cp-input
        label="Product Warehouse Location (Optional)"
        type="text"
        :error="validationErrors['items.'+itemIndex+'.custom_sku']"
        :value="item.location"
        v-model="item.location"></cp-input>
    </div>
    <div class="col">
      <h4>VARIANT</h4>
      <hr />
      <cp-input
        label="Print"
        type="text"
        :error="validationErrors['items.'+itemIndex+'.print']"
        :value="item.print"
        v-model="item.print"></cp-input>
      <cp-input
        label="Size"
        type="text"
        :error="validationErrors['items.'+itemIndex+'.size']"
        :value="item.size"
        v-model="item.size"></cp-input>
    </div>
    <div class="col">
      <h4>DIMENSIONS</h4>
      <hr />
      <cp-input
        label="Width"
        type="number"
        :error="validationErrors['items.'+itemIndex+'.width']"
        :value="item.width"
        v-model="item.width"></cp-input>
      <cp-input
        label="Length"
        type="number"
        :error="validationErrors['items.'+itemIndex+'.length']"
        :value="item.length"
        v-model="item.length"></cp-input>
      <cp-input
        label="Height"
        type="number"
        :error="validationErrors['items.'+itemIndex+'.height']"
        :value="item.height"
        v-model="item.height"></cp-input>
      <cp-input
        label="Weight"
        type="number"
        :error="validationErrors['items.'+itemIndex+'.weight']"
        :value="item.weight"
        v-model="item.weight"></cp-input>
    </div>
    <div class="col">
      <h4>PRICE</h4>
      <hr />
      <cp-input
        label="Premium"
        type="number"
        :error="validationErrors['items.'+itemIndex+'.premium_price.price']"
        :value="item.premium_price.price"
        v-model="item.premium_price.price"></cp-input>
      <cp-input
        label="Wholesale"
        type="number"
        :error="validationErrors['items.'+itemIndex+'.wholesale_price.price']"
        :value="item.wholesale_price.price"
        v-model="item.wholesale_price.price"></cp-input>
      <cp-input
        label="MSRP"
        type="number"
        :error="validationErrors['items.'+itemIndex+'.msrp.price']"
        :value="item.msrp.price"
        v-model="item.msrp.price"></cp-input>
      <cp-input
        label="Premium Shipping Cost (Optional)"
        type="number"
        :error="validationErrors['items.'+itemIndex+'.premium_shipping_cost']"
        :value="item.premium_shipping_cost"
        v-model="item.premium_shipping_cost"></cp-input>
    </div>
  </div>
</template>

<script>
module.exports = {
  data () {
    return {}
  },
  props: {
    validationErrors: {},
    item: {
      type: Object,
      default () {
        return {
          msrp: { price: null },
          wholesale_price: { price: null },
          premium_price: { price: null }
        }
      }
    },
    itemIndex: {
      default: ''
    }
  },
  components: {
    CpInput: require('../../cp-components-common/inputs/CpInput.vue')
  }
}
</script>

<style lang="scss" scoped>
.four-column-grid {
  display: flex !important;
  .col {
    flex: 1;
    padding: 1%;
    h4 {
      margin-bottom: 5px;
    }
    hr {
      display: block;
      margin-top: 10px;
      margin-bottom: 20px;
    }
  }
}
</style>
