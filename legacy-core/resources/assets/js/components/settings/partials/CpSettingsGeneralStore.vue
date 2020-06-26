<template lang="html">
    <div class="store-wrapper">
        <div class="cp-accordion">
            <div class="cp-accordion-head black-header">
                <h5>General Store Settings</h5>
            </div>
            <div class="cp-accordion-body">
                <div class="cp-accordion-body-wrapper">
                    <div class="cp-left-col">
                      <h4>Store Options</h4>
                      <hr />
                      <div class="line-wrapper">
                          <label>Use the Built-In Store</label>
                          <input class="toggle-switch" type="checkbox" v-model="general_store_settings.use_built_in_store.show">
                      </div>
                      <div class="line-wrapper">
                          <label>Admin can Use the Store Builder</label>
                          <input class="toggle-switch" type="checkbox" v-model="general_store_settings.store_builder_admin.show">
                      </div>
                      <div class="line-wrapper">
                          <label>Reseller can Use the Store Builder </label>
                          <input class="toggle-switch" type="checkbox" v-model="general_store_settings.store_builder_reseller.show">
                      </div>
                      <div class="line-wrapper">
                        <label>Reseller about me page</label>
                        <input class="toggle-switch" type="checkbox" v-model="general_store_settings.about_rep.show">
                      </div>
                      <div class="line-wrapper">
                          <label>Allow Self Pick Up on Wholesale Orders</label>
                          <input class="toggle-switch" type="checkbox" v-model="general_store_settings.self_pickup_wholesale.show">
                      </div>
                      <div>
                          <div class="line-wrapper">
                              <label>Allow Wholesale Low Inventory Display</label>
                              <input class="toggle-switch" type="checkbox" v-model="general_store_settings.wholesale_low_inventory.show">
                          </div>
                          <div class="line-wrapper" v-if="general_store_settings.wholesale_low_inventory.show">
                            <label>Enter Minimum Amount</label>
                              <cp-input-mask
                                custom-class="input-class"
                                type="number"
                                mask="#####"
                                v-model="general_store_settings.wholesale_low_inventory.value"></cp-input-mask>
                          </div>
                      </div>
                      <div>
                          <div class="line-wrapper">
                              <label>Allow Retail Low Inventory Display</label>
                              <input class="toggle-switch" type="checkbox" v-model="general_store_settings.retail_low_inventory.show">
                          </div>
                          <div class="line-wrapper" v-if="general_store_settings.retail_low_inventory.show">
                              <label>Enter Minimum Amount</label>
                              <div>
                                <cp-input-mask
                                  custom-class="input-class"
                                  type="number"
                                  mask="#####"
                                  v-model="general_store_settings.retail_low_inventory.value"></cp-input-mask>
                              </div>
                          </div>
                      </div>
                    </div>
                    <div class="cp-right-col">
                      <h4>Cart Options</h4>
                      <hr />
                      <div class="line-wrapper">
                          <label>Require Minimum on Wholesale Shopping Cart</label>
                          <input class="toggle-switch" type="checkbox" v-model="general_store_settings.wholesale_cart_min.show">
                      </div>
                      <div class="line-wrapper" v-if="general_store_settings.wholesale_cart_min.show">
                        <input type="radio" id="Dollar" value="dollar" v-model="general_store_settings.wholesale_cart_min.value">
                        <label for="Dollar">Dollar Amount</label>
                        <input type="radio" id="Quantity" value="quantity" v-model="general_store_settings.wholesale_cart_min.value">
                        <label for="Quantity">Total Item Quantity</label>
                      </div>
                      <div class="line-wrapper" v-if="general_store_settings.wholesale_cart_min.show">
                          <label>Enter Amount</label>
                          <div class="">
                            <span v-if="general_store_settings.wholesale_cart_min.value === 'dollar'">$</span>
                            <input class="input-class" type="number" v-model="general_store_settings.wholesale_cart_min_amount.value">
                          </div>
                      </div>
                      <h4>Product Options</h4>
                      <hr />
                      <div class="line-wrapper">
                          <label>Use Variant Claim Number</label>
                          <input class="toggle-switch" type="checkbox" v-model="general_store_settings.variant_claim_number.show">
                      </div>
                      <div v-if="general_store_settings.variant_claim_number.show"><input class="cp-button-standard" type="button" @click="showConfirm = true" value="Update Variants"></div>
                      <section class="cp-modal-standard" v-if="showConfirm">
                        <div class="cp-modal-body">
                          <div>
                            Are you sure you want to update variants to use claim numbers? This will overwrite a variant name.
                          </div>
                          <br/>
                          <div>
                            <button class="cp-button-standard" @click="showConfirm = false">Cancel</button> <button class="cp-button-standard confirm" @click="updateClaimNumbers()">Confirm</button>
                          </div>
                        </div>
                      </section>

                    </div>
                    <div class="save-settings-button">
                        <input class="cp-button-standard" type="button" @click="saveGeneralStoreSettings()" value="Save">
                    </div>
                </div>
            </div>
            <br>
        </div>
    </div>
</template>

<script>
const Settings = require('../../../resources/settings.js')

module.exports = {
  data: function () {
    return {
      closed: true,
      showConfirm: false,
      general_store_settings: {
        new_product_create: {},
        store_builder_admin: {},
        store_builder_reseller: {},
        title_store: {},
        use_built_in_store: {},
        variant_claim_number: {},
        wholesale_cart_min: {},
        wholesale_cart_min_amount: {},
        wholesale_low_inventory: {},
        retail_low_inventory: {},
        self_pickup_wholesale: {},
        about_rep: {}
      }
    }
  },
  computed: {},
  mounted () {
    this.getGeneralStoreSettings()
  },
  methods: {
    getGeneralStoreSettings: function () {
      Settings.getGeneralStoreSettings()
        .then((response) => {
          this.general_store_settings = response
        })
    },
    saveGeneralStoreSettings: function () {
      Settings
        .update(this.general_store_settings)
        .then((response) => {
          this.$updateGlobal(this.general_store_settings)
          this.$toast('Store settings saved successfully.')
        })
    },
    updateClaimNumbers: function () {
      Settings.updateVariantClaimNumber()
        .then((response) => {
          this.showConfirm = false
          if (!response.error) {
            this.$toast('Variant claim numbers updated.', {dismiss: false})
          }
        })
    }
  },
  components: {
    CpInputMask: require('../../../cp-components-common/inputs/CpInputMask.vue')
  }
}
</script>

<style lang="sass">
    @import "resources/assets/sass/var.scss";
    .store-wrapper {
        .confirm {
          float: right;
        }
        .cp-accordion-head {
            &.black-header {
                position: relative;
                padding: 10px;
                height: auto;
                h5 {
                    margin: 0;
                    display: inline-block;
                    margin-right: 0px;
                    font-weight: 300;
                    font-size: 1.2em;
                }
                background-color: $cp-main;
                color: $cp-main-inverse;
            }
        }
        .cp-left-col {
            width: 48%;
        }
        .cp-right-col {
            width: 48%;
        }
        .line-wrapper {
            display: flex;
            -webkit-display: flex;
            justify-content: space-between;
            -webkit-justify-content: space-between;
            label {
                font-size: 14px;
                font-weight: 300;
                margin-bottom: 0;
            }
            .input-class {
                height: 100%;
                width: 50%;
                height: 30px;
                text-indent: 10px;
                margin: 5px 0;

            }
            &.toggle-switch {
                width: 40px;
            }
        }
    }
</style>
