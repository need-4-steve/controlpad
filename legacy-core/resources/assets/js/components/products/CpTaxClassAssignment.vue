<template lang="html">
  <div class="cp-box-standard tax-class-wrapper">
    <div class="cp-box-heading">
      TAX CLASSES
    </div>
    <div class="cp-box-body tax-inputs">
        <cp-input
        label="Tax Class"
        type="text"
        :error="validationErrors['tax_class']"
        v-model="newTaxClass"
        @input="$emit('input', newTaxClass)"></cp-input>
        <button class="cp-button-standard" @click="openTaxModal()">See Tax Classes</button>
    </div>
    <!-- tax class modal -->
    <div class="cp-modal-standard" v-show="taxClassModalShow" @click="taxClassModalShow = !taxClassModalShow">
        <div class="cp-modal-body" @click="$event.stopPropagation()">
            <table class="tax-class-table">
                <thead class="heading">
                    <tr>
                        <th>
                            <h3>Tax Code</h3>
                        </th>
                        <th>
                            <h3>Description</h3>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr><td>2038112</td><td>Annual events, regional events, training events</td></tr>
                    <tr><td>2038710</td><td>Business Kits and Materials</td></tr>
                    <tr><td>2038356</td><td>Clothing</td></tr>
                    <tr><td>2038895</td><td>Clothing accessories (hats, belts, purse, etc)</td></tr>
                    <tr><td>2038887</td><td>Cosmetics, skin care</td></tr>
                    <tr><td>2038305</td><td>Food</td></tr>
                    <tr><td>2038710</td><td>General retail items</td></tr>
                    <tr><td>2039004</td><td>Sale of gift card non-taxable</td></tr>
                    <tr><td>2038908</td><td>Supplements</td></tr>
                    <tr><td>2038112</td><td>Training and coaching</td></tr>
                </tbody>
            </table>
            <div class="tax-class-controls">
              <button class="cp-button-standard" @click="taxClassModalShow = !taxClassModalShow">Close</button>
            </div>
        </div>
    </div>
  </div>
</template>

<script>
module.exports = {
  data () {
    return {
      taxClassModalShow: false,
      newTaxClass: {}
    }
  },
  props: {
    taxClass: {},
    validationErrors: {
      default () {
        return {}
      }
    }
  },
  methods: {
    openTaxModal () {
      this.taxClassModalShow = true
      window.scrollTo(1, 1)
    }
  },
  mounted () {
    this.newTaxClass = this.taxClass
  },
  components: {
    CpInput: require('../../cp-components-common/inputs/CpInput.vue')
  }
}
</script>

<style lang="scss">
.tax-class-wrapper {
  .tax-class-controls {
    margin-top: 25px;
    text-align: right;
    width: 100%;
  }
  .tax-class-table {
    width: 100%;
  }
  .tax-inputs {
    text-align: center;
    span {
      display: inline-block;
    }
    button {
      margin-left: 5px;
    }
    padding-right: 5%;
    padding-left: 5%;
  }
}
</style>
