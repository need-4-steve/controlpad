<template>
  <div class="cp-announcements">
    <div class="cp-annnouncement-wrapper">
      <h4 class="panel-title">Announcements</h4>
      <ul>
        <li v-for="announcement in announcements">
          <a @click="viewAnnouncements(announcement)">
            <h3 class="no-top no-bottom">{{ announcement.title }}</h3>
            <span>{{ announcement.updated_at | shortDate }}</span>
          </a>
        </li>
      </ul>
      <a class="all-link" v-if="allLink" href="/announcements">See All</a>
    </div>
    <transition name='modal'>
        <section class="cp-modal-standard" v-if="announcementModal">
            <div class="cp-modal-body">
                <cp-announcement-show :announcement="selectedAnnouncement" :modal-show="announcementModal" @close="announcementModal = false"></cp-announcement-show>
            </div>
        </section>
    </transition>
  </div>
</template>
<script>
module.exports = {
  data: function () {
    return {
      selectedAnnouncement: {},
      announcementModal: false
    }
  },
  props: ['announcements', 'allLink'],
  methods: {
    viewAnnouncements: function (announcement) {
      this.announcementModal = true
      this.selectedAnnouncement = announcement
    }
  },
  components: {
    CpAnnouncementShow: require('../announcements/CpAnnouncementShow.vue')
  }
}
</script>
<style lang="scss" scoped>
.cp-annnouncement-wrapper {
  ul {
    width: 300px
  }
  h3 {
    width: 68%
  }
  span {
    width: 50%
  }
  .all-link {
    width: 300px;
    text-align: center;
    display: block;
    box-shadow: 0 2px 8px rgba(0, 0, 0, .33);
  }
}
</style>
