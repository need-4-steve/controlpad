/* global Vue moment, VueBootstrapDatetimePicker */
// custom Vue filters

window.Vue.filter('cpStandardDate', (date, time = false, timezone = true) => {
  if (date === null)
    return null

  var moment = require('moment-timezone')
  if (!timezone) {
    if (time) {
      return moment.utc(date).format('MM/DD/YYYY hh:mm a z')
    } else {
      return moment.utc(date).format('MM/DD/YYYY')
    }
  }
  date = moment.utc(date).local()
  if (time) {
    return date.format('MM/DD/YYYY hh:mm a z')
  } else {
    return date.format('MM/DD/YYYY')
  }
})
window.Vue.filter('shortDate', function (date) {
  var moment = require('moment')
  return moment(date).format('ll')
})

window.Vue.filter('phone', function (phone) {
  return phone.replace(/[^0-9]/g, '')
    .replace(/(\d{3})(\d{3})(\d{4})/, '($1) $2-$3')
})

window.Vue.filter('pluckSum', function (list, key) {
  return list.reduce(function (total, item) {
    return total + item[key]
  }, 0)
})

window.Vue.filter('removeWhiteSpace', {
  read: function (value) {
    return value.replace(/\s/g, '')
  },
  write: function (value) {
    return value.replace(/\s/g, '')
  }
})

window.Vue.filter('currency', function (num, floor) {
  num = Number(num)
  if (floor) {
    if (num || num === 0) {
      if (num < 0) {
        num *= -1
        return '-$' + num.toFixed(2).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',')
      }
      return '$' + num.toFixed(2).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',')
    }
    return ''
  }
  return '$' + parseFloat(num).toFixed(2).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',')
})

window.Vue.filter('imageSize', function (image, size) {
  let reg = /(?:\.([^.]+))?$/
  let ext = reg.exec(image)[1]
  image = image.replace(/\.[^/.]+$/, '')
  return image + '-' + size + '.' + ext
})

window.Vue.filter('numeral', function (value) {
  return numeral(value).format('0,0');
})

// vue - extended libraries

const VueMask = require('v-mask')
window.Vue.use(VueMask)

const { VueMaskDirective } = require('v-mask')
window.Vue.directive('mask', VueMaskDirective)

window.Vue.use(require('vue-resource'))
const Toast = require('./custom-plugins/toast/index.js')
window.Vue.use(Toast)
// d3
var VueD3 = require('vue-d3')
window.Vue.use(VueD3)

// settings
const cpSettings = require('./custom-plugins/settings.js')
window.Vue.use(cpSettings)

window.cpHelpers = {
  getParameterByName: function (name, url) {
    if (!url) url = window.location.href
    name = name.replace(/[[\]]/g, '\\$&')
    var regex = new RegExp('[?&]' + name + '(=([^&#]*)|&|#|$)')
    var results = regex.exec(url)
    if (!results) return null
    if (!results[2]) return ''
    return decodeURIComponent(results[2].replace(/\+/g, ' '))
  }
}

window.Vue.use({
  install (Vue, options) {
    // get path parameters
    Vue.prototype.$pathParameter = function () {
      var urlSplit = window.location.pathname.split('/')
      var i = 0
      while (i < urlSplit.length) {
        if (!isNaN(urlSplit[i]) && urlSplit[i] !== '') {
          return urlSplit[i]
        }
        i++
      }
    }
    Vue.prototype.$pathParameterName = function () {
      var urlSplit = window.location.pathname.split('/')
      return urlSplit[urlSplit.length - 1]
    }
  }
})

window.Vue.use({
  install (Vue) {
    const SERVER_FORMAT = 'YYYY-MM-DDTHH:mm:ss.SSS'
    const CLIENT_FORMAT = 'MM/DD/YYYY h:mm'
    Vue.prototype.$clientDate = (date) => {
      return moment.utc(date).local().format(CLIENT_FORMAT)
    }
    Vue.prototype.$serverDate = (date) => {
      return moment(date, CLIENT_FORMAT).utc().format(SERVER_FORMAT)
    }
  }
})

