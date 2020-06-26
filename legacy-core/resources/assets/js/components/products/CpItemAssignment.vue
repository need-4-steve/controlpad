<template lang="html">
  <div class="cp-box-standard add-items-wrapper">
    <div class="cp-box-heading">
      ADD ITEMS
    </div>
    <div class="cp-box-body add-item-body">
      <div :class="{ 'new-item-error': validationErrors['items'] }">
        <cp-item-form :validation-errors="validationErrors" :item="newItem"></cp-item-form>
      </div>
      <span :class="{ 'cp-validation-errors': validationErrors['items'] }" v-if="validationErrors['items']">{{ validationErrors['items'][0] }}</span>
      <div class="add-item-wrapper">
        <button class="cp-button-standard" @click="addItem(newItem)">Add Item</button>
      </div>
      <div class="added-items">
        <h4>ADDED ITEMS</h4>
        <hr />
        <div class="cp-accordion" v-for="(item, index) in newAddedItems" @change="updateItems()">
            <div class="cp-accordion-head" :class="{ 'item-error': itemError(index) }" @click="showItem(index)">
              <h5>Manufacturer SKU: {{item.manufacturer_sku}}</h5>
              <span class="arrow" v-if="showId !== index"><i class="mdi mdi-chevron-down"></i></span>
              <span class="arrow" v-if="showId === index"><i class="mdi mdi-chevron-up"></i></span>
            </div>
            <div class="cp-accordion-body" :class="{ closed: showId !== index }">
                <div class="cp-accordion-body-wrapper">
                  <div class="delete-or-default">
                    <div>
                      <input
                      type="checkbox"
                      :id="index"
                      :checked="item.is_default"
                      @click="addDefault(item, index, $event)">
                      <label>Default Item</label>
                    </div>
                    <i class="mdi mdi-close pointer right" @click="removeItem(item, index)"></i>
                  </div>
                  <cp-item-form :validation-errors="validationErrors" :item-index="index" :item="item"></cp-item-form>
                </div>
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
      newItem: {
        premium_price: {
          price: null
        },
        msrp: {
          price: null
        },
        wholesale_price: {
          price: null
        }
      },
      showId: null,
      newAddedItems: []
    }
  },
  props: {
    items: {
      default () {
        return {}
      }
    },
    addedItems: {
      type: Array,
      default () {
        return []
      }
    },
    validationErrors: {
      default () {
        return {}
      }
    }
  },
  mounted () {
    this.newAddedItems = this.addedItems
  },
  computed: {
    itemError () {
      return function (index) {
        return JSON.stringify(this.validationErrors).includes('items.' + index + '.')
      }
    }
  },
  methods: {
    updateItems () {
      this.$emit('input', this.newAddedItems)
    },
    restrictChar (event) {
      if (event.keyCode > 185 && event.keyCode < 223) {
        event.preventDefault()
      }
    },
    showItem (index) {
      if (index === this.showId) {
        this.showId = null
      } else {
        this.showId = index
      }
    },
    addItem (item) {
      if (item) {
        let newItem = JSON.parse(JSON.stringify(item))
        if (this.addedItems.length < 1) {
          newItem.is_default = true
        } else {
          newItem.is_default = false
        }
        this.newAddedItems.push(newItem)
        this.$emit('input', this.newAddedItems)
      }
    },
    removeItem (item, index) {
      var itemIsDefualt = false
      if (item && item.is_default === true && this.newAddedItems.length > 0 || item && item.is_default === 1 && this.newAddedItems.length > 0) {
        itemIsDefualt = true
      }
      // delete it from the database if it already exists
      if (this.newAddedItems[index].id) {
        this.deleteItem(this.newAddedItems[index].id, index)
      } else {
        this.newAddedItems.splice(index, 1)
        this.$emit('input', this.newAddedItems)
      }
      if (itemIsDefualt && this.newAddedItems[0]) {
        this.addDefault(this.newAddedItems[0], 0)
      }
    },
    /* deletes exisiting itme fom database */
    deleteItem (id, index) {
      Inventory.deleteItem(id)
        .then((response) => {
          if (!response.error) {
            this.newAddedItems.splice(index, 1)
            this.$emit('input', this.newAddedItems)
            this.$toast('Successfully removed item from system.', { dismiss: false })
          }
        })
    },
    addDefault (item, index, event) {
      if (item && item.is_default) {
        event.preventDefault()
        return
      }
      item.is_default = true
      for (var i = 0; i < this.newAddedItems.length; i++) {
        if (index !== i) {
          this.newAddedItems[i].is_default = false
        }
      }
      this.$emit('input', this.newAddedItems)
    }
  },
  components: {
    CpInput: require('../../cp-components-common/inputs/CpInput.vue'),
    CpItemForm: require('../products/CpItemForm.vue')
  }
}
</script>

<style lang="scss">
.add-items-wrapper {
  .delete-or-default {
    padding: 5px;
    display: flex;
    width: 100%;
    div, i {
      flex: 1;
    }
    i {
      width: 100%;
      text-align: right;
    }
    div {
      input {
        display: inline;
        width: 20px;
        height: 15px;
      }
      label {
        display: inline;
      }
    }
  }
  .item-error {
    color: tomato;
  }
  .new-item-error {
    border: 1px solid tomato;
  }
  .add-item-body {
    overflow-x: hidden;
  }
  .add-item-wrapper {
    float: right;
  }
  .added-items {
    hr {
      margin-top: 5px;
    }
    padding: 10px;
  }
}

</style>
