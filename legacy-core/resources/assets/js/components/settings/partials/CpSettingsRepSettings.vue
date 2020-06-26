<template lang="html">
    <div class="branding-wrapper" v-if="repsettingsLoading">
        <div class="cp-accordion">
            <div class="cp-accordion-head black-header">
                <h5>Rep Settings</h5>
            </div>
            <div class="cp-accordion-body">
                <div class="cp-accordion-body-wrapper">
                    <div class="cp-left-col">
                        <div>
                            <h4>Backoffice</h4>
                            <hr />
                            <div class="line-wrapper">
                                <label>Rep Title</label>
                                <input class="input-class" type="text" v-model="rep_settings.title_rep.value">
                            </div>
                            <div class="line-wrapper">
                                <label>Reps can have replicated site</label>
                                <input class="toggle-switch" type="checkbox" v-model="rep_settings.replicated_site.show">
                            </div>
                            <div class="line-wrapper">
                                <label>Reps can view orders tab</label>
                                <input class="toggle-switch" type="checkbox" v-model="rep_settings.rep_orders_tab.show">
                            </div>
                            <div class="line-wrapper">
                                <label>Reps can view sales tab</label>
                                <input class="toggle-switch" type="checkbox" v-model="rep_settings.rep_sales_tab.show">
                            </div>
                            <div class="line-wrapper">
                                <label>Reps must confirm inventory before it is transferred</label>
                                <input class="toggle-switch" type="checkbox" v-model="rep_settings.inventory_confirmation.show">
                            </div>
                            <div class="line-wrapper"  v-if="Auth.hasAnyRole('Superadmin')">
                                <label>Reps can edit their inventory</label>
                                <input class="toggle-switch" type="checkbox" @change="confirmEditInventory(rep_settings.rep_edit_inventory.show)" v-model="rep_settings.rep_edit_inventory.show">
                                <section class="cp-modal-standard" v-if="showConfirm">
                                  <div class="cp-modal-body">
                                    <div class="line-wrapper">
                                      <p>This setting is intended to be used temporarily, however, there may be specific needs applicable to a unique situation. By turning on this setting please note the following:
                                      </br>
                                      </br>
                                      &#9679; Inventory will not properly write over to the the commission engine (if applicable)
                                      </br>
                                      &#9679; Reps may use this feature to bypass selling product in the system, which may affect tax liability
                                      </br>
                                      &#9679; There will be no visibility into changes made to the inventory numbers</p>
                                    </div>
                                    <div class="confirm">
                                      <button class="cp-button-standard" @click="showConfirm = false, rep_settings.rep_edit_inventory.show = !rep_settings.rep_edit_inventory.show">Cancel</button>
                                      <button class="cp-button-standard Confirm" @click="showConfirm = false, rep_settings.rep_edit_inventory.show = 'true', saveRepSettings()">Confirm</button>
                                    </div>
                                  </div>
                                </section>
                            </div>
                            <div class="line-wrapper">
                                <label>Reps can set custom prices for inventory</label>
                                <input class="toggle-switch" type="checkbox" v-model="rep_settings.rep_custom_prices.show">
                            </div>
                            <div class="line-wrapper">
                                <label>Reps can edit their own information</label>
                                <input class="toggle-switch" type="checkbox" v-model="rep_settings.rep_edit_information.show">
                            </div>
                            <div class="line-wrapper">
                                <label>Reps can sell inventory to other reps</label>
                                <input class="toggle-switch" type="checkbox" v-model="rep_settings.rep_transfer.show">
                            </div>
                            <div class="line-wrapper">
                                <label>Grace period for subscription before system lock in days</label>
                                <input class="input-class" type="text" v-model="rep_settings.sub_grace_period.value">
                            </div>
                            <div class="line-wrapper">
                                <label>Reps have access to third party shipping link
                                  <cp-tooltip :options="{ content: 'When turned on a button for a link will appear on the all orders page and when viewing individual orders.'}"></cp-tooltip>
                                </label>
                                <input class="toggle-switch" type="checkbox" v-model="rep_settings.shipping_link.show">
                            </div>
                            <div v-if="rep_settings.shipping_link.show" class="line-wrapper">
                                <label>Shipping Link URL</label>
                                <input class="input-class" type="text" v-model="rep_settings.shipping_link.value">
                            </div>
                            <div v-if="rep_settings.shipping_link.show" class="line-wrapper">
                                <label>Shipping Link Text</label>
                                <input class="input-class" type="text" v-model="rep_settings.shipping_link_text.value">
                            </div>
                            <div class="line-wrapper">
                                <label>Reps have affiliate link
                                  <cp-tooltip :options="{ content: 'Display a link to reps that points to an affiliate store'}"></cp-tooltip>
                                </label>
                                <input class="toggle-switch" type="checkbox" v-model="rep_settings.affiliate_link.show">
                            </div>
                            <div v-if="rep_settings.affiliate_link.show">
                              <div class="line-wrapper">
                                <label>Affiliate Link URL</label>
                                <input class="input-class" type="url" v-model="rep_settings.affiliate_link.value.url">
                              </div>
                              <div class="line-wrapper">
                                <label>Display Affiliate Link On Reseller Site</label>
                                <input class="toggle-switch" type="checkbox" v-model="rep_settings.affiliate_link.value.display_on_rep_site">
                              </div>
                              <div class="line-wrapper">
                                <label>Affiliate Link Display Name</label>
                                <input class="input-class" type="text" v-model="rep_settings.affiliate_link.value.display_name">
                              </div>
                            </div>
                            <div class="line-wrapper" v-if="rep_settings.replicated_site.show">
                                <cp-custom-links-create></cp-custom-links-create>
                            </div>
                            <div v-if="rep_settings.replicated_site.show">
                              <h4>Rep subdomains</h4>
                              <hr />
                              <div class="line-wrapper">
                                <label>Subdomain blacklist<cp-tooltip :options="{ content: 'Seperate by commas without spaces, ex: rep,store'}"></cp-tooltip></label>
                              </div>
                              <textarea class="textarea" rows="5" cols="40" v-model="subdomain_blacklist"></textarea>
                            </div>
                            <div>
                              <h4>General</h4>
                              <hr />
                              <div class="line-wrapper">
                                <label>Enter the number of hours to show sold out product before hiding</label>
                                <input class="input-class" type="number" v-model="rep_settings.sold_out.value">
                              </div>
                              <br />
                              <div class="line-wrapper">
                                  <label>Show join link on store</label>
                                  <input class="toggle-switch" type="checkbox" v-model="rep_settings.store_join_link.show">
                              </div>
                              <div v-if="rep_settings.store_join_link.show" class="line-wrapper">
                                  <label>Set join link text</label>
                                  <input class="input-class" type="text" v-model="rep_settings.store_join_link.value">
                              </div>
                            </div>
                            <div>
                              <h4>Rep Locator</h4>
                              <hr />
                              <div class="line-wrapper">
                                  <label>Rep Locator</label>
                                  <input class="toggle-switch" type="checkbox" v-model="rep_settings.rep_locator_enable.value">
                              </div>
                              <section v-show="rep_settings.rep_locator_enable.value">
                                <div class="line-wrapper">
                                  <label>Search Radius</label>
                                  <input class="input-class" name="radiuslocator" type="number" minlength="1" maxlength="3" min="1" max="250" v-model="rep_settings.rep_locator_radius.value">
                                </div>
                                <div class="line-wrapper">
                                  <label>Map View</label>
                                  <input class="toggle-switch" type="checkbox" v-model="rep_settings.rep_locator_map_view.show">
                                </div>
                                <div v-if="rep_settings.rep_locator_map_view.show">
                                    <small>* If any Rep does not have a valid geo-location, they will not show up on the map view</small>
                                </div>
                                <div class="line-wrapper">
                                  <label>Number of random results</label>
                                  <input class="input-class" name="randomusers" type="number" min="1" max="50" v-model="rep_settings.rep_locator_random_users.value">
                                </div>
                                <div class="line-wrapper">
                                    <label>Number of searched results</label>
                                    <input class="input-class" type="number" name="maxresults" min="1" max="50" v-model="rep_settings.rep_locator_max_results.value">
                                </div>
                              </section>
                            </div>
                            <span v-if="Auth.hasAnyRole('Superadmin') && (rep_settings.reseller_purchase_inventory.show || rep_settings.affiliate_purchase_inventory.show)">
                            <h4>Wholesale Purchase Options</h4>
                            <hr />
                            </span>
                            <div class="line-wrapper" v-if="Auth.hasAnyRole('Superadmin') && (rep_settings.reseller_purchase_inventory.show || rep_settings.affiliate_purchase_inventory.show)">
                              <label>Allow wholesale purchase with eWallet balance</label>
                              <input class="toggle-switch" type="checkbox" v-model="rep_settings.wholesale_ewallet.show">
                            </div>
                            <div class="line-wrapper" v-if="Auth.hasAnyRole('Superadmin') && (rep_settings.reseller_purchase_inventory.show || rep_settings.affiliate_purchase_inventory.show)">
                              <label>Allow wholesale purchase with credit card on file</label>
                              <input class="toggle-switch" type="checkbox" v-model="rep_settings.wholesale_card_token.show">
                            </div>
                            <h4>Learning Management System</h4>
                            <hr />
                            <div class="line-wrapper">
                                <label>Show LMS Link on Navigation</label>
                                <input class="toggle-switch" type="checkbox" v-model="rep_settings.lms_link.show">
                            </div>
                            <div class="line-wrapper" v-show="rep_settings.lms_link.show">
                                <label>LMS Link Url</label>
                                <input class="input-class" type="text" v-model="rep_settings.lms_link.value">
                            </div>
                            <div class="line-wrapper" v-show="rep_settings.lms_link.show">
                                <label>LMS Link Display Name</label>
                                <input class="input-class" type="text" v-model="rep_settings.lms_link_name.value">
                            </div>
                        </div>
                    </div>
                    <div class="cp-right-col">
                      <div>
                        <h4>Reseller Settings</h4>
                        <hr />
                        <div class="line-wrapper">
                          <label>Resellers can create products</label>
                          <input class="toggle-switch" type="checkbox" v-model="rep_settings.reseller_create_product.show">
                        </div>
                        <div class="line-wrapper">
                          <label>Resellers can purchase inventory</label>
                          <input class="toggle-switch" type="checkbox" v-model="rep_settings.reseller_purchase_inventory.show">
                        </div>
                        <div class="line-wrapper">
                          <label>Resellers can see My Orders tab</label>
                          <input class="toggle-switch" type="checkbox" v-model="rep_settings.reseller_my_orders.show">
                        </div>
                        <div class="line-wrapper" v-if="Auth.hasAnyRole('Superadmin')">
                            <label>Turn on payment application for Resellers</label>
                            <input class="toggle-switch" type="checkbox" v-model="rep_settings.reseller_payment_option.show">
                        </div>
                        <div class="line-wrapper" v-if="Auth.hasAnyRole('Superadmin')">
                            <label>Resellers can process returns</label>
                            <input class="toggle-switch" type="checkbox" v-model="rep_settings.reseller_returns.show">
                        </div>
                        <div class="line-wrapper">
                            <label>Resellers can use coupons</label>
                            <input class="toggle-switch" type="checkbox" v-model="rep_settings.reseller_coupons.show">
                        </div>
                        <div class="line-wrapper">
                            <label>Resellers have access to the media library</label>
                            <input class="toggle-switch" type="checkbox" v-model="rep_settings.reseller_media_library.show">
                        </div>
                        <div class="line-wrapper">
                            <label>Resellers business address shows in footer on replicated sites</label>
                            <input class="toggle-switch" type="checkbox" v-model="rep_settings.reseller_address_store.show">
                        </div>
                        <div class="line-wrapper" v-if="Auth.hasAnyRole('Superadmin')">
                            <label>Resellers have access to YouTube integration</label>
                            <input class="toggle-switch" type="checkbox" v-model="rep_settings.reseller_youtube.show">
                        </div>
                        <div class="line-wrapper">
                            <label>Resellers can set their own logo</label>
                            <input class="toggle-switch" type="checkbox" v-model="rep_settings.reseller_logo.show">
                        </div>
                        <div class="line-wrapper">
                            <label>Resellers have access to Custom Order</label>
                            <input class="toggle-switch" type="checkbox" v-model="rep_settings.reseller_custom_order.show">
                        </div>
                        <div class="line-wrapper">
                            <label>Resellers have access to Corporate inventory in custom order</label>
                            <input class="toggle-switch" type="checkbox" v-model="rep_settings.reseller_custom_corp.show">
                        </div>
                        <div class="line-wrapper">
                            <label>Resellers can allow Self Pickup Orders</label>
                            <input class="toggle-switch" type="checkbox" v-model="rep_settings.self_pickup_reseller.show">
                        </div>
                        <span  v-if="Auth.hasAnyRole('Superadmin')">
                        <h4>Reseller eWallet Settings</h4>
                        <hr />
                        </span>
                        <div class="line-wrapper" v-if="Auth.hasAnyRole('Superadmin')">
                          <label>eWallet for Resellers</label>
                          <input class="toggle-switch" type="checkbox" v-model="rep_settings.reseller_ewallet.show">
                        </div>
                          <div class="line-wrapper">
                            <label>Show eWallet Balance for Resellers</label>
                            <input class="toggle-switch" type="checkbox" v-model="rep_settings.reseller_ewallet_balance.show">
                          </div>
                          <div class="line-wrapper">
                            <label>Show Pending eWallet Balance for Resellers</label>
                            <input class="toggle-switch" type="checkbox" v-model="rep_settings.reseller_ewallet_pending_balance.show">
                          </div>
                          <div class="line-wrapper">
                            <label>Show eWallet Commissions for Resellers</label>
                            <input class="toggle-switch" type="checkbox" v-model="rep_settings.reseller_ewallet_commission.show">
                          </div>
                          <div class="line-wrapper">
                            <label>Show eWallet Taxes for Resellers</label>
                            <input class="toggle-switch" type="checkbox" v-model="rep_settings.reseller_ewallet_taxes.show">
                          </div>
                        <div v-show="Auth.hasAnyRole('Superadmin') && rep_settings.reseller_ewallet.show">
                          <div class="line-wrapper">
                            <label>eWallet balance withdraw</label>
                            <input class="toggle-switch" type="checkbox" v-model="rep_settings.reseller_ewallet_withdraw.show">
                          </div>
                          <div class="line-wrapper" v-if="rep_settings.reseller_ewallet_withdraw.show">
                            <label>Pay taxes first before being able to withdraw balance</label>
                            <input class="toggle-switch" type="checkbox" v-model="rep_settings.reseller_ewallet_taxes_paid_first.show">
                          </div>
                          <div class="line-wrapper">
                            <label>eWallet paying taxes with balance</label>
                            <input class="toggle-switch" type="checkbox" v-model="rep_settings.reseller_ewallet_taxes_balance.show">
                          </div>
                          <div class="line-wrapper">
                            <label>eWallet paying taxes with credit card</label>
                          <input class="toggle-switch" type="checkbox" v-model="rep_settings.reseller_ewallet_taxes_credit_card.show">
                          </div>
                        </div>
                      </div>
                      <div>
                        <h4>Affiliate Settings</h4>
                        <hr />
                        <div class="line-wrapper">
                          <label>Affiliates can create products</label>
                          <input class="toggle-switch" type="checkbox" v-model="rep_settings.affiliate_create_product.show">
                        </div>
                        <div class="line-wrapper">
                          <label>Affiliates can purchase inventory</label>
                          <input class="toggle-switch" type="checkbox" v-model="rep_settings.affiliate_purchase_inventory.show">
                        </div>
                        <div class="line-wrapper" v-if="Auth.hasAnyRole('Superadmin')">
                            <label>Turn on payment application for Affiliates</label>
                            <input class="toggle-switch" type="checkbox" v-model="rep_settings.affiliate_payment_option.show">
                        </div>
                        <div class="line-wrapper" v-if="Auth.hasAnyRole('Superadmin')">
                            <label>Affiliates can process returns</label>
                            <input class="toggle-switch" type="checkbox" v-model="rep_settings.affiliate_returns.show">
                        </div>
                        <div class="line-wrapper">
                            <label>Affiliates have access to the media library</label>
                            <input class="toggle-switch" type="checkbox" v-model="rep_settings.affiliate_media_library.show">
                        </div>
                        <div class="line-wrapper" v-if="Auth.hasAnyRole('Superadmin')">
                            <label>Affiliates have access to YouTube integration</label>
                            <input class="toggle-switch" type="checkbox" v-model="rep_settings.affiliate_youtube.show">
                        </div>
                        <div class="line-wrapper">
                            <label>Affiliates have access to Shipping Rates</label>
                            <input class="toggle-switch" type="checkbox" v-model="rep_settings.affiliate_shipping_rates.show">
                        </div>
                        <div class="line-wrapper">
                            <label>Affiliates have access to Custom Order</label>
                            <input class="toggle-switch" type="checkbox" v-model="rep_settings.affiliate_custom_order.show">
                        </div>
                        <div class="line-wrapper">
                            <label>Affiliates have access to Corporate inventory in custom order</label>
                            <input class="toggle-switch" type="checkbox" v-model="rep_settings.affiliate_custom_corp.show">
                        </div>
                        <span v-if="Auth.hasAnyRole('Superadmin')">
                        <h4>Affiliate eWallet Settings</h4>
                        <hr />
                        </span>
                        <div class="line-wrapper" v-if="Auth.hasAnyRole('Superadmin')">
                          <label>Turn on eWallet for Affiliates</label>
                          <input class="toggle-switch" type="checkbox" v-model="rep_settings.affiliate_ewallet.show">
                        </div>
                        <div v-show="Auth.hasAnyRole('Superadmin') && rep_settings.affiliate_ewallet.show">
                          <div class="line-wrapper">
                            <label>Show eWallet Balance for Affiliates</label>
                            <input class="toggle-switch" type="checkbox" v-model="rep_settings.affiliate_ewallet_balance.show">
                          </div>
                          <div class="line-wrapper">
                            <label>Show Pending eWallet Balance for Affiliates</label>
                            <input class="toggle-switch" type="checkbox" v-model="rep_settings.affiliate_ewallet_pending_balance.show">
                          </div>
                          <div class="line-wrapper">
                            <label>Show eWallet Commissions for Affiliates</label>
                            <input class="toggle-switch" type="checkbox" v-model="rep_settings.affiliate_ewallet_commission.show">
                          </div>
                          <div class="line-wrapper">
                            <label>Show eWallet Taxes for Affiliates</label>
                            <input class="toggle-switch" type="checkbox" v-model="rep_settings.affiliate_ewallet_taxes.show">
                          </div>
                          <div class="line-wrapper">
                            <label>eWallet balance withdraw for Affiliates</label>
                            <input class="toggle-switch" type="checkbox" v-model="rep_settings.affiliate_ewallet_withdraw.show">
                          </div>
                          <div class="line-wrapper" v-if="rep_settings.affiliate_ewallet_withdraw.show">
                            <label>Pay taxes first before being able to withdraw balance</label>
                            <input class="toggle-switch" type="checkbox" v-model="rep_settings.affiliate_ewallet_taxes_paid_first.show">
                          </div>
                          <div class="line-wrapper">
                            <label>eWallet paying taxes with balance</label>
                            <input class="toggle-switch" type="checkbox" v-model="rep_settings.affiliate_ewallet_taxes_balance.show">
                          </div>
                          <div class="line-wrapper">
                            <label>eWallet paying taxes with credit card</label>
                            <input class="toggle-switch" type="checkbox" v-model="rep_settings.affiliate_ewallet_taxes_credit_card.show">
                          </div>
                        </div>
                      </div>
                    </div>
                    <div class="save-settings-button">
                      <input class="cp-button-standard" type="button" @click="saveRepSettings()" value="Save">
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
const Settings = require('../../../resources/settings.js')
const Auth = require('auth')

