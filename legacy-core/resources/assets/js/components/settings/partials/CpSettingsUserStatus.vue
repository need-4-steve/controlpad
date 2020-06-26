<template lang="html">
    <div class="user-settings-wrapper">
        <div class="">
            <div class="panel item-list">
                <div class="panel-heading">
                    <h2 class="panel-title align-left">User Status Settings</h2>
                </div>
                <table class="cp-table-standard desktop table-setting">
                    <thead>
                        <th>Position</th>
                        <th>Name</th>
                                                <th>Display <cp-tooltip :options="{ content: 'When unchecked it will hide the status from the users page for admins.'}"></cp-tooltip></th>
                        <th>Sign in <cp-tooltip :options="{ content: 'When unchecked it will prevent users from logging in.'}"></cp-tooltip></th>
                        <th>Buy <cp-tooltip :options="{ content: 'When unchecked it will prevent users from buying on Inventory Purchase.'}"></cp-tooltip></th>
                        <th>Sell <cp-tooltip :options="{ content: 'When unchecked it will prevent users from selling. This includes Custom Order and selling on their replicated site. Replicated sites will then be redirected to the Sell Redirect Url.'}"></cp-tooltip></th>
                        <th>Renew <cp-tooltip :options="{ content: 'This will set the subscription auto renew on/off for users depending on having Renew checked or not.'}"></cp-tooltip></th>
                        <th v-if="$getGlobal('rep_locator_enable').value">Rep Locator <cp-tooltip :options="{ content: 'When unchecked it will prevent users from showing up in the Rep Locator v2.'}"></cp-tooltip></th>
                        <th><!-- column for delete button --></th>
                    </thead>
                    <tbody>
                        <tr v-for="(status, index) in userStatuses">
                            <td>
                                <input class="align-center status-position input-class" type="number" v-model="status.position" @keyup="updateStatus(status)">
                            </td>
                            <td>
                                <span v-if="status.default == true"><input class="input-class" type="text" v-model="status.name" disabled></span>
                                <span v-else><input class="input-class" type="text" v-model="status.name" @keyup="updateStatusName(status)"></span>
                            </td>
                            <td>
                                <input type="checkbox" v-model="status.visible" @change="updateStatus(status)">
                            </td>
                            <td>
                                <input v-if="status.default == true" type="checkbox" v-model="status.login" disabled>
                                <input v-else type="checkbox" v-model="status.login" @change="updateStatus(status)">
                            </td>
                            <td>
                                <input v-if="status.default == true" type="checkbox" v-model="status.buy" disabled>
                                <input v-else type="checkbox" v-model="status.buy" @change="updateStatus(status)">
                            </td>
                            <td>
                                <input v-if="status.default == true" type="checkbox" v-model="status.sell" disabled>
                                <input v-else type="checkbox" v-model="status.sell" @change="updateStatus(status)">
                            </td>
                            <td>
                                <input v-if="status.default == true" type="checkbox" v-model="status.renew_subscription" disabled>
                                <input v-else type="checkbox" v-model="status.renew_subscription" @change="updateStatus(status)">
                            </td>
                            <td v-if="$getGlobal('rep_locator_enable').value">
                                <input v-if="status.default == true" type="checkbox" v-model="status.rep_locator" disabled>
                                <input v-else type="checkbox" v-model="status.rep_locator" @change="updateStatus(status)">
                            </td>
                            <td>
                                <span v-if="status.default != true"><i class="mdi mdi-close pointer" @click="deleteStatus(status.id, index)"></i></span>
                            </td>
                        </tr>
                        <tr style="background-color:white">
                            <td colspan="5"><button class="cp-button-standard" @click="addNew">+ Add New Status</button></td>
                        </tr>
                    </tbody>
                </table>
            </div>
                <div class="url-setting">
                    <div>
                        <cp-tooltip :options="{ content: 'When the User Status setting of Sell is unchecked, the user\'s replicated site will be redirected to the inputted url. The input must have http:// or https:// in it.'}"></cp-tooltip>
                        <cp-input label="Sell Redirect Url" custom-class="url-input" type="text" v-model="url_settings.user_status_sell_url.value" ></cp-input>
                            <div class="save-settings-button-special-case-because-of-stupid-reasons">
                                <button class="cp-button-standard" type="button" @click="saveUrl()">Save</button>
                            </div>
                        </div>
                    </div>
                </div>
        </div>
    </div>


