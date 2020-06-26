<template>
  <nav class="main-menu-nav-scope" ref="nav">
    <h2>
      <img :src="$getGlobal('back_office_logo_inverse').value" />
    </h2>
    <ul>
      <li v-if="isImpersonating">
        <a href="javascript:void(0)" class="mdi-chevron-left" @click="revertLoginAs()">Return to Admin</a>
      </li>
      <li data-root="/dashboard" v-if="dashboardVisible"><a href="/dashboard" class="mdi-speedometer">Dashboard</a></li>
      <li data-root="/sales" v-if="$getGlobal('rep_sales_tab').show && hasAnyRole('Rep')"><a href="/sales" class="mdi-chart-bar">Sales</a></li>
      <li data-root="/reports" v-if="hasAnyRole('Admin', 'Superadmin')">
        <span class="mdi-chart-bar">Reports</span>
        <section>
          <ul>
            <li><a href="/reports/financial">Sales Reports</a></li>
            <li v-if="hasAnyRole('Superadmin')"><a href="/reports/subscriptions">Subscription Reports</a></li>
            <li><a href="/reports/emails">Emails Reports</a></li>
            <li><a href="/reports/customers">Customers Report</a></li>
          </ul>
        </section>
      </li>
      <li data-root="/history" v-if="hasAnyRole('Superadmin')">
        <a href="/history" class="mdi-history">History</a>
      </li>
      <li data-root="/autoship" v-if="hasAnyRole('Superadmin', 'Admin') && ($getGlobal('autoship_enabled').show || $getGlobal('autoship_retail').show)">
        <span class="mdi-truck-fast">{{$getGlobal('autoship_display_name').value}}</span>
        <section>
          <ul>
            <li><a href="/autoship">{{ $getGlobal('autoship_display_name').value }} Plans</a></li>
            <li><a href="/autoship/subscriptions">{{$getGlobal('autoship_display_name').value}} Subscriptions</a></li>
          </ul>
        </section>
      </li>
      <li data-root="/autoship" v-if="hasAnyRole('Rep') && $getGlobal('autoship_enabled').show">
        <span class="mdi-truck-fast">{{$getGlobal('autoship_display_name').value}}</span>
        <section>
          <ul>
            <li v-if="$getGlobal('autoship_enabled').show">
              <a href="/inventory/autoship">My {{$getGlobal('autoship_display_name').value}}</a>
            </li>
            <li v-if="$getGlobal('reseller_my_orders').show">
              <a href="/inventory/rep-orders">My Orders</a>
            </li>
            <li v-if="userStatusBuy">
              <a :href="'/inventory/purchase'">{{$getGlobal('autoship_purchase_label').value}}</a>
            </li>
            <li>
              <a :href="'/customer-subscriptions'">Customer Subscriptions</a>
            </li>
          </ul>
        </section>
      </li>
      <li data-root="/inventory"
          v-if="hasAnyRole('Admin', 'Superadmin')
            || (hasAnyRole('Rep')
                && userSellerType === 'Affiliate'
                && ($getGlobal('affiliate_purchase_inventory').show || $getGlobal('affiliate_create_product').show)
            ) || hasAnyRole('Rep') && userSellerType === 'Reseller'">
        <span class="mdi-tshirt-crew">Inventory</span>
        <section>
          <ul v-if="hasAnyRole('Admin', 'Superadmin')">
            <li><a href="/inventory">Corp Inventory</a></li>
            <li><a href="/inventory/admin-rep-index">Rep Inventory</a></li>
            <li v-if="hasAnyRole('Rep')">
              <a href="/inventory/personal">Personal Use</a>
            </li>
          </ul>
          <ul v-if="hasAnyRole('Rep')
                && userSellerType === 'Affiliate'
                && ($getGlobal('affiliate_purchase_inventory').show || $getGlobal('affiliate_create_product').show)">
            <li><a href="/inventory">My Inventory</a></li>
            <li v-if="$getGlobal('affiliate_purchase_inventory').show">
              <a href="/inventory/rep-orders">My Orders</a>
            </li>
            <li v-if="$getGlobal('affiliate_purchase_inventory').show && userStatusBuy">
              <a href="/inventory/purchase">Purchase Inventory</a>
            </li>
            <li>
              <a href="/inventory/personal">Personal Use</a>
            </li>
            <li v-if="$getGlobal('autoship_enabled').show">
              <a href="/inventory/autoship">My {{$getGlobal('autoship_display_name').value}}</a>
            </li>
          </ul>
          <ul v-if="hasAnyRole('Rep') && userSellerType === 'Reseller'">
            <li><a href="/inventory">My Inventory</a></li>
            <li v-if="$getGlobal('reseller_my_orders').show">
              <a href="/inventory/rep-orders">My Orders</a>
            </li>
            <li v-if="$getGlobal('reseller_purchase_inventory').show && userStatusBuy">
              <a href="/inventory/purchase">Purchase Inventory</a>
            </li>
            <li>
              <a href="/inventory/personal">Personal Use</a>
            </li>
            <li v-if="$getGlobal('rep_transfer').show">
              <a href="/inventory/rep-transfer">{{ 'Sell to ' + $getGlobal('title_rep').value }}</a>
            </li>
          </ul>
        </section>
      </li>
      <li data-root="/orders,/invoices"
          v-if="hasAnyRole('Admin', 'Superadmin')
            || (userSellerType === 'Reseller' && $getGlobal('rep_orders_tab').show)
            || (userSellerType === 'Affiliate' && $getGlobal('affiliate_custom_order') && (
              $getGlobal('affiliate_custom_corp').show
              || $getGlobal('affiliate_create_product').show
              || $getGlobal('affiliate_purchase_inventory').show)
            )">
        <span class="mdi-dropbox">Orders</span>
        <section>
          <ul>
            <li><a href="/orders">All Orders</a></li>
            <li v-if="userStatusSell && (
                  hasAnyRole('Admin', 'Superadmin')
                  || (userSellerType === 'Affiliate' && $getGlobal('affiliate_custom_order').show)
                  || (userSellerType === 'Reseller' && $getGlobal('reseller_custom_order').show))">
              <a href="/orders/custom">Custom Order</a>
            </li>
            <li v-if="userStatusSell && (
                  hasAnyRole('Admin', 'Superadmin')
                  || (userSellerType === 'Affiliate' && $getGlobal('affiliate_custom_order').show)
                  || (userSellerType === 'Reseller' && $getGlobal('reseller_custom_order').show))">
             <a href="/invoices">Unpaid Invoices</a></li>
          </ul>
        </section>
      </li>
      <li data-root="/shipping"
          v-if="$getGlobal('enable_shipping_label_creation').show
            || (hasAnyRole('Admin', 'Superadmin')
                  || (userSellerType === 'Affiliate' && $getGlobal('affiliate_shipping_rates').show)
                  || (userSellerType === 'Reseller' && $getGlobal('replicated_site').show)
            )">
        <span class="mdi-truck">Shipping</span>
        <section>
          <ul>
            <li v-if="$getGlobal('enable_shipping_label_creation').show">
              <a href="/shipping/create-shipping-label">Create Shipping Label</a>
            </li>
            <li v-if="hasAnyRole('Admin', 'Superadmin')
                  || (userSellerType === 'Affiliate' && $getGlobal('affiliate_shipping_rates').show)
                  || (userSellerType === 'Reseller' && $getGlobal('replicated_site').show)">
              <a href="/shipping/settings">Settings</a>
            </li>
          </ul>
        </section>
      </li>
      <li data-root="/announcements" v-if="hasAnyRole('Superadmin', 'Admin', 'Rep')">
        <a href="/announcements" class="mdi-bullhorn">{{ announcementsTitle }}</a>
      </li>
      <li data-root="/coupons"
          v-if="(hasAnyRole('Admin', 'Superadmin') && $getGlobal('corp_coupons').show)
            || (userSellerType === 'Reseller' && $getGlobal('reseller_coupons').show)">
        <a href="/coupons/create" class="mdi-coin">Coupons</a>
      </li>
      <li data-root="/product,/bundles"
          v-if="hasAnyRole('Admin', 'Superadmin')
            || (userSellerType === 'Reseller' && $getGlobal('reseller_create_product').show)
            || (userSellerType === 'Affiliate' && $getGlobal('affiliate_create_product').show)">
        <span class="mdi-cube-outline">Products</span>
        <section>
          <ul>
            <li><a href="/products">All Products</a></li>
            <li><a href="/products/create">New Product</a></li>
            <li v-if="hasAnyRole('Admin', 'Superadmin')">
              <a href="/bundles/create">New Pack</a>
            </li>
          </ul>
        </section>
      </li>
      <li data-root="/category" v-if="hasAnyRole('Admin', 'Superadmin')">
        <a href="/category" class="mdi-grid">Categories</a>
      </li>
      <li data-root="/users,/subscription" v-if="hasAnyRole('Admin', 'Superadmin')">
        <span class="mdi-account-multiple">Users</span>
        <section>
          <ul>
            <li><a href="/users">All Users</a></li>
            <li><a href="/users/create">New User</a></li>
            <li><a href="/subscriptions">All Subscriptions</a></li>
            <li><a href="/subscription-plans/all">All Subscription Plans</a></li>
            <li><a href="/subscriptions/create">New Subscription Plan</a></li>
          </ul>
        </section>
      </li>
      <li data-root="/direct-deposit" v-if="hasAnyRole('Superadmin') && $getGlobal('direct_deposit').show">
        <span class="mdi-cash-usd">Direct Deposit</span>
        <section>
          <ul>
            <li v-if="$getGlobal('direct_deposit').show"><a href="/payment-files">Payment Files</a></li>
            <li v-if="$getGlobal('direct_deposit').show"><a href="/bank-account-search">Bank Accounts</a></li>
            <li v-if="$getGlobal('payquicker').show"><a href="/direct-deposit/paymentList">Direct Deposit</a></li>
          </ul>
        </section>
      </li>
      <li data-root="/ewallet"
          v-if="hasAnyRole('Admin', 'Superadmin')
            || (userSellerType === 'Reseller' && $getGlobal('reseller_ewallet').show)
            || (userSellerType === 'Affiliate' && $getGlobal('affiliate_ewallet').show)">
        <a href="/ewallet/dashboard" class="mdi-wallet">eWallet</a>
      </li>
      <li data-root="/events"
          v-if="eventsVisible">
        <a href="/events" class="mdi-calendar">{{ $getGlobal('events_title').value.plural }}</a>
      </li>
      <!--<li data-root="/commission-engine"
        v-if="$getGlobal('comm_engine_tab').show && hasAnyRole('rep')">-->
        <li data-root="/commission-engine">
        <span class="mdi-engine">Commission Engine</span>
        <section>
          <ul>
            <li v-if="$getGlobal('comm_engine_type').value!=='MCom'"><a href="/commission-engine/dashboard" >Dashboard</a></li>
            <li v-else-if="$getGlobal('comm_engine_type').value==='MCom'"><a href="/commission-engine/mcomm-dashboard">MCom Dashboard</a></li>
            <li v-if="$getGlobal('comm_engine_type').value!=='MCom'"><a href="/commission-engine/my-team-contacts">My Team Contacts</a></li>
            <li v-else-if="$getGlobal('comm_engine_type').value==='MCom'"><a href="/commission-engine/my-mcomm-team-contacts">MCom Team Contacts</a></li>
            <li v-if="$getGlobal('comm_engine_type').value!=='MCom'"><a href="/commission-engine/downline-report">Downline Report</a></li>
            <li v-else-if="$getGlobal('comm_engine_type').value==='MCom'"><a href="/commission-engine/mcomm-downline-report">MCom Downline Report</a></li>
            <li><a href="/commission-engine/tree-view">Downline Tree</a></li>
            <li><a href="/commission-engine/my-ledger">My Ledger</a></li>
          </ul>
        </section>
      </li>
      <li :data-root="$getGlobal('commission_engine_link').value"
          v-if="$getGlobal('commission_engine_link').show">
        <a :href="$getGlobal('commission_engine_link').value" target="_blank" class="mdi-poll">Commission Engine</a>
      </li>
      <li data-root="/lms"
          v-if="hasAnyRole('Rep') && $getGlobal('lms_link').show">
        <a :href="$getGlobal('lms_link').value+'?cpjwt='+token" target="_blank" class="mdi-school">{{$getGlobal('lms_link_name').value}}</a>
      </li>
      <li data-root="/returns"
          v-if="hasAnyRole('Admin', 'Superadmin')
            || (hasAnyRole('Rep') && userSellerType === 'Reseller' && $getGlobal('reseller_returns').show)
            || (hasAnyRole('Rep') && userSellerType === 'Affiliate' && $getGlobal('affiliate_returns').show)">
        <a href="/returns" class="mdi-backup-restore">{{ hasAnyRole('Rep') ? 'My ' : '' }}Returns</a>
      </li>
      <li data-root="/media"
          v-if="hasAnyRole('Admin', 'Superadmin')
            || (userSellerType === 'Reseller' && $getGlobal('reseller_media_library').show)
            || (userSellerType === 'Affiliate' && $getGlobal('affiliate_media_library').show)">
        <a href="/media" class="mdi-folder-open">Media Library</a>
      </li>
      <li data-root="store-builder"
          v-if="(hasAnyRole('Admin', 'Superadmin') && $getGlobal('store_builder_admin').show)
            || (userSellerType === 'Reseller' && $getGlobal('store_builder_reseller').show)">
        <a href="/store-builder" class="mdi-store">Store Builder</a>
      </li>
      <!-- li v-if="hasAnyRole('Superadmin')">
        <a href="/webhooks" class="mdi-source-fork">Webhooks</a>
      </li -->
      <li v-if="hasAnyRole('Admin', 'Superadmin')">
        <a href="/settings" class="mdi-settings">Settings</a>
      </li>
      <li data-root="/release" v-if="hasAnyRole('Superadmin')">
        <a href="/release" class="mdi-format-list-bulleted">Release Summary</a>
      </li>
      <!-- customer items -->
      <li data-root="/my-subscriptions" v-if="hasAnyRole('Customer') && $getGlobal('autoship_enabled').show">
        <a href="/my-subscriptions" class="mdi-calendar-clock">My Subscription</a>
      </li>
      <li data-root="/my-orders" v-if="hasAnyRole('Customer')">
        <a href="/my-orders" class="mdi-dropbox">My Orders</a>
      </li>
      <li data-root="/my-settings" v-if="hasAnyRole('Customer')">
        <a href="/my-settings" class="mdi-folder-account">My Account</a>
      </li>
    </ul>
  </nav>
