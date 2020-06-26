<template lang="html">
    <div class="user-index-wrapper">
        <div>
          <a class="cp-button-standard" href="/users/create">New User</a>
            <a class="cp-button-standard download" download :href="'/api/v1/user/csv?search_term=' +indexRequest.search_term +
                '&column=' + indexRequest.column +
              '&order=' + indexRequest.order +
              '&role=' + indexRequest.role +
              '&status=' + indexRequest.status +
              '&page=' + indexRequest.page">Download CSV</a>
            <a v-show="showBankDownload" class="cp-button-standard download" download href="/api/v1/banking/csv/all-users">Download Banking CSV</a>
    <cp-tabs
     :items="[
       { name: 'ALL', active: true },
       { name: 'ADMINS', active: false },
       { name: $getGlobal('title_rep').value.toUpperCase() + 'S', active: false },
       { name: 'CUSTOMERS', active: false },
     ]"
     :callback="selectUsers"></cp-tabs>
    <cp-tabs v-if="activeTag == 'rep' || Auth.hasAnyRole('Superadmin') && activeTag == 'admin'"
     :items="userStatuses"
     custom-class="cp-tabs-light"
     :callback="selectUserStatus"></cp-tabs>
        <cp-table-controls
          :date-picker="false"
          :index-request="indexRequest"
          :resource-info="pagination"
          :get-records="getUsers"></cp-table-controls>
        <div v-if="activeTag == 'rep' || Auth.hasAnyRole('Superadmin') && activeTag == 'admin'" class="status-select">
             <select  v-model="statusUpdate" @change="updateStatuses()" :class="{ 'disable-select-box': disableSelectBox }">
                 <option :value='null' selected disabled>Change Status to:</option>
                 <option v-for="status in statuses" :value="status.name">{{status.name.charAt(0).toUpperCase() + status.name.slice(1)}}</option>
             </select>
         </div>
        <table class="cp-table-standard desktop">
            <thead>
                <tr>
                    <th v-if="activeTag == 'rep' || Auth.hasAnyRole('Superadmin') && activeTag == 'admin'"><!--checkbox--></th>
                    <th @click="sortColumn('last_name')">Name
                        <span v-show="indexRequest.column == 'last_name'">
                            <span v-show="reverseSort"><i class='mdi mdi-sort-ascending'></i></span>
                            <span v-show="!reverseSort"><i class='mdi mdi-sort-descending'></i></span>
                        </span>
                    </th>
                    <th @click="sortColumn('id')">ID
                        <span v-show="indexRequest.column == 'id'">
                            <span v-show="reverseSort"><i class='mdi mdi-sort-ascending'></i></span>
                            <span v-show="!reverseSort"><i class='mdi mdi-sort-descending'></i></span>
                        </span>
                    </th>
                    <th @click="sortColumn('state')">State
                        <span v-show="indexRequest.column == 'state'">
                            <span v-show="reverseSort"><i class='mdi mdi-sort-ascending'></i></span>
                            <span v-show="!reverseSort"><i class='mdi mdi-sort-descending'></i></span>
                        </span>
                    </th>
                    <th>Phone</th>
                    <th v-if="activeTag != 'rep'">Role</th>
                    <th v-else @click="sortColumn('rep_type')">Role
                        <span v-show="indexRequest.column == 'rep_type'">
                            <span v-show="reverseSort"><i class='mdi mdi-sort-ascending'></i></span>
                            <span v-show="!reverseSort"><i class='mdi mdi-sort-descending'></i></span>
                        </span>
                    </th>
                    <th v-if="activeTag == 'rep' || Auth.hasAnyRole('Superadmin') && activeTag == 'admin'" @click="sortColumn('status')">Status
                        <span v-show="indexRequest.column == 'status'">
                            <span v-show="reverseSort"><i class='mdi mdi-sort-ascending'></i></span>
                            <span v-show="!reverseSort"><i class='mdi mdi-sort-descending'></i></span>
                        </span>
                    </th>
                    <th @click="sortColumn('join_date')">Join Date
                        <span v-show="indexRequest.column == 'join_date'">
                            <span v-show="reverseSort"><i class='mdi mdi-sort-ascending'></i></span>
                            <span v-show="!reverseSort"><i class='mdi mdi-sort-descending'></i></span>
                        </span>
                    </th>
                    <th v-show="activeTag === 'rep'">Bank Status
                    </th>
                </tr>
            </thead>
            <tbody v-if="!loading">
                <tr v-for="user in users">
                    <td v-if="activeTag == 'rep' || Auth.hasAnyRole('Superadmin') && activeTag == 'admin'"><input type="checkbox" :id="user.id" :value="user.id" v-model="usersToUpdate" @click="checkUsersLength()"/></td>
                    <td><a :href="'/my-settings/' + user.id">{{user.last_name}}, {{user.first_name}}</a></td>
                    <td>{{user.id}}</td>
                    <td>{{user.state}}</td>
                    <td>{{user.phone_number | phone}}</td>

                    <td>
                        <span v-if="user.role.name === 'Rep' && user.seller_type_id === 1">Affiliate</span>
                        <span v-if="user.role.name === 'Rep' && user.seller_type_id === 2">Reseller</span>
                        <span v-if="user.role.name !== 'Rep'">{{user.role.name}}</span>
                    </td>
                    <td v-if="activeTag == 'rep' || Auth.hasAnyRole('Superadmin') && activeTag == 'admin'">{{user.status.charAt(0).toUpperCase() + user.status.slice(1)}}</td>
                    <td v-if="Auth.hasAnyRole('Superadmin') && $getGlobal('edit_join_date').show"><cp-datetime :value="moment.utc(user.join_date).local().format('YYYY-MM-DD HH:mm:ss')" type="date" @input="confirmJoinDate(user, $event)"></cp-datetime></td>
                    <td v-else>{{ user.join_date | cpStandardDate(0, 0) }}</td>
                    <td v-show="activeTag === 'rep'">
                        <span v-if="user.role_id == 5 && user.verified == 1">Verified</span>
                        <span v-else>Unverified</span>
                    </td>
                </tr>
                <cp-confirm
                :message="'Are you sure you want to change the join date of ' + newDate.name  + ' to ' + moment(newDate.join_date).format('L') "
                v-model="showConfirmDate"
                :show="showConfirmDate"
                :callback="saveJoinDate"
                :params="{}"></cp-confirm>
            </tbody>
        </table>
