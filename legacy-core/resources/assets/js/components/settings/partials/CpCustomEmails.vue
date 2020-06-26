<template>
  <div class="custom-email-wrapper">
      <div class="panel item-list">
        <div class="panel-heading">
        <h2  class="panel-title align-left">Emails</h2>
      </div>
        <table class="cp-table-standard table-setting">
            <thead>
                <th>Email Name</th>
                <th>Subject</th>
                <th>Send Email</th>
            </thead>
            <tbody>
                <tr v-for="(email, index) in emails">
                    <td><a :href="'/email/edit/' + email.title">{{ email.display_name }}</a></td>
                    <td>{{ email.subject }}</td>
                    <td><input class="toggle-switch" type="checkbox" v-model="email.send_email" @change="saveEmail(email.title, email)"></td>
                </tr>
            </tbody>
        </table>
      </div>
  </div>
</template>

<script>
const Settings = require('../../../resources/settings.js')

module.exports = {
  data: function () {
    return {
      emails: []
    }
  },
  mounted: function () {
    this.getEmails()
  },
  methods: {
    getEmails: function () {
      Settings.customEmails()
        .then((response) => {
          if (response.error) {
            this.$toast(response.message, {error: true})
          } else {
            this.emails = response
          }
        })
    },
    saveEmail: function (title, email) {
      Settings.saveCustomEmail(title, email)
        .then((response) => {
          if (response.error) {
            this.errorMessages = response.message
            return
          }
          this.$toast('Saved successfully.', { dismiss: false })
        })
    }
  }
}
</script>

<style lang="scss">
@import "resources/assets/sass/var.scss";
.custom-email-wrapper {
    .cp-table-standard {
        padding: 0px;
    }
    .table-setting {
        padding: 0px;
        border-top: 0px;
        th {
            background-color: $cp-lightGrey;
            color: black;
        }
    }
}
</style>
