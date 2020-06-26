<template lang="html">
  <div class="cp-dashboard-wrapper">
    <cp-login-as-typeahead v-if="showLoginAsControl" class="login-as-right"></cp-login-as-typeahead>
    <cp-dashboard-sales-volume :announcements="recentAnnouncements" :allLink="showAnnouncementLink" v-if="hasAnyRole('Superadmin', 'Admin', 'Rep')"></cp-dashboard-sales-volume>
    <cp-customer-dashboard v-if="hasAnyRole('Customer')"></cp-customer-dashboard>
  </div>
</template>

<script>
const Announcements = require('../../resources/announcements.js')
const Auth = require('auth')

module.exports = {
  name: 'CpDashboard',
  routing: [
    {
      name: 'site.CpDashboardRoot',
      path: '/',
      meta: {
        title: 'Dashboard',
        nosubscription: !Auth.hasAnyRole('Rep')
      },
      props: true
    },
    {
      name: 'site.CpDashboard',
      path: '/dashboard',
      meta: {
        title: 'Dashboard',
        nosubscription: !Auth.hasAnyRole('Rep')
      },
      props: true
    }
  ],
  data () {
    return {
      showLoginAsControl: Auth.hasAnyRole('Superadmin', 'Admin'),
      recentAnnouncements: [],
      showAnnouncementLink: false,
      announcementRequest: {
        column: 'created_at',
        order: 'desc',
        per_page: '5'
      }
    }
  },
  mounted () {
    this.getAnnouncements()
    this.$events.$on('login-as-change', (e) => {
      this.showLoginAsControl = Auth.hasAnyRole('Superadmin', 'Admin')
    })
  },
  methods: {
    hasAnyRole (...roles) {
      return Auth.hasAnyRole(...roles)
    },
    getAnnouncements () {
      // Don't grab announcements for customers
      if (this.hasAnyRole('Superadmin', 'Admin', 'Rep')) {
        Announcements.index(this.announcementRequest)
        .then((response) => {
          if (!response.error) {
            this.recentAnnouncements = response.data
            if (response.total > 5) {
              this.showAnnouncementLink = true;
            }
          }
        })
      }
    }
  },
  components: {
    CpLoginAsTypeahead: require('../authentication/CpLoginAsTypeahead.vue'),
    CpDashboardSalesVolume: require('../dashboard/sales-volume.vue'),
    CpCustomerDashboard: require('../dashboard/CpCustomerDashboard.vue')
  }
}
</script>

<style lang="scss">
.cp-dashboard-wrapper {
  .login-as-right {
    max-width: 300px;
    margin-right: 0;
    margin-left: auto;
  }
}
</style>
