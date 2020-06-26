<template>
  <div class="cp-ewallet-dashboard-scope">
    <span class="ewallet-block" v-if="checkWithdraw()">
        <span v-if="checkPayTaxesFirst()">
            <cp-withdraw :call-back="getReport" :modal-show="report" :available-funds="report.balance.eWalletBalance" :available-commissions="report.commission.eWalletBalance" :balance-withdraw="balanceWithdraw" :commission-withdraw="commissionWithdraw" class="ewallet-payment-button"></cp-withdraw>
        </span>
        <span v-else>
            <button type="button" class="cp-button-standard" disabled>Get Paid</button>
        </span>
        <span v-show="!checkPayTaxesFirst()">
            <cp-tooltip :options=" {content: 'All open sales tax needs to be paid before you can withdraw.'}"></cp-tooltip>
        </span>
    </span>
    <span class="ewallet-block" v-if="checkPayTaxes()">
        <cp-pay-taxes :call-back="getReport" :modal-show="report" :available-funds="report.balance.eWalletBalance" :taxes-owed="report.balance.pendingTaxTotal" :user="user"></cp-pay-taxes>
    </span>
    <span class="ewallet-block">
        <span></span>
        <span>
            <a class="cp-button-link" href="javascript:void(0)" @click="redirectToPayQuicker()" v-show="$getGlobal('payquicker').show && Auth.hasAnyRole('Rep')">PayQuicker</a>
        </span>
    </span>
    <div>
    <cp-totals-banner
      :totals="banner"
      :floor="true"></cp-totals-banner>
    </div>
    <cp-tabs
    :items="tabs"
    :callback="selectLedger"></cp-tabs>
      <cp-totals-banner v-if="activeLedger.salesLedger && showPending()"
        :totals="[
        {title:'Pending Sales', amount: report.balance.pendingSalesTotal},
        {title:'Pending Sales Count', amount: report.balance.pendingSalesCount, currency: false}]"
        :floor="true">
      </cp-totals-banner>
      <cp-totals-banner v-if="activeLedger.salesTaxLedger"
        :totals="[
        {title:'Pending Tax Count', amount: report.balance.pendingTaxCount, currency: false}]"
        :floor="true">
      </cp-totals-banner>
        <cp-ledger key="Company" v-if="activeLedger.salesLedger && Auth.hasAnyRole('Superadmin', 'Admin')" source="Rep"></cp-ledger>
        <cp-ledger key="Rep" v-if="activeLedger.salesLedger && Auth.hasAnyRole('Rep')" source="Rep"></cp-ledger>
        <cp-ledger key="Commissions" v-if="activeLedger.commissionsLedger && Auth.hasAnyRole('Rep')" source="Company"></cp-ledger>
        <cp-tax-ledger v-if="activeLedger.salesTaxLedger"></cp-tax-ledger>
    </div>
</template>

<script>
const Auth = require('auth')
const EWallet = require('../../../resources/ewallet.js')

