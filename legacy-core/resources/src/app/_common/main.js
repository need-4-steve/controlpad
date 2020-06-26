/* global sms Vue VueRouter */
sms.vInstance((require, module, vRoutes) => {
  const Auth = require('auth')
  const requestHandler = require('assets/js/resources/requestHandler.js')
  window.Vue.config.ignoredElements = [/^cp-.+|^v-.+|.+-scoped$/]
  const routes = []
  Object.keys(vRoutes).forEach((key) => {
    const route = vRoutes[key]
    let lineage = route.name.split('.')
    route.name = lineage.pop()
    lineage = lineage.join('.')
    if (lineage) {
      if (!vRoutes[lineage]) {
        throw Error(`Invalid route '${key}'. Parent route '${lineage}' not found!`)
      }
      if (!vRoutes[lineage].children) {
        vRoutes[lineage].children = []
      }
      vRoutes[lineage].children.push(route)
    } else {
      if (route.children) {
        route.children.sort((a, b) => b.path.localeCompare(a.path))
      }
      routes.push(route)
    }
  })
  const router = new VueRouter({ mode: 'history', routes })
  router.beforeEach((to, from, next) => {
    if (!Auth.isLoggedIn() && !to.meta.noauth) {
      let path = '/auth'
      if (to.fullPath !== '/') {
        path += `?return=${encodeURIComponent(to.fullPath)}`
      }
      next(path)
    } else if (Auth.isLoggedIn() && !Auth.hasActiveSubscription() && !to.meta.nosubscription) {
      window.Vue.toast('Feature unavailable. Please renew your subscription.', { dismiss: false })
      next('/my-settings')
    } else if (Auth.isLoggedIn() && !Auth.hasAcceptedTerms() && !to.meta.nosubscription) {
      next('/my-settings')
    } else {
      next()
    }
  })
  window.routes = routes
  requestHandler.get('/api/v1/settings').then((settings) => {
    (() => { // GOOGLE ANALYTICS - START
      const trackingId = (settings['google_tracking_id'] || {}).value
      window.ga = window.ga || function () { (window.ga.q = window.ga.q || []).push(arguments) }; window.ga.l = +new Date()
      window.ga('create', trackingId, 'auto')
      window.ga('require', 'urlChangeTracker')
      window.ga('send', 'pageview');
      ['https://www.google-analytics.com/analytics.js',
        '//cdnjs.cloudflare.com/ajax/libs/autotrack/2.4.1/autotrack.js']
        .forEach((url) => {
          const script = document.createElement('SCRIPT')
          script.src = url
          document.body.appendChild(script)
        })
    })() // GOOGLE ANALYTICS - END

    const announcementsTitle = settings['title_announcement']
    if (announcementsTitle.show === 1) {
      routes
        .find(x => x.name === 'site')
        .children
        .find(x => x.name === 'CpAnnouncementIndex')
        .meta.title = announcementsTitle.value
    }
    const autoshipTitle = settings['autoship_display_name']
    routes
      .find(x => x.name === 'site')
      .children
      .find(x => x.name === 'CpPlanIndex')
      .meta.title = autoshipTitle.value

    window.Vue.use({
      install (Vue, options) {
        Vue.prototype.$events = new Vue()
        const $global = { settings }
        Vue.prototype.$updateGlobal = function (partialSettings) {
          Object.keys(partialSettings)
            .filter(key => !!$global.settings[key])
            .forEach(key => {
              if ($global.settings[key]) {
                $global.settings[key].show = partialSettings[key].show
                $global.settings[key].value = partialSettings[key].value
              }
            })
          this.$events.$emit('global-settings-change', $global.settings)
        }
        Vue.prototype.$getGlobal = (key) => {
          return $global.settings[key] || { show: false, value: '' }
        }
        Vue.prototype.$isBlank = (str) => {
          return (!str || /^\s*$/.test(str))
        }
        Vue.prototype.$isUrl = (str) => {
          return (!str || /^(?:(?:https?|ftp):\/\/)?(?:(?!(?:10|127)(?:\.\d{1,3}){3})(?!(?:169\.254|192\.168)(?:\.\d{1,3}){2})(?!172\.(?:1[6-9]|2\d|3[0-1])(?:\.\d{1,3}){2})(?:[1-9]\d?|1\d\d|2[01]\d|22[0-3])(?:\.(?:1?\d{1,2}|2[0-4]\d|25[0-5])){2}(?:\.(?:[1-9]\d?|1\d\d|2[0-4]\d|25[0-4]))|(?:(?:[a-z\u00a1-\uffff0-9]-*)*[a-z\u00a1-\uffff0-9]+)(?:\.(?:[a-z\u00a1-\uffff0-9]-*)*[a-z\u00a1-\uffff0-9]+)*(?:\.(?:[a-z\u00a1-\uffff]{2,})))(?::\d{2,5})?(?:\/\S*)?$/.test(str))
        }
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

    let Rollbar = require('assets/js/resources/Rollbar.js')
    window.Vue.use(Rollbar)

    window.VueInstance = module.exports = new Vue({
      router,
      el: '#vue-app',
      data: { title: 'Controlpad LLC' }
    })
  }).catch(reason => console.error(reason))
})