<section class="cp-table-mobile">
  <div v-for="user in users" >
  <div><span>Name: </span><span><a :href="'/my-settings/'+ user.id">{{user.last_name}}, {{user.first_name}}</a></span></div>
  <div><span>ID: </span><span>{{user.id}}</span></div>
  <div><span>State: </span><span>{{user.state}}</span></div>
  <div><span>Phone: </span><span>{{user.phone_number | phone}}</span></div>
  <div><span>Role: </span><span>
    <span v-if="user.role.name === 'Rep' && user.seller_type_id === 1">Affiliate</span>
    <span v-if="user.role.name === 'Rep' && user.seller_type_id === 2">Reseller</span>
    <span v-if="user.role.name !== 'Rep'">{{user.role.name}}</span></span>
  </div>
  <div><span>Join Date: </span><span>{{user.join_date | cpStandardDate(0, 0) }}</span></div>
</div>
</section>
        <div class="align-center">
            <img class="loading" :src="$getGlobal('loading_icon').value" v-if="loading">
            <cp-pagination :pagination="pagination" :callback="getUsers" :offset="2"></cp-pagination>
        </div>
    </div>

</div>
</template>

<script>
const Users = require('../../resources/users.js')
const Auth = require('auth')
const UserStatus = require('../../resources/user-status.js')
const moment = require('moment')
const _ = require('lodash')

