<template>
  <div id="item-form">
     <div class="item-form-wrapper cp-form-standard">
       <div class="col">
          <cp-select
          :label="'Variant Name'"
          :options="variants"
          :key-value="{ name: 'name', value: 'id' }"
          v-model="item.variant_id"></cp-select>
        <cp-input
          :label="optionLabel"
          type="text"
          :error="validationErrors['option']"
          v-model="item.option"></cp-input>
        <cp-input
          :label="'SKU'"
          type="text"
          :error="validationErrors['sku']"
          v-model="item.sku"></cp-input>
        <cp-input
          :label="'Weight (Optional)'"
          type="number"
          :error="validationErrors['weight']"
          v-model="item.weight"></cp-input>
          <cp-input
          :label="'Location (Optional)'"
          type="text"
          :error="validationErrors['location']"
          v-model="item.location"></cp-input>
      </div>
      <div class="col">
        <cp-input
          :label="'Premium Price'"
          type="number"
          :error="validationErrors['premium_price']"
          v-model="item.premium_price"></cp-input>
        <cp-input
          :label="'Wholesale Price'"
          type="number"
          :error="validationErrors['wholesale_price']"
          v-model="item.wholesale_price"></cp-input>
        <cp-input
          :label="'Retail Price'"
          type="number"
          :error="validationErrors['retail_price']"
          v-model="item.retail_price"></cp-input>
        <cp-input
          :label="'Premium Shipping Cost (Optional)'"
          type="number"
          :error="validationErrors['premium_shipping_cost']"
          v-model="item.premium_shipping_cost"></cp-input>
        </div>
      </div>
      <button v-if="!item.id" class="cp-button-standard item" @click="createItem()" :disabled="disableSubmit">Add Item</button>
      <button v-if="item.id" class="cp-button-standard item" @click="updateItem()" :disabled="disableSubmit">Update Item</button>
  </div>
</template>
<script>
const Inventory = require('../../resources/InventoryAPIv0.js')

module.exports = {
  routing: [
    { name: 'site.CpItemFormBeta', path: '/products/create', meta: { title: 'Create Product' } }
  ],
  data: () => ({
    validationErrors: {},
    optionType: '',
    disableSubmit: false
  }),
  props: {
    item: {
      type: Object,
      default () {
        return {
          variant_id: null,
          option: '',
          sku: '',
          location: '',
          premium_price: null,
          wholesale_price: null,
          retail_price: null,
          premium_shipping_cost: null
        }
      }
    },
    variants: {
      type: Array,
      default () {
        return null
      }
    }
  },
  computed: {
    optionLabel () {
      if (this.item.variant_id) {
        for (var i = 0; i < this.variants.length; i++) {
          if (this.variants[i].id.toString() === this.item.variant_id.toString() && this.variants[i].option_label !== '') {
            return this.variants[i].option_label
          }
        }
      }
      return 'Option'
    }
  },
  methods: {
    validatePrices () {
      if (this.item.premium_price === '') {
        this.item.premium_price = null
      }
      if (this.item.wholesale_price === '') {
        this.item.wholesale_price = null
      }
      if (this.item.retail_price === '') {
        this.item.retail_price = null
      }
      if (this.item.premium_shipping_cost === '') {
        this.item.premium_shipping_cost = null
      }
      if (this.item.weight === '') {
        this.item.weight = null
      }
    },
    createItem () {
      this.validatePrices()
      Inventory.createItem(this.item)
        .then((response) => {
          if (response.error) {
            this.validationErrors = response.message
            return
          }
          this.validationErrors = {}
          this.$emit('added-variant')
          this.$toast('Item successfully created.')
        })
    },
    updateItem () {
      this.validatePrices()
      Inventory.updateItem(this.item, this.item.id)
        .then((response) => {
          if (response.error) {
            this.validationErrors = response.message
            return
          }
          this.validationErrors = {}
          this.$emit('added-variant')
          this.$toast('Item successfully updated.')
        })
    }
  },
  components: {
    CpInput: require('../../cp-components-common/inputs/CpInput.vue'),
    CpSelect: require('../../cp-components-common/inputs/CpSelect.vue')
  }
}
</script>
<style lang="scss" scoped>
#item-form{
  .item-form-wrapper {
    display: flex;
    justify-content: space-between;
    .col{
      width: 49%;
      display: flex;
      flex-direction: column;
    }
  }
.item {
  float: right;
}
}
</style>
