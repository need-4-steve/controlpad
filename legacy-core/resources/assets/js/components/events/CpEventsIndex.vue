<template>
  <div id="events-index-wrapper">
    <div class="space-between">
        <div>
            <button @click="newEvent()" class="cp-button-link">Create {{ eventTitle }}</button>
        </div>
    </div>
    <cp-tabs
     :items="[
       { name: 'OPEN', active: true },
       { name: 'CLOSED', active: false }
     ]"
     :callback="selectEventType"></cp-tabs>
    <cp-table-controls
    :date-picker="false"
    :index-request="indexRequest"
    :search-place-holder="searchPlaceholder"
    :resource-info="pagination"
    :get-records="getEvents"></cp-table-controls>
    <table class="cp-table-standard">
      <thead>
        <tr>
          <th>Id</th>
          <th>Name</th>
          <th>Location</th>
          <th>Status</th>
          <th>{{ eventTitle }} Orders</th>
          <th>Sale Start</th>
          <th>Sale End</th>
          <th><!-- placeholder for delete button --></th>
        </tr>
      </thead>
      <tbody v-for="(event, i) in events" :key="i">
        <tr>
          <td>{{event.id}}</td>
          <td><a @click="showEvent(event)">{{event.name}}</a></td>
          <td>{{event.location}}</td>
          <td>{{event.status}}</td>
          <td><a @click="showOrders(event)"> View Orders</a></td>
          <td>{{event.sale_start | cpStandardDate(true)}}</td>
          <td>{{event.sale_end | cpStandardDate(true)}}</td>
          <td v-if="Auth.hasAnyRole('Superadmin','Admin')"><i class="mdi mdi-close pointer" @click="confirmAndDelete(event.id)"></i></td>
          <td v-else></td>
        </tr>
      </tbody>
    </table>
    <cp-confirm
    :message="'Are you sure you want to delete this event?'"
    v-model="showConfirm"
    :show="showConfirm"
    :callback="deleteEvent"
    :params="{id:eventId}"></cp-confirm>
    <div class="align-center">
        <img class="loading" :src="$getGlobal('loading_icon').value" v-if="loading">
        <cp-pagination :pagination="pagination" :callback="getEvents" :offset="2"></cp-pagination>
    </div>
    <cp-dialog :open="eventsModal" @close="clearModal()">
      <h2 slot="header">
        {{ edit && eventsModal ? 'Edit' : eventsModal ? 'Create' : (eventTitle + ' Details') }}
      </h2>
      <template slot="content">
        <cp-events-form v-if="eventsModal" :event="selectedEvent" @close="clearModal" @add-event="addEvent" :edit="edit"></cp-events-form>
      </template>
    </cp-dialog>
  </div>
</template>
<script>
const Events = require('../../resources/EventsAPIv0.js')
const Auth = require('auth')
const moment = require('moment')

module.exports = {
  name: 'CpEventsIndex',
  routing: [
    {
      name: 'site.CpEventsIndex',
      path: '/events',
      meta: {
        title: 'Events'
      },
      props: true
    }
  ],
  data: () => ({
    Auth: Auth,
    loading: false,
    events: {},
    eventsTitle: 'Events',
    eventTitle: 'Event',
    searchPlaceholder: 'Search Events',
    indexRequest: {
      search_term: '',
      column: 'created_at',
      order: 'DESC',
      page: 1,
      per_page: 15,
      sponsor_id: Auth.getOwnerId(),
      status: 'open'
    },
    pagination: {},
    eventsModal: false,
    showConfirm: false,
    edit: false,
    eventId: null,
    selectedEvent: {
      date: moment().toString(),
      start_date: moment().toString()
    }
  }),
  created () {
      this.eventsTitle = this.$getGlobal('events_title').value.plural
      this.eventTitle = this.$getGlobal('events_title').value.single
      this.searchPlaceholder = 'Search ' + this.eventsTitle
  },
  mounted () {
    Auth.hasAnyRole('Superadmin', 'Admin') ? this.indexRequest.per_page = '100' : this.indexRequest.per_page = '15'
    this.getEvents()
  },
  methods: {
    newEvent () {
      this.eventsModal = true
      this.edit = false
      this.selectedEvent = {}
    },
    getEvents () {
      Events.getEvents(this.indexRequest)
        .then((response) => {
          this.pagination = response
          this.events = response.data
        })
    },
    showOrders (event) {
      this.$router.push({ name: 'CpEventsOrders', params: { eventProp: event, id: event.id.toString() }})
    },
    deleteEvent (event) {
      Events.deleteEvents(this.indexRequest, event.id)
        .then((response) => {
          this.$toast(response)
          this.getEvents()
        })
    },
    selectEventType (status) {
      switch (status) {
        case 'OPEN':
          this.indexRequest.status = status.toLowerCase()
          this.getEvents()
          break
        case 'CLOSED':
          this.indexRequest.status = status.toLowerCase()
          this.getEvents()
          break
        default:
      }
    },
    confirmAndDelete (id) {
      this.eventId = id
      this.showConfirm = true
    },
    showEvent (event) {
      this.selectedEvent = event
      this.eventsModal = true
      this.edit = true
    },
    clearModal () {
      this.eventsModal = false
      this.selectedEvent = {}
      this.edit = false
    },
    addEvent (event) {
      this.eventsModal = false
      this.getEvents()
    }
  },
  components: {
    CpTableControls: require('../../cp-components-common/tables/CpTableControls.vue'),
    CpTabs: require('../../cp-components-common/navigation/CpTabs.vue'),
    CpEventsForm: require('./CpEventsForm.vue'),
    CpConfirm: require('../../cp-components-common/CpConfirm.vue')
  }
}
</script>
<style lang="scss" scoped>
#events-index-wrapper {
  .flex{
    display: flex;
    justify-content: space-between;
  }
}
</style>
