<template>
  <div id="variants">
    <div class="added-items">
      <h4>ADDED Variants</h4>
      <hr />
      <div class="cp-accordion" v-for="(variant, index) in addedItems">
          <div class="cp-accordion-head" :class="{ 'item-error': itemError(index) }" @click="showItem(index)">
            <div v-if="variant" class="variant-header">
              <div class="col" v-if="variant.images[0]">
                <img class="variant-img" :src="variant.images[0].url" alt="">
              </div>
              <div class="col" v-else>
                <h5 class="variant-img" >No Image</h5>
              </div>
              <div class="col">
                <h5 class="col">{{variant.name}}</h5>
              </div>
              <div class="col">
                <h5 class="col">{{variant.items.length}}</h5>
              </div>
            </div>
            <div class="col">
              <span class="arrow" v-if="showId !== index"><i class="mdi mdi-chevron-down"></i></span>
              <span class="arrow" v-if="showId === index"><i class="mdi mdi-chevron-up"></i></span>
            </div>
          </div>
          <div class="cp-accordion-body" :class="{ closed: showId !== index }">
              <div class="cp-accordion-body-wrapper">
                <div class="right">
                  <i class="mdi mdi-close pointer" @click="deleteItem('variant', variant, index)"></i>
                </div>
                <cp-variant-form
                :variant="variant"
                :product-id="variant.product_id"
                :item-index="index"></cp-variant-form>
                <section class="cp-panel-border">
                  <label for="items-list">Variant Items: </label>
                  <div v-if="variant.items" class="cp-accordion item" v-for="(item, index) in variant.items">
                    <div class="cp-accordion-head" :class="{ 'item-error': itemError(index) }" @click="showItemChild(index)">
                      <div class="item-header">
                        <h5>{{variant.option_label}}: {{item.option}} - SKU: {{item.sku}}</h5>
                      </div>
                      <span class="arrow" v-if="showIdChild !== index"><i class="mdi mdi-chevron-down"></i></span>
                      <span class="arrow" v-if="showIdChild === index"><i class="mdi mdi-chevron-up"></i></span>
                    </div>
                    <div class="cp-accordion-body" :class="{ closed: showIdChild !== index }">
                      <div class="cp-accordion-body-wrapper">
                        <div class="right">
                          <i class="mdi mdi-close pointer" @click="deleteItem('item', item, index)"></i>
                        </div>
                        <cp-item-form
                        :item-index="index"
                        :item="item"
                        :variants="addedItems"></cp-item-form>
                      </div>
                    </div>
                  </div>
                </section>
              </div>
          </div>
      </div>
    </div>
  </div>
</template>
<script>
const Inventory = require('../../resources/InventoryAPIv0.js')

module.exports = {
  data () {
    return {
      newImages: [],
      variants: {},
      newAddedItems: [],
      showId: null,
      showIdChild: null,
      validationErrors: {}
    }
  },
  props: {
    addedItems: {
      type: Array,
      default () {
        return []
      }
    }
  },
  mounted () {
  },
  computed: {
    itemError () {
      return function (index) {
        return JSON.stringify(this.validationErrors).includes('items.' + index + '.')
      }
    }
  },
  methods: {
    showItem (index) {
      if (index === this.showId) {
        this.showId = null
      } else {
        this.showId = index
      }
    },
    showItemChild (index) {
      if (index === this.showIdChild) {
        this.showIdChild = null
      } else {
        this.showIdChild = index
      }
    },
    deleteItem (type, item, index) {
      // delete it from the database if it already exists
      if (type === 'item') {
        Inventory.deleteItem(item.id)
        .then((response) => {
          if (response.error) {
            this.validationErrors = response.message
            return
          }
          this.$emit('update-product')
          return this.$toast('Item successfuly removed.')
        })
      } else {
        Inventory.deleteVariant(item.id)
        .then((response) => {
          if (response.error) {
            this.validationErrors = response.message
            return
          }
          this.$emit('update-product')
          return this.$toast('Variant successfuly removed.')
        })
      }
    }
  },
  components: {
    CpVariantForm: require('../products/CpVariantForm.vue'),
    CpItemForm: require('../products/CpItemFormBeta.vue')
  }
}
</script>
<style lang="scss" scoped>
#variants {
  .variant-img {
    max-width: 50px;
  }
  .variant-header {
    display: flex;
    width: 100%;
    padding: 5px;
    .col {
      padding: 3px;
      flex: 1;
    }
  }
  .item-header {
    display: flex;
    width: 75%;
  }
  .item {
    margin-left: 25px;
    margin-top: 40px;
  }
  .cp-accordion-head{
  }
  .right{
    text-align: right;
  }
}
</style>