module.exports = {
  data: function () {
    return {
      Auth: Auth,
      moment: moment,
      loading: false,
      users: [],
      pagination: {
        current_page: 1
      },
      reverseSort: false,
      userCount: 0,
      activeTag: 'all',
      activeUser: {
        all: true,
        admin: false,
        rep: false,
        customer: false
      },
      selectedUser: {},
      showConfirm: false,
      showConfirmDate: false,
      newDate: {},
      showBankDownload: false,
      userStatuses: [
       { name: 'ALL', active: true }
      ],
      disableSelectBox: true,
      usersToUpdate: [],
      statusUpdate: null,
      statuses: [],
      indexRequest: {
        search_term: null,
        column: 'last_name',
        order: 'ASC',
        role: '',
        page: 1,
        per_page: 15,
        status: ''
      }
    }
  },
  filters: {
    phone (number) {
      if (number) {
        return number.replace(/(\d{3})(\d{3})(\d{4})/, "($1) $2-$3");
      }
    }
  },
  mounted: function () {
    Auth.hasAnyRole('Superadmin', 'Admin') ? this.indexRequest.per_page = '100' : this.indexRequest.per_page = '15'
    Auth.hasAnyRole('Superadmin') ? this.showBankDownload = true : this.showBankDownload = false
    this.getUserStatus()
    this.getUsers()
  },
  methods: {
    checkUsersLength: _.debounce(function () {
      if (this.usersToUpdate.length >= 1) {
        this.disableSelectBox = false
      } else {
        this.disableSelectBox = true
      }
    }, 100),
    confirmJoinDate (user, joinDate) {
      this.showConfirmDate = true
      this.newDate = {
        // Hard coded hours to 12 so that the utc offset will still be on the chosen date
        join_date: moment(joinDate).format('YYYY-MM-DD 12:mm:ss'),
        user_id: user.id,
        name: user.first_name + ' ' + user.last_name
      }
    },
    selectUsers (name) {
      this.resetUserStatuses()
      switch (name) {
        case 'ALL':
          this.getUsers(this.indexRequest.role = '', this.pagination.current_page = 1)
          this.setActiveClass('all')
          this.activeTag = 'all'
          break
        case 'ADMINS':
          this.getUsers(this.indexRequest.role = 7, this.pagination.current_page = 1)
          this.activeTag = 'admin'
          break
        case this.$getGlobal('title_rep').value.toUpperCase() + 'S':
          this.getUsers(this.indexRequest.role = 5, this.pagination.current_page = 1)
          this.activeTag = 'rep'
          break
        case 'CUSTOMERS':
          this.getUsers(this.indexRequest.role = 3, this.pagination.current_page = 1)
          this.activeTag = 'customer'
          break
        default: }
    },
    selectUserStatus (name) {
      this.indexRequest.page = 1
      if (name == 'ALL') {
        this.indexRequest.status = ''
      } else {
        this.indexRequest.status = name.toLowerCase()
      }
      this.getUsers()
    },
    getUsers: function () {
      this.loading = true
      this.indexRequest.page = this.pagination.current_page
      Users.index(this.indexRequest)
        .then((response) => {
          if (!response.error) {
            this.pagination = response
            this.userCount = response.total
            this.users = response.data
            this.loading = false
            return response
          }
        })
    },
    getUserStatus: function () {
      UserStatus.getIndex()
      .then((response) => {
        for (var item in response) {
          this.statuses.push(response[item])
          if (response[item].visible) {
            this.userStatuses.push({
              name: response[item].name.toUpperCase(),
              active: false
            })
          }
        }
      })
    },
    updateStatuses () {
      if (this.usersToUpdate.length > 0) {
        UserStatus.updateStatuses({ users: this.usersToUpdate, status: this.statusUpdate})
          .then((response) => {
          if (response.error) {
            return this.$toast(response.message)
          }
          this.usersToUpdate = []
          this.statusUpdate = null
          this.disableSelectBox = true
          this.getUsers()
          return this.$toast('statuses updated', {dismiss: false})
        })
      }
    },
    resetUserStatuses () {
      this.indexRequest.status = ''
      for (status in this.userStatuses) {
        if (this.userStatuses[status].name === 'ALL') {
          this.userStatuses[status].active = true
        } else {
          this.userStatuses[status].active = false
        }
      }
    },
    deleteUser: function () {
      Users.softDeleteUser({ids: [this.selectedUser.id]})
        .then((response) => {
        if (!response.error) {
          this.$toast(this.selectedUser.role.name + ', ' + this.selectedUser.full_name + ' has been deleted.')
          this.showConfirm = false
          return this.getUsers(this.indexRequest)
        }
        return this.$toast(response.message)
      })
    },
    saveJoinDate: function () {
      Users.editJoinDate(this.newDate)
        .then((response) => {
          if (response.error) {
            this.$toast(response.message + ' ' + this.newDate.join_date + ' for ' + this.newDate.name, { error: true, dismiss: true })
            return this.getUsers(this.indexRequest)
          } else {
            this.$toast(this.newDate.name + ' was updated', {dismiss: true})
          }
        })
    },
    setActiveClass: function (user) {
      this.activeUser = {
        all: false,
        admin: false,
        rep: false,
        customer: false
      }
      switch (user) {
        case 'all':
          this.activeUser.all = true
          this.activeTag = 'all'
          break
        case 'admin':
          this.activeUser.admin = true
          this.admin = true
          this.activeTag = 'admin'
          break
        case 'rep':
          this.activeUser.rep = true
          this.rep = true
          this.activeTag = 'rep'
          break
        case 'customer':
          this.activeUser.customer = true
          this.customer = true
          this.activeTag = 'customer'
          break
      }
    },
    sortColumn: function (column) {
      this.reverseSort = !this.reverseSort
      this.indexRequest.column = column
      this.asc = !this.asc
      if (this.asc === true) {
        this.indexRequest.order = 'asc'
      } else {
        this.indexRequest.order = 'desc'
      }
      this.getUsers()
    }
  },
  components: {
    'CpConfirm': require('../../cp-components-common/CpConfirm.vue'),
    CpTableControls: require('../../cp-components-common/tables/CpTableControls.vue'),
    CpTabs: require('../../cp-components-common/navigation/CpTabs.vue')

  }
}
</script>

<style lang="sass">
    @import "resources/assets/sass/var.scss";
    .user-index-wrapper {
        .disable-select-box{
          pointer-events: none;
          color: $cp-lightGrey;
        }
        .cp-button-standard {
            color: white;
            & a {
                color: white;
                text-decoration: none;
            }
        }
        .download {
            margin-left: 5px;
            text-decoration: none;
        }
        .top-div {
            margin-bottom: 5px;
            & span {
                font-size: 16px;
            }
        }

    }
</style>