</template>
<script id="CpNavMenu">
const Auth = require('auth')
module.exports = {
  data () {
    return {
      token: window.localStorage.getItem('jwt_token'),
      isImpersonating: Auth.isImpersonating(),
      announcementsTitle: this.$getGlobal('title_announcement').value || 'Announcements',
      userSellerType: Auth.getClaims().sellerType,
      userStatusBuy: !!Auth.getClaims().perm['core:buy'],
      userStatusSell: !!Auth.getClaims().perm['core:sell']
    }
  },
  mounted () {
    // console.log('COMME ENGINE TYPE'+this.$getGlobal('comm_engine_type').value.toString())
    this.$router.afterEach((to, from) => {
      this.selectActive(to.fullPath)
    })
    this.init()
    this.$events.$on('login-as-change', (e) => {
      this.init()
    })
    this.$events.$on('global-settings-change', (settings) => {
      this.announcementsTitle = this.$getGlobal('title_announcement').value || 'Announcements'
    })
  },
  methods: {
    init () {
      this.isImpersonating = Auth.isImpersonating()
      this.userSellerType = Auth.getClaims().sellerType
      this.userStatusBuy = !!Auth.getClaims().perm['core:buy']
      this.userStatusSell = !!Auth.getClaims().perm['core:sell']
      this.$refs.nav.querySelectorAll('li.expanded').forEach((x) => {
        x.classList.remove('expanded')
        const section = x.querySelector('section')
        if (section) {
          this.hide(section)
        }
      })
      setTimeout(() => {
        this.selectActive()
        this.registerMenuClickEvents()
      })
    },
    revertLoginAs () {
      Auth.revertLoginAs().then(res => {
        this.isImpersonating = Auth.isImpersonating()
        this.$events.$emit('login-as-change')
        this.$router.push('/dashboard')
      })
    },
    hasAnyRole: Auth.hasAnyRole.bind(Auth),
    selectActive (pathname) {
      const url = pathname ? window.location.origin + pathname : window.location.href
      document.querySelectorAll('nav li.active').forEach(x => x.classList.remove('active'))
      document.querySelectorAll('nav .active-parent').forEach(x => x.classList.remove('active-parent'))
      const list = document.querySelectorAll('nav a')
      for (let i = 0; i < list.length; i++) {
        const link = list[i]
        if (!link.$selectRegex && link.dataset.selectCondition) {
          link.$selectRegex = new RegExp(link.dataset.selectCondition)
        }
        if (link.href === url || (link.$selectRegex && link.$selectRegex.test(url))) {
          const li = link.parentNode
          if (li.tagName === 'LI') {
            li.classList.add('active')
          }
          const container = li.parentNode.parentNode
          if (container.tagName === 'SECTION') {
            container.parentNode.classList.add('active-parent')
          }
          break
        }
      }
    },
    hide (target) {
      window.$(target).hide()
    },
    slideDown (target) {
      window.$(target).slideDown()
    },
    slideUp (target) {
      window.$(target).slideUp()
    },
    hideOne (target) {
      this.slideUp(target)
      target.parentNode.classList.remove('expanded')
    },
    showOnly (except) {
      if (except) {
        this.slideDown(except)
        except.parentNode.classList.add('expanded')
      }
      [...document.querySelectorAll('nav section')].filter(x => x !== except).forEach(this.hideOne)
    },
    registerMenuClickEvents () {
      document
        .querySelectorAll('nav.main-menu-nav-scope span')
        .forEach((item) => {
          if (item.__hasMenuListener) return
          item.__hasMenuListener = true
          item.addEventListener('click', (e) => {
            const section = e.target.parentNode.querySelector('section')
            if (section.style.display !== 'block') {
              this.showOnly(section)
            } else {
              this.hideOne(section)
            }
          })
        })
    }
  },
  computed: {
    dashboardVisible() {
      if (this.hasAnyRole('Superadmin', 'Admin', 'Customer')) {
        return true
      } else if (this.hasAnyRole('Rep')) {
        // Workaround to make sure affiliates can make sales, this is a hack for myzoom live
        return (this.userSellerType === 'Reseller' || this.$getGlobal('affiliate_custom_order').show)
      } else {
        return false
      }
    },
    eventsVisible () {
      // Events should not be visible to affiliates or customers
      return this.hasAnyRole('Admin', 'Superadmin')
        || (this.hasAnyRole('Rep') && this.userSellerType === 'Reseller' && this.$getGlobal('allow_reps_events').show)
    },
    mcommOrNo () {
      return $getGlobal('comm_engine_type')
    }
  }
}
</script>
<style lang="scss">
  nav.main-menu-nav-scope{
    position: relative;
    left: -250px;
    height: 100%;
    width: 255px;
    font-size: 16px;
    position: fixed;
    background-color: $cp-main;
    color: $cp-main-inverse;
    overflow: auto;
    z-index: 1000;
    transition: left .1s ease-in-out;
    @media(min-width: 1024px){
      left: 0;
    }

    h2{
      text-align:center;
      font-size:24px;
      img{
        width:180px;
      }
    }

    ul{
      padding:0;
      margin:0;
    }

    & section{
      border-left:dashed 1px $cp-lightGrey;
      margin-left:25px;
      display:none;
    }

    a {
      color:$cp-main-inverse;
    }

    li{
      display:block;
      list-style:none;

      &.active-parent{
        background-color:rgba(255, 255, 255, .1);
        span{
          font-weight: bold;
        }
      }
      li > a {
        margin: 5px;
        border-radius: 2px;
      }
      &.active > span,&.active > a,& span:hover,& a:hover{
        background-color:$cp-lighterGrey;
        color:$cp-main;
      }

      span::after{
        content: '\F141';
        float:right;
        display: inline-block;
        font: normal normal normal 24px/1 "Material Design Icons";
        font-size: inherit;
        text-rendering: auto;
        line-height: inherit;
        -webkit-font-smoothing: antialiased;
        -moz-osx-font-smoothing: grayscale;

        font-size: 24px;
        line-height: 24px;
        margin-right: 8px;
        vertical-align: middle;
        transition: transform 0.2s ease-in-out;
      }
      &.expanded > span::after{
        transform: rotate(-90deg);
      }

      a,span{
        cursor:pointer;
        text-decoration:none;
        display:block;
        padding:10px 15px;
        &[class*="mdi-"]::before{
          display: inline-block;
          font: normal normal normal 24px/1 "Material Design Icons";
          font-size: inherit;
          text-rendering: auto;
          line-height: inherit;
          -webkit-font-smoothing: antialiased;
          -moz-osx-font-smoothing: grayscale;

          font-size: 24px;
          line-height: 24px;
          margin-right: 8px;
          vertical-align: middle;
        }
      }
    }
  }
</style>
