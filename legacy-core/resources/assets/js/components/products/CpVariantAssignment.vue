<template lang="html">
  <div class="product-assignment-wrapper cp-box-standard">
    <div class="cp-box-heading">
      PRODUCTS
    </div>
    <div class="cp-box-body">
      <span :class="{ 'cp-validation-errors': validationErrors['items'] }" v-if="validationErrors['items']">{{ validationErrors['items'][0] }}</span>
      <label for="">Search Products</label>
      <cp-typeahead
        @input="addVariant"
        :options="searchResults"
        :clear-dropdown="clearSearchOptions"
        :name-value="{ name: 'name', value: 'id'}"
        @options-cleared="clearSearchOptions"
        :search-function="searchVariants"></cp-typeahead>
        <label for="">Added Products</label>
        <hr />
        <div class="cp-accordion" v-for="(variant, index) in variants">
            <div class="cp-accordion-head" @click="showVariant(index)">
              <h5>{{(variant.product.name != variant.name ? variant.product.name + ' - ' + variant.name : variant.product.name)}}</h5>
              <span class="arrow" v-if="showId !== index"><i class="mdi mdi-chevron-down"></i></span>
              <span class="arrow" v-if="showId === index"><i class="mdi mdi-chevron-up"></i></span>
            </div>
            <div class="cp-accordion-body" :class="{ closed: showId !== index }">
                <div class="cp-accordion-body-wrapper">
                  <div class="delete-or-default">
                    <i class="mdi mdi-close pointer right" @click="removeVariant(variant, index)"></i>
                  </div>
                  <img v-if="variant.images.length > 0" :src="variant.images[0].url" style="max-width: 75px" />
                  <table class="cp-table-inverse">
                    <tr>
                      <th>SIZE</th>
                      <th>PRICE</th>
                      <th>QUANTITY</th>
                    </tr>
                    <tr v-for="(item, index) in variant.items">
                        <td>{{ item.option }}</td>
                        <td>{{ item.wholesale_price }}</td>
                        <td>
                          <cp-input
                            @input="(value) => updateQuantity(item, value)"
                            type="number"
                            :error="validationErrors[item.id]"
                            :value="itemMap[item.id]"></cp-input>
                        </td>
                    </tr>
                  </table>
                </div>
            </div>
        </div>
        <div class="product-totals-grid">
          <hr />
          <div class="col">
            <h4>TOTAL PRICE</h4>
            <hr />
            {{ totalWholesaleValue | currency }}
          </div>
          <div class="col">
            <h4>TOTAL QTY.</h4>
            <hr />
            {{ totalQuantity }}
          </div>
          <div class="col">
            <h4>PACK PRICE</h4>
            <hr />
            <cp-input
              type="text"
              :error="validationErrors['wholesale_price']"
              v-model="bundle.wholesale_price"></cp-input>
          </div>
        </div>
    </div>
  </div>
</template>

<script>
const Inventory = require('../../resources/InventoryAPIv0.js')
const _ = require('lodash')

module.exports = {
  data () {
    return {
      searchResults: [],
      showId: null,
      totalWholesaleValue: 0,
      totalQuantity: 0,
      variants: [],
      itemMap: {}
    }
  },
  props: {
    bundle: {},
    validationErrors: {}
  },
  created () {
    this.loadItems(this.bundle.items)
  },
  methods: {
    clearSearchOptions: _.debounce(function () {
      this.searchResults = []
    }, 400),
    getTotalWholesaleValue () {
      let total = 0
      let quantity = 0
      this.variants.forEach(v => {
        v.items.forEach(i => {
          if (i.id in this.itemMap) {
            quantity += this.itemMap[i.id]
            total += this.itemMap[i.id] * i.wholesale_price
          }
        })
      })

      this.totalQuantity = quantity
      this.totalWholesaleValue = total
    },
    loadItems (items) {
      // TODO loading
      // Convert bundle items to a map of quantities
      this.itemMap = {}
      if (items.length == 0) {
        return
      }
      let variantIds = new Set();
      items.forEach(i => {
        this.itemMap[i.id] = i.quantity
        variantIds.add(i.variant_id)
      })
      // Search and save variants for existing items
      Inventory.getVariants({variant_ids: [...variantIds], expands: ['product', 'variant_images']})
        .then((response) => {
          if (response.error) {
            // TODO report failure and lock page so bundle doesn't get mangled
          } else {
            this.variants = response.data
            this.refresh()
          }
        })
    },
    showVariant (index) {
      if (index === this.showId) {
        this.showId = null
      } else {
        this.showId = index
      }
    },
    searchVariants (searchTerm) {
      // call search product endpoint
      Inventory.getVariants({ search_term: searchTerm, user_id: 1, expands: ['product', 'variant_images'] })
        .then((response) => {
          if (!response.error) {
            this.searchResults = []
            response.data.forEach(v => this.searchResults.push({name: (v.product.name != v.name ? v.product.name + ' - ' + v.name : v.product.name), id: v.id, variant: v}))
          }
        })
    },
    inVariants (id) {
      for (var i = 0; i < this.variants.length; i++) {
        if (this.variants[i].id === id) {
          return true
        }
      }
      return false
    },
    addVariant (variantWrapper) {
      if (!this.inVariants(variantWrapper.variant.id)) {
        this.variants.push(variantWrapper.variant)
      }
      this.refresh()
    },
    updateQuantity (item, value) {
      console.log('updateQuantity', item, value)
      if (value == 0 || this.$isBlank(value)) {
        delete this.itemMap[item.id]
      } else {
        this.itemMap[item.id] = parseInt(value)
      }
      this.refresh()
    },
    refresh () {
      this.bundle.items = this.getItems()
      this.getTotalWholesaleValue()
      this.$forceUpdate()
    },
    removeVariant (variant, index) {
      this.showId = null
      this.variants.splice(index, 1)
      variant.items.forEach((i) => {delete this.itemMap[i.id]})
      this.refresh()
    },
    getItems () {
      // Convert item map to array of objects for bundle to use
      let items = []
      for(let key in this.itemMap) {
        items.push({id: key, quantity: this.itemMap[key]})
      }
      return items
    }
  },
  components: {
    CpInput: require('../../cp-components-common/inputs/CpInput.vue'),
    CpTypeahead: require('../../cp-components-common/inputs/CpTypeahead.vue')
  }
}
</script>

<style lang="scss" scoped>
.product-assignment-wrapper {
  .product-totals-grid {
    margin-top: 10px;
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
}
</style>
