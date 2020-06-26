<template lang="html">
  <div class="wholesale-nav-wrapper">
    <div v-if="!displayError">
      <div class="button-wrapper">
        <button  class="cp-button-standard" @click="findCart()"><i class="mdi mdi-cart"></i></button>
      </div>
      <cp-tabs v-if='items.length > 0'
        :items="items"
        :callback="navController"></cp-tabs>
        <component v-if="tabsAreSet" :is="currentView" v-bind="{cartType, inventoryUserPid}" :key="cartType"></component>
      </div>
      <div v-else><strong class="errorText">{{ displayError}}</strong></div>
  </div>
</template>

<script>
const CartUtility = require('../../libraries/CartUtility.js')
const Auth = require('auth')

module.exports = {
  routing: [
    {
      name: 'site.CpWholesaleNav',
      path: 'inventory/purchase',
      meta: {
        title: 'Purchase Inventory'
      }
    },
    {
      name: 'site.CpCustomOrder',
      meta: {
        title: 'Custom Orders'
      },
      path: 'orders/custom'
    },
    {
      name: 'site.CpPersonalUse',
      meta: {
        title: 'Personal Use'
      },
      path: 'inventory/personal'
    },
    {
      name: 'site.CpRepTransfer',
      meta: {
        title: 'Sell to Rep'
      },
      path: 'inventory/rep-transfer'
    }
  ],
  data () {
    return {
      products: true,
      bundles: false,
      fbc: false,
      personal: true,
      corporate: false,
      items: [],
      affiliate: true,
      cartObject: {},
      company: this.$getGlobal('company_name').value.toUpperCase(),
      currentView: 'cp-wholesale-products',
      cartType: 'custom_personal',
      inventoryUserPid: null,
      tabsAreSet: false,
      cartPid: null,
      creatingCart: false,
      displayError: null
    }
  },
  props: {
    orderType: {
      default () {
        return this.$pathParameterName()
      }
    }
  },
  mounted () {
    this.setTabs()
  },
  methods: {
    setTabs () {
      let userRole = Auth.getClaims().role
      let updatedCartType = null

      switch (this.orderType) {
        case 'purchase':
          if (!Auth.getClaims().perm['core:buy']) {
            this.displayError = "Buying disabled. Contact support if this was received in error."
            return false
          }
          updatedCartType = 'wholesale'
          break;
        case 'custom':
          if (!Auth.getClaims().perm['core:sell']) {
            this.displayError = "Selling disabled. Contact support if this was received in error."
            return false
          }
          if (userRole === 'Rep') {
            updatedCartType = 'custom-retail'
          }
          if (userRole === 'Superadmin' || userRole === 'Admin') {
            updatedCartType = 'custom-corp'
          }
          break;
        case 'personal':
          updatedCartType = 'custom-personal'
          break;
        case 'rep-transfer':
          updatedCartType = 'rep-transfer'
          break;
      }

      this.cartType = updatedCartType
      this.inventoryUserPid = CartUtility.getCartSetup(this.cartType).inventoryUserPid

      if (this.orderType === 'purchase') {
        this.items.push({ name: 'PRODUCTS', active: true })
        this.items.push({ name: 'PACKS', active: false })
      }
      if (this.orderType === 'custom') {
        if (this.isAffiliate() && (this.$getGlobal('affiliate_create_product').show || this.$getGlobal('affiliate_purchase_inventory').show)) {
          this.items.push({ name: 'MY INVENTORY', active: false })
        }

        if (this.isAffiliate() && this.$getGlobal('affiliate_custom_corp').show) {
          this.items.push({ name: this.company + ' INVENTORY', active: false })
        }

        if (!this.isAffiliate() && !Auth.hasAnyRole('Superadmin', 'Admin')) {
          this.items.push({ name: 'MY INVENTORY', active: false })
        }

        if (!this.isAffiliate() && !Auth.hasAnyRole('Superadmin', 'Admin') && this.$getGlobal('reseller_custom_corp').show) {
          this.items.push({ name: this.company + ' INVENTORY', active: false })
        }

        if (Auth.hasAnyRole('Superadmin', 'Admin')) {
          this.items.push({ name: 'MY INVENTORY', active: false })
        }
      } else if (this.orderType === 'personal' || this.orderType === 'rep-transfer') {
        this.items.push({ name: 'MY INVENTORY', active: true })
      }

      this.items[0].active = true
      this.navController(this.items[0].name)
      this.tabsAreSet = true
    },
    findCart () {
      if (!this.cartPid || !this.cartPid.length > 0) {
        CartUtility.getCartPid(this.cartType)
          .then(pid => {
            this.cartPid = pid
            this.$router.push({
              path: '/carts/' + this.cartPid
            })
          })
          .catch(err => {
            console.log('Error happend at find cart')
            if (err.prototype.message === 'Already Creating Cart') {
              this.findCart()
            } else {
              console.log(err)
            }
          })
      } else {
        this.$router.push({
          path: '/carts/' + this.cartPid
        })
      }
    },
    isAffiliate () {
      let claims = Auth.getClaims()
      let sellerType = claims.sellerType
      if (sellerType === 'Affiliate' && !Auth.hasAnyRole('Superadmin', 'Admin')) {
        return true
      }
      return false
    },
    navController (tab) {
      let userRole = Auth.getClaims().role

      switch (tab) {
        case 'PRODUCTS':
          this.currentView = 'cp-wholesale-products'
          this.cartType = 'wholesale'
          break
        case 'PACKS':
          this.currentView = 'cp-wholesale-bundles'
          this.cartType = 'wholesale'
          break
        case this.company + ' INVENTORY':
          this.currentView = 'cp-wholesale-products'
          this.cartType = 'custom-affiliate'
          break
        case 'MY INVENTORY':
          this.currentView = 'cp-wholesale-products'
          if (this.orderType === 'personal') {
            this.cartType = 'custom-personal'
          } else if (this.orderType === 'rep-transfer') {
            this.cartType = 'rep-transfer'
          } else if (userRole === 'Rep') {
            this.cartType = 'custom-retail'
          } else if (userRole === 'Superadmin' || userRole === 'Admin') {
            this.cartType = 'custom-corp'
          }
          break
        default:
      }
      this.inventoryUserPid = CartUtility.getCartSetup(this.cartType).inventoryUserPid
    }
  },
  components: {
    CpTabs: require('../../cp-components-common/navigation/CpTabs.vue'),
    CpWholesaleProducts: require('../../components/store/CpWholesaleProducts.vue'),
    CpWholesaleBundles: require('../../components/store/CpWholesaleBundles.vue')
  }
}
</script>

<style lang="scss">
.wholesale-nav-wrapper {
  .button-wrapper {
    display: flex;
    justify-content: flex-end;
  }
  .cp-table-standard {
    & > tbody > tr {
      &:nth-child(odd) {
        cursor: pointer;
        &:hover {
          background: lightgrey;
        }
      }
    }
    .add-to-cart {
      float: right
    }
    .item-table {
      width: 100%;
      margin: 0px auto 0px auto;
      th {
        background-color: transparent;
        font-weight: bold;
        color: black;
        text-align: center;
      }
      td {
        text-align: center;
      }
    }
  }
}
</style>