module.exports = {
  data () {
    return {
      closed: true,
      Auth,
      repsettingsLoading: false,
      subdomain_blacklist: '',
      new_subdomain_list: [],
      showConfirm: false,
      rep_settings: {
        // reseller settlings
        reseller_address_store: {},
        reseller_create_product: {},
        reseller_ewallet: {},
        reseller_ewallet_pending_balance: {},
        reseller_ewallet_withdraw: {},
        reseller_payment_option: {},
        reseller_coupons: {},
        reseller_returns: {},
        reseller_media_library: {},
        reseller_ewallet_taxes_paid_first: {},
        reseller_ewallet_taxes_balance: {},
        reseller_ewallet_taxes_credit_card: {},
        reseller_rep_retail: {},
        self_pickup_reseller: {},
        rep_transfer: {},

        // affiliate settings
        affiliate_create_product: {},
        affiliate_purchase_inventory: {},
        affiliate_ewallet: {},
        affiliate_ewallet_pending_balance: {},
        affiliate_ewallet_withdraw: {},
        affiliate_media_library: {},
        affiliate_payment_option: {},
        affiliate_returns: {},
        affiliate_ewallet_taxes_paid_first: {},
        affiliate_ewallet_taxes_balance: {},
        affiliate_ewallet_taxes_credit_card: {},
        affiliate_rep_retail: {},

        // general rep settings
        title_rep: {},
        rep_welcome: {},
        replicated_site: {},
        rep_orders_tab: {},
        rep_sales_tab: {},
        rep_custom_prices: {},
        rep_edit_information: {},
        inventory_confirmation: {},
        sub_grace_period: {},
        subdomain_blacklist: {},
        sold_out: {},
        store_join_link: {},
        lms_link: {},
        lms_link_name: {},
        // locator settings
        rep_locator_enable: {},
        rep_locator_radius: {},
        rep_locator_map_view: {},
        rep_locator_random_users: {},
        rep_locator_max_results: {},
        shipping_link: {}
      }
    }
  },
  computed: {},
  mounted () {
    this.getRepSettings()
    this.getBlacklistNames()
  },
  methods: {
    getRepSettings: function () {
      this.repsettingsLoading = false
      Settings.getRepSettings()
        .then((response) => {
          if (response.error) {
            return
          }
          this.rep_settings = response
          this.repsettingsLoading = true
        })
    },
    getBlacklistNames: function () {
      Settings.getBlacklist()
        .then((response) => {
          this.subdomain_blacklist = response
        })
    },
    saveRepSettings: function () {
      if (this.rep_settings.rep_locator_radius.value > 250) {
        return this.$toast('Rep Locator Radius must be 250 miles or less.', {error: true})
      }
      if (this.rep_settings.rep_locator_radius.value < 1) {
        return this.$toast('Rep Locator Radius must be at least 1 mile.', {error: true})
      }
      Settings
        .update(this.rep_settings)
        .then((response) => {
          this.$updateGlobal(this.rep_settings)
          this.$toast('Rep settings saved successfully.')
          this.saveBlacklist()
        })
    },
    saveBlacklist: function () {
      this.new_subdomain_list = []
      this.new_subdomain_list.push(this.subdomain_blacklist)
      Settings
        .updateBlacklist(this.new_subdomain_list)
        .then((response) => {
          this.getBlacklistNames()
          if (response.error) {
            this.$toast('Something went wrong. Please try again or contact support.')
          }
        })
    },
    confirmEditInventory: function (canEdit) {
      if (canEdit) {
        this.showConfirm = true
      }
    }
  },
  components: {
    'CpConfirm': require('../../../cp-components-common/CpConfirm.vue'),
    CpCustomLinksCreate: require('../../custom-links/CpCustomLinksCreate.vue'),
    CpTooltip: require('../../../custom-plugins/CpTooltip.vue')

  }
}
</script>

<style lang="scss">
    @import "resources/assets/sass/var.scss";
    .branding-wrapper {
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
        .textarea {
            box-sizing: border-box;
            border: 1px solid #ddd;
            margin: 2px;
            padding: 6px;
            width: 100%;
        }
        .Confirm {
            float: right;
        }
    }
</style>