// Routes
Vue.component('cp-events-index', require('./components/events/CpEventsIndex.vue'))
Vue.component('cp-events-orders', require('./components/events/CpEventsOrders.vue'))

// Inputs
Vue.component('cp-date-input', require('./cp-components-common/inputs/CpDateInput.vue'))
Vue.component('cp-tooltip', require('./custom-plugins/CpTooltip.vue'))
Vue.component('cp-select', require('./cp-components-common/inputs/CpSelect.vue'))
Vue.component('cp-input', require('./cp-components-common/inputs/CpInput.vue'))
Vue.component('date-picker', VueBootstrapDatetimePicker.default)

;(() => {
  const regex = /attachment;\s*filename=(['"])([^\1]*)\1/i
  const captureAnchorClick = function (e) {
    e.preventDefault()
    const url = e.target.href
    const download = e.target.download
    const anchor = document.createElement('a')
    const token = (window.localStorage.getItem('jwt_token') || '')
    const headers = new window.Headers()
    const options = { headers }
    if (url.indexOf(window.location.origin) === 0) {
      options.credentials = 'include'
      headers.append('Authorization', `Bearer ${token}`)
    }
    let filename = 'filename.ext'
    window.fetch(url, options)
      .then(res => {
        filename = ((res.headers.get('Content-Disposition') || '').match(regex) || [])[2]
        return res.blob()
      })
        .then((blob) => {

            debugger
        const blobUrl = window.URL.createObjectURL(blob)
        anchor.href = blobUrl
        anchor.download = download || filename
        anchor.target = '_self'
        anchor.style.display = 'none'
        window.document.body.append(anchor)
        anchor.click()
        window.URL.revokeObjectURL(blobUrl)
        anchor.remove()
      })
  }

  new window.MutationObserver(() => {
    document
      .querySelectorAll('a[download]')
      .forEach((item) => {
        if (item.$__hasLinkListener) return
        item.$__hasLinkListener = true
        item.addEventListener('click', captureAnchorClick)
      })
  }).observe(document, {
    subtree: true,
    attributes: true,
    childList: true
  })
})()

Vue.component('cp-toggle', require('./cp-components-common/inputs/CpToggle.vue'))
Vue.component('cp-input', require('./cp-components-common/inputs/CpInput.vue'))
Vue.component('cp-login-as', require('./components/authentication/CpLoginAs.vue'))
Vue.component('cp-input-mask', require('./cp-components-common/inputs/CpInputMask.vue'))
Vue.component('cp-select', require('./cp-components-common/inputs/CpSelect.vue'))
Vue.component('cp-tooltip', require('./custom-plugins/CpTooltip.vue'))
Vue.component('cp-pagination', require('./cp-components-common/navigation/CpPagination.vue'))


window.vueInstance = new Vue({
  el: '#vue-app',
  data: {
    title: 'Controlpad LLC'
  },
  beforeCreate: function() {
    console.log(this.$commEngineType)
  },
  components: {
    CpCustomOrder: require('./components/orders/custom-order.vue'),
    CpCoupon: require('./components/coupons/coupon.vue'),
    CpInvoiceIndex: require('./components/invoices/index.vue'),
    CpDashboardSalesVolume: require('./components/dashboard/sales-volume.vue'),
    CpDashboardMcommSalesVolume: require('./components/commission-engine/mcomm/dashboard-layout/mcomm-sales-volume.vue'),
    CpPaymentLists: require('./components/direct-deposit/pay-quicker.vue'),
    CpDirectDepositClosed: require('./components/direct-deposit/closed-index.vue'),
    CpDirectDepositOpen: require('./components/direct-deposit/open-index.vue'),
    CpDirectDepositOpenDetail: require('./components/direct-deposit/open-detail.vue'),
    CpDirectDepositClosedDetail: require('./components/direct-deposit/closed-detail.vue'),
    CpOrder: require('./components/orders/order.vue'),
    CpProductIndex: require('./components/products/index.vue'),
    CpInventoryIndex: require('./components/inventory/CpInventoryIndex.vue'),
    CpSalesTax: require('./components/ewallet/admin/sales-tax.vue'),
    CpProcessingFees: require('./components/ewallet/admin/processing-fees.vue'),
    CpEwalletDashboard: require('./components/ewallet/rep/dashboard.vue'),
    CpHistoryIndex: require('./components/history/index.vue'),
    CpMyPayments: require('./components/ewallet/rep/my-payments.vue'),
    CpReturnsIndex: require('./components/returns/index.vue'),
    CpReturnDetail: require('./components/returns/details-index.vue'),
    CpRepOrderIndex: require('./components/orders/rep-order-index.vue'),
    CpShippingSettings: require('./components/shipping/CpShippingSettings.vue'),
    CpEInvoice: require('./components/orders/CpEInvoice.vue'),
    CpSubscriptionForm: require('./components/subscription/form.vue'),
    CpSubscriptionPlanIndex: require('./components/subscription/plan-index.vue'),
    CpSubscriptionUserIndex: require('./components/subscription/user-index.vue'),
    CpSubscriptionReports: require('./components/subscription/reports.vue'),
    CpUserCreateForm: require('./components/users/user-create-form.vue'),
    CpMySettings: require('./components/settings/CpMySettings.vue'),
    CpSalesIndex: require('./components/sales/sales-index.vue'),
    CpLogin: require('./components/authentication/CpLogin.vue'),
    // CpLoginAs: require('./components/authentication/CpLoginAs.vue'),
    CpSettingsIndex: require('./components/settings/index.vue'),
    CpSettingsEmail: require('./components/settings/partials/CpSettingsEmail.vue'),
    CpSettingsTaxes: require('./components/settings/partials/CpSettingsTaxes.vue'),
    CpCustomPagesIndex: require('./components/custom-pages/CpCustomPagesIndex.vue'),
    CpAnnouncementIndex: require('./components/announcements/announcement-index.vue'),
    CpStoreBuilder: require('./components/store-builder/store-builder.vue'),
    CpMediaIndex: require('./components/media/media-index.vue'),
    CpRegistration: require('./components/registration/CpRegistration.vue'),
    CpShippo: require('./components/shipping/CpShippo.vue'),
    CpBundleForm: require('./components/products/CpBundleForm.vue'),
    // CpRepToCustomerSalesIndex: require('./components/sales/rep-to-customer.vue'),
    CpFinancialReports: require('./components/reports/FinancialReports.vue'),
    CpEmailReports: require('./components/reports/EmailReports.vue'),
    CpEmailEdit: require('./components/emails/custom_email_edit.vue'),
    CpLiveVideoIndex: require('./components/live-videos/index.vue'),
    CpLiveVideoCreate: require('./components/live-videos/create.vue'),
    CpLiveVideoCreateYoutube: require('./components/live-videos/create-youtube.vue'),
    CpLiveVideoShow: require('./components/live-videos/show.vue'),
    CpLiveVideoPublic: require('./components/live-videos/CpLiveVideoPublic.vue'),
    CpUserIndex: require('./components/users/index.vue'),
    CpAdminRepIndex: require('./components/inventory/AdminRepIndex.vue'),
    CpLoginAsTypeahead: require('./components/authentication/CpLoginAsTypeahead.vue'),
    CpWholesaleNav: require('./components/store/WholesaleNav.vue'),
    CpRepStoreLocator: require('./components/rep-locator/RepStoreLocator.vue'),
    CpCategories: require('./components/categories/categories-index.vue'),
    CpCheckout: require('./components/checkout/CpCheckout.vue'),
    CpBackOfficeCart: require('./components/store/CpBackOfficeCart.vue'),
    CpDashboard: require('./components/dashboard/CpDashboard.vue'),
    CpPasswordReset: require('./components/authentication/CpPasswordReset.vue'),
    CpSendPasswordReset: require('./components/authentication/CpSendPasswordReset.vue'),
    CpReleaseNotes: require('./components/release-notes/CpReleaseNotes.vue'),
    CpNavMenu: require('./components/CpNavMenu.vue')
  }
})
