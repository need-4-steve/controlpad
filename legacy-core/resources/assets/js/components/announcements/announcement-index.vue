<template lang="html">
    <div>
        <div class="annoucenment-wrapper">
            <div class="space-between">
                <div>
                    <button v-show="Auth.hasAnyRole('Superadmin', 'Admin')" @click="openModal()" class="cp-button-link">Create</button>
                </div>
            </div>
            <cp-table-controls
            :date-picker="false"
            :index-request="indexRequest"
            :search-place-holder="'Search' + '\x20' + $getGlobal('title_announcement').value"
            :resource-info="pagination"
            :get-records="getAnnouncements"></cp-table-controls>
            <table class="cp-table-standard desktop">
                <thead>
                    <tr>
                        <th><!-- Edit --></th>
                        <th>Title</th>
                        <th>Description</th>
                        <th>Last Updated</th>
                        <th>Created</th>
                        <th><!-- Delete --></th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="announcement in announcements">
                        <td v-if="Auth.hasAnyRole('Superadmin','Admin')"><a @click="showAnnouncements(announcement)"><i class="mdi mdi-pencil"></i></a></td>
                        <td v-else></td>
                        <td><a @click="viewAnnouncements(announcement)">{{announcement.title}}</a></td>
                        <td>{{announcement.description}}</td>
                        <td>{{announcement.updated_at | cpStandardDate }}</td>
                        <td>{{announcement.created_at | cpStandardDate }}</td>
                        <td v-if="Auth.hasAnyRole('Superadmin','Admin')"><i class="mdi mdi-close pointer" @click="confirmAndDelete(announcement.id)"></i></td>
                        <td v-else></td>
                    </tr>
                </tbody>
            </table>
            <section class="cp-table-mobile">
                <div  v-for="announcement in announcements">
                    <div v-if="Auth.hasAnyRole('Superadmin','Admin')"><span></span><span><a @click="showAnnouncements(announcement)"><i class="mdi mdi-pencil"></i></a></span></div>
                    <div v-else-if><span></span><span></span></div>
                    <div><span>Title: </span><span><a @click="viewAnnouncements(announcement)">{{announcement.title}}</a></span></div>
                    <div><span>Description: </span><span>{{announcement.description}}</span></div>
                    <div><span>Last Updated: </span><span>{{announcement.updated_at | cpStandardDate }}</span></div>
                    <div><span>Created: </span><span>{{announcement.created_at | cpStandardDate }}</span></div>
                    <div v-if="Auth.hasAnyRole('Superadmin','Admin')"><span></span><span><i class="mdi mdi-close pointer" @click="confirmAndDelete(announcement.id)"></i></span></div>
                    <div v-else-if><span></span><span></span></div>
                </div>
            </section>
            <cp-confirm
            :message="'Are you sure you want to delete this announcement?'"
            v-model="showConfirm"
            :show="showConfirm"
            :callback="deleteAnnouncement"
            :params="{id:announcementId}"></cp-confirm>
            <div class="align-center">
                <img class="loading" :src="$getGlobal('loading_icon').value" v-if="loading">
                <cp-pagination :pagination="pagination" :callback="getAnnouncements" :offset="2"></cp-pagination>
            </div>
        </div>
        <!-- EDIT MODAL -->
        <transition name='modal'>
            <section class="cp-modal-standard" v-if="announcementModal.edit">
                <div class="cp-modal-body">
                    <h2>Edit</h2>
                    <cp-announcement-form :announcement="selectedAnnouncement" :modal-show="announcementModal.edit" :edit="true" @close="announcementModal.edit = false"></cp-announcement-form>
                </div>
            </section>
        </transition>
        <!-- CREATE MODAL -->
        <transition name='modal'>
            <section class="cp-modal-standard" v-if="announcementModal.create">
                <div class="cp-modal-body">
                    <h2>Create</h2>
                    <cp-announcement-form :call-back="getAnnouncements" :modal-show="announcementModal.create" :edit="false" @close="announcementModal.create = false"></cp-announcement-form>
                </div>
            </section>
        </transition>
          <!-- Show MODAL -->
        <transition name='modal'>
            <section class="cp-modal-standard" v-if="announcementModal.view">
                <div class="cp-modal-body">
                    <cp-announcement-show :announcement="selectedAnnouncement" :modal-show="announcementModal.view" @close="announcementModal.view = false"></cp-announcement-show>
                </div>
            </section>
        </transition>
    </div>
</template>
<script>
const Announcements = require('../../resources/announcements.js')
const Auth = require('auth')
const moment = require('moment')

module.exports = {
  data: function () {
    return {
      Auth: Auth,
      loading: false,
      announcements: [],
      announcementId: null,
      selectedAnnouncement: {},
      showConfirm: false,
      announcementModal: {
        edit: false,
        create: false,
        view: false
      },
      pagination: {},
      indexRequest: {
        search_term: null,
        column: 'created_at',
        order: 'DESC',
        page: 1,
        per_page: 15
      }
    }
  },
  mounted: function () {
    this.getAnnouncements()
  },
  methods: {
    openModal () {
      this.announcementModal.create = true
    },
    confirmAndDelete (id) {
      this.announcementId = id
      this.showConfirm = true
    },
    getAnnouncements: function () {
      this.loading = true
      this.indexRequest.page = this.pagination.current_page
      Announcements.index(this.indexRequest)
        .then((response) => {
          if (!response.error) {
            this.pagination = response
            this.announcements = response.data
            this.loading = false
            return response
          }
        })
    },
    viewAnnouncements: function (announcement) {
      this.announcementModal.view = true
      this.selectedAnnouncement = announcement
    },
    showAnnouncements: function (announcement) {
      if (Auth.hasAnyRole('Superadmin', 'Admin')) {
        this.announcementModal.edit = true
        this.selectedAnnouncement = announcement
      } else {
        window.location = '/announcements/' + announcement.url
      }
    },
    deleteAnnouncement: function (announcement) {
      Announcements.delete(announcement.id)
        .then((response) => {
          if (response.error) {
            this.$toast(response.messages, { dismiss: false, error: true })
            return
          }
          this.getAnnouncements()
          this.$toast('Successfully deleted.', { dismiss: false })
          this.modalShow = false
        })
    }
  },
  events: {
    'search_term': function (term) {
      this.indexRequest.search_term = term
      this.pagination.current_page = 1
      this.getAnnouncements()
    }
  },
  components: {
    CpTableControls: require('../../cp-components-common/tables/CpTableControls.vue'),
    CpAnnouncementForm: require('./CpAnnouncementForm.vue'),
    CpAnnouncementShow: require('./CpAnnouncementShow.vue'),
    CpConfirm: require('../../cp-components-common/CpConfirm.vue'),
    CpEditor: require('../../cp-components-common/inputs/CpEditor.vue')
  }
}
</script>

<style lang="scss">
.announcement-wrapper {
    .cp-table-standard {
        margin-top: 15px;
    }
}
</style>