</template>

<script>
const Settings = require('../../../resources/settings.js')
const UserStatus = require('../../../resources/user-status.js')
const _ = require('lodash')

module.exports = {
  data: function () {
    return {
      userStatuses: [],
      url_settings: {
          user_status_sell_url: []
      }
    }
  },
  mounted: function () {
    this.getUserStatus()
    this.getUrlSettings()
  },
  methods: {
    getUrlSettings: function () {
        var url_setting = this.$getGlobal('user_status_sell_url')
        this.url_settings['user_status_sell_url'] = {
            value: url_setting.value,
            show: url_setting.show
        }
    },
    getUserStatus: function () {
      UserStatus.getIndex()
      .then((response) => {
          for (var item in response) {
              this.userStatuses.push(response[item])
          }
      })
    },
    addNew: function () {
        var position = 0;
        for (index = 0; index < this.userStatuses.length; ++index) {
            if (position < this.userStatuses[index].position) {
                position = this.userStatuses[index].position
            }
        }
        this.userStatuses.push({
            id: null,
            name: "",
            position: Number(position) + 1,
            visible: false,
            login: false,
            buy: false,
            sell: false,
            renew_subscription: false,
            rep_locator: false
        })
    },
    updateStatusName: _.debounce(function (status) {
        this.updateStatus(status)
    }, 1000),
    updateStatus: function(status) {
        if (!status.id && status.name !== "") {
            UserStatus.create(status)
            .then((response) => {
                if (response.error) {
                    this.$toast(response.message, { error: true, dismiss: true })
                    return
                }
                this.$toast('User Status Created', { error: false, dismiss: false })
                status.id = response.id
            })
        } else if(status.id) {
            UserStatus.update(status.id, status)
            .then((response) => {
                if (response.error) {
                    this.$toast(response.message, { error: true, dismiss: true })
                    return
                }
                this.$toast('User Status Updated', { error: false, dismiss: false })
            })
        }
    },
    saveUrl: function () {
      Settings.update(this.url_settings)
        .then((response) => {
          if (response.error) {
            this.$toast(response.message, { error:true })
          }
          this.$toast('setting updated', { dismiss: false })
        })
    },
    deleteStatus: function (id, index) {
        if (id) {
            UserStatus.delete(id)
            .then((response) => {
                if (response.error) {
                    this.$toast(response.message, { error: true, dismiss: true })
                    return
                }
                this.userStatuses.splice(index, 1)
                this.$toast('User Status Deleted', { error: false, dismiss: false })
            })
        } else {
            this.userStatuses.splice(index, 1)
        }
    }
  },
  components: {
  }
}
</script>

<style lang="sass">
    // @import "resources/assets/sass/var.scss";
    .user-settings-wrapper {
        .save-settings-button-special-case-because-of-stupid-reasons{
            display: block;
            width: 100%;
            text-align: right;
            margin-top: 20px;
        }
        .cp-table-standard {
            padding: 0px;
            .status-position {
                width: 30px;
            }
            .delete-column {
                width: 50px;
                align: right;
            }
        }
        .table-setting {
            padding: 0px;
            border-top: 0px;
            th {
                background-color: $cp-lightGrey;
                color: black;
            }
        }
        .url-label {
            width: 125px;
        }
        .url-input {
            width: 65%;
        }
        .float-right {
            float: right;
        }
        .url-setting {
            padding-bottom: 20px;
        }
    }
</style>