module.exports = {
  data: function () {
    return {
      loading: true,
      user: {
        seller_type_id: 2
      },
      report: {
        balance: {},
        commission: {}
      },
      Auth: Auth,
      activeLedger: {
        salesLedger: false,
        commissionsLedger: false,
        salesTaxLedger: false,
      },
      banner: [],
      tabs: [{name: ''}],
      balanceWithdraw: false,
      commissionWithdraw: false
    }
  },
  mounted: function () {
    this.getUser()
    this.getReport()
  },
  methods: {
    redirectToPayQuicker () {
      EWallet
        .getPayQuickerRedirectLink()
        .then(res => (window.location = res.invitationUrl))
    },
    getUser () {
      EWallet.getUser()
        .then((response) => {
          this.user = response
        })
    },
    getReport: function () {
      EWallet.dashboard()
        .then((response) => {
          this.report = response
          this.getSettings()
          this.loading = false
        })
    },
    getSettings: function () {
      var tabs = this.tabs.length
      for (i = 0; i <= tabs; i++) {
        this.tabs.pop()
      }
      var banner = this.banner.length
      for (i = 0; i <= banner; i++) {
        this.banner.pop()
      }
      var active = true
      if (Auth.hasAnyRole('Admin', 'Superadmin')) {
        this.tabs.push({ name: 'Company Balance Ledger', active: active })
        this.banner.push({title:'Current Balance', amount: this.report.balance.eWalletBalance})
        this.activeLedger.salesLedger = active
        active = false
        this.tabs.push({ name: 'Sales Tax Ledger', active: active})
        this.banner.push({title:'Sales Tax Owed', amount: this.report.balance.pendingTaxTotal})
      } else if (Auth.hasAnyRole('Rep')) {
        if (this.$getGlobal('affiliate_ewallet_balance').show && this.user.seller_type_id === 1 ||
            this.$getGlobal('reseller_ewallet_balance').show && this.user.seller_type_id === 2 ) {
              this.tabs.push({ name: 'My Balance Ledger', active: active})
              this.banner.push({title:'Current Balance', amount: this.report.balance.eWalletBalance})
              this.balanceWithdraw = true
              this.activeLedger.salesLedger = active
              active = false
        }
        if (this.$getGlobal('affiliate_ewallet_commission').show && this.user.seller_type_id === 1 ||
            this.$getGlobal('reseller_ewallet_commission').show && this.user.seller_type_id === 2 ) {
              this.tabs.push({ name: 'Commissions Ledger', active: active})
              this.banner.push({title:'Current Commissions Balance', amount: this.report.commission.eWalletBalance})
              this.commissionWithdraw = true
              this.activeLedger.commissionsLedger = active
              active = false
        }
        if (this.$getGlobal('affiliate_ewallet_taxes').show && this.user.seller_type_id === 1 ||
            this.$getGlobal('reseller_ewallet_taxes').show && this.user.seller_type_id === 2 ) {
              this.tabs.push({ name: 'Sales Tax Ledger', active: active})
              this.banner.push({title:'Sales Tax Owed', amount: this.report.balance.pendingTaxTotal})
              this.activeLedger.salesTaxLedger = active
              active = false
        }
      }

    },
    selectLedger (name) {
      this.activeLedger.salesLedger = false
      this.activeLedger.salesTaxLedger = false
      this.activeLedger.commissionsLedger = false
      switch (name) {
        case 'Company Balance Ledger':
          this.activeLedger.salesLedger = true
          break
        case 'My Balance Ledger':
          this.activeLedger.salesLedger = true
          break
        case 'Sales Tax Ledger':
          this.activeLedger.salesTaxLedger = true
          break
        case 'Commissions Ledger':
          this.activeLedger.commissionsLedger = true
          break
      }
    },
    checkPayTaxesFirst: function () {
      if (this.user.seller_type_id === 1 && this.$getGlobal('affiliate_ewallet_taxes_paid_first').show && this.report.balance.pendingTaxTotal > 0) {
        return false
      }
      if (this.user.seller_type_id === 2 && this.$getGlobal('reseller_ewallet_taxes_paid_first').show && this.report.balance.pendingTaxTotal > 0) {
        return false
      }
      return true
    },
    checkWithdraw: function () {
      if (this.Auth.hasAnyRole('Rep')) {
        if (this.user.seller_type_id === 1 && this.$getGlobal('affiliate_ewallet_withdraw').show) {
          return true
        }
        if (this.user.seller_type_id === 2 && this.$getGlobal('reseller_ewallet_withdraw').show) {
          return true
        }
      }
      return false
    },
    checkPayTaxes: function () {
      if (this.Auth.hasAnyRole('Rep')) {
        if (this.user.seller_type_id === 1 && this.report.balance.pendingTaxTotal > 0 && (
          this.$getGlobal('affiliate_ewallet_taxes_balance').show ||
          this.$getGlobal('affiliate_ewallet_taxes_ach').show ||
          this.$getGlobal('affiliate_ewallet_taxes_credit_card').show
        )) {
          return true
        }
        if (this.user.seller_type_id === 2 && this.report.balance.pendingTaxTotal > 0 && (
          this.$getGlobal('reseller_ewallet_taxes_balance').show ||
          this.$getGlobal('reseller_ewallet_taxes_ach').show ||
          this.$getGlobal('reseller_ewallet_taxes_credit_card').show
        )) {
          return true
        }
      }
      return false
    },
    showPending: function () {
      if (this.Auth.hasAnyRole('Rep')) {
        if (this.user.seller_type_id === 1 && this.$getGlobal('affiliate_ewallet_pending_balance').show) {
          return true
        }
        if (this.user.seller_type_id === 2 && this.$getGlobal('reseller_ewallet_pending_balance').show) {
          return true
        }
      }
      return false
    }
  },
  components: {
    CpTotalsBanner: require('../../reports/CpTotalsBanner.vue'),
    CpWithdraw: require('../../ewallet/rep/CpWithdraw.vue'),
    CpTableControls: require('../../../cp-components-common/tables/CpTableControls.vue'),
    CpPayTaxes: require('../../ewallet/rep/CpPayTaxes.vue'),
    CpLedger: require('../../ewallet/rep/CpLedger.vue'),
    CpTabs: require('../../../cp-components-common/navigation/CpTabs.vue'),
    CpTaxLedger: require('../../ewallet/rep/CpTaxLedger.vue'),
    CpTooltip: require('../../../custom-plugins/CpTooltip.vue')
  }
}

</script>

<style lang="scss">
.cp-ewallet-dashboard-scope {
  .ewallet-block {
    display: inline-block;
  }
  @media (max-width: 768px) {
    .dashboard-wrapper {
      .cp-select-standard select {
        width: 100% !important;
      }
      .cp-button-standard {
        font-size: 10px;
      }
    }
  }
}
</style>
