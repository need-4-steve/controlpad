<template lang="html">
  <div class="cp-box-standard">
    <div class="cp-box-heading">
      ASSIGN MINIMUM AND MAXIMUM QUANTITIES AVAILABLE FOR PURCHASE (optional)
    </div>
    <div class="cp-box-body min-max-grid">
      <div class="col">
        <cp-input
        label="Min"
        type="number"
        :error="validationErrors['min']"
        v-model="newMin"
        @input="$emit('min', newMin)"></cp-input>
      </div>
      <div class="col">
        <cp-input
        label="Max"
        type="number"
        :error="validationErrors['max']"
        v-model="newMax"
        @input="$emit('max', newMax)"></cp-input>
      </div>
      </div>
    </div>
  </div>
</template>

<script>
module.exports = {
  data () {
    return {
      newMin: null,
      newMax: null
    }
  },
  mounted: function () {
    this.newMin = this.min
    this.newMax = this.max
  },
  props: {
    min: {
      default () {
        return null
      }
    },
    max: {
      default () {
        return null
      }
    },
    validationErrors: {
      default () {
        return {}
      }
    }
  },
  components: {
    CpInput: require('../../cp-components-common/inputs/CpInput.vue')
  }
}
</script>

<style lang="scss">
.min-max-grid {
  display: flex;
  .col {
    padding: 5px;
    flex: 1;
  }
}
</style>
