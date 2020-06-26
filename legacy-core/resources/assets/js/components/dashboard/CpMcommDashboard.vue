<template lang="html">
  <div class="cp-mcomm-dashboard-wrapper">
    <cp-login-as-typeahead v-if="showLoginAsControl" class="login-as-right"></cp-login-as-typeahead>
    <cp-dashboard-mcomm-sales-volume :announcements="recentAnnouncements" :allLink="showAnnouncementLink"></cp-dashboard-mcomm-sales-volume>
  </div>
</template>
<script>
const Announcements = require('../../resources/announcements.js')
const Auth = require('auth')

module.exports = {
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
  },
  components: {
    CpLoginAsTypeahead: require('../authentication/CpLoginAsTypeahead.vue'),
    CpDashboardMcommSalesVolume: require('../dashboard/mcomm-sales-volume.vue')
  }
}
</script>

<style lang="scss">
.cp-mcomm-dashboard-wrapper {
  .login-as-right {
    max-width: 300px;
    margin-right: 0;
    margin-left: auto;
  }
}
</style>