<template>
  <div class="tab-wrapper">
    <div :class="customClass">
      <!-- DESKTOP TABS -->
        <button
          v-for="item in tabItems"
          :class="{ active: item.active }"
          @click="setActiveItem(item), updateValue(item[keyValue['value']]), callback(item[keyValue['value']])">{{ item.name }}</button>
          <div class="tab-select-options" v-if="showSelect">
            <cp-select
            @input="updateValue(value)"
            :options='selectBoxOptions'
            :key-value="{ name: 'name' , value: 'name' }"></cp-select>
          </div>
        <!-- MOBILE SELECT BOX -->
    </div>
    <div class="mobile-tabs">
      <cp-select
        label="Filter:"
        :options='items'
        :key-value="{ name: 'name' , value: 'name' }"
        @input="updateValue(mobileSelectedItem), callback(mobileSelectedItem)"
        v-model="mobileSelectedItem"></cp-select>
    </div>
  </div>
</template>
<script>
module.exports = {
  data: () => ({
    mobileSelectedItem: 'OPEN ORDERS',
    tabItems: []
  }),
  props: {
    showSelect: {
      type: Boolean,
      required: false,
      default: false
    },
    selectBoxOptions: {
      type: Array,
      required: false,
      default: null
    },
    selectValue: {
      type: String,
      required: false,
      default: null
    },
    keyValue: {
      type: Object,
      default () {
        return {
          name: 'name',
          value: 'name'
        }
      }
    },
    customClass: {
      default () {
        return 'cp-tabs-standard'
      }
    },
    items: {
      type: Array,
      required: true
    },
    callback: {
      type: Function,
      required: true
    }
  },
  mounted () {
    this.mobileSelectedItem = this.items[0][this.keyValue['value']]
    this.tabItems = this.items
  },
  methods: {
    updateValue (value) {
      return this.$emit('input', value)
    },
    setActiveItem (item) {
      for (var i = 0; i < this.tabItems.length; i++) {
        this.tabItems[i].active = false
      }
      item.active = true
    }
  },
  components: {
    CpSelect: require('../../cp-components-common/inputs/CpSelect.vue')
  }
}
</script>
<style lang="scss" scoped>
.tab-wrapper{
  .tab-select-options {
    padding-top: 10px;
    padding-right: 5px;
    float: right;
  }
  .mobile-tabs{
    display: none;
  }
  @media (max-width: 768px) {
    .mobile-tabs{
      display: block;
    }
    .cp-tabs-standard {
      display: none;
    }
    .cp-tabs-light {
      display: none;
    }
  }
}

</style>
