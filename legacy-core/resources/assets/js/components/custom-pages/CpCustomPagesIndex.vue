<template>
  <div class="custom-page-wrapper">
        <table class="cp-table-standard">
            <thead>
                <th>Title</th>
                <th>URL</th>
                <th>Updated At</th>
            </thead>
            <tbody>
                <tr v-for="(page, index) in pages">
                    <td><a :href="'/pages/edit/' + page.slug">{{ page.title }}</a></td>
                    <td>{{ page.slug }}</td>
                    <td>{{ page.updated_at | cpStandardDate}}</td>
                </tr>
            </tbody>
        </table>
  </div>
</template>

<script>
const Settings = require('../../resources/settings.js')

module.exports = {
  data: function () {
    return {
      pages: []
    }
  },
  mounted: function () {
    this.getPages()
  },
  methods: {
    getPages: function () {
      Settings.customPages()
        .then((response) => {
          if (response.error) {
            this.$toast(response.message, {error: true})
          }
          this.pages = response
        })
    }
  }
}
</script>

<style lang="scss">
.custom-page-wrapper {
    .cp-table-standard {
        padding: 0px;
    }
}
</style>
