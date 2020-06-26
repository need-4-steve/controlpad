<template>
    <div class="cp-history-index">
        <table class="cp-table-standard">
            <thead>
                <th>Username</th>
                <th>Action</th>
                <th>Model</th>
                <th>Model ID</th>
                <th>Model Name</th>
                <th>Before</th>
                <th>After</th>
                <th>IP</th>
                <th>Updated At</th>
            </thead>
            <tbody>
                <tr v-for="(history, index) in history">
                    <td v-if="history.username">{{ history.username.full_name}}</td>
                    <td v-else>Action taken by non-registered actor.</td>
                    <td>{{ history.action }}</td>
                    <td>{{ history.historable_type }}</td>
                    <td>{{ history.historable_id }}</td>
                    <td>{{ history.model_name || "N/A"}}</td>
                    <td>
                        <ul v-if="history.before" class="history-sublist">
                            <li v-for="(value, key) in history.before">
                                <strong>{{ key }}:</strong> {{ value }}
                            </li>
                        </ul>
                    </td>
                    <td>
                        <ul v-if="history.after" class="history-sublist">
                            <li v-for="(value, key) in history.after">
                                <strong>{{ key }}:</strong> {{ value }}
                            </li>
                        </ul>
                     </td>
                    <td>{{ history.ip }}</td>
                    <td>{{ history.updated_at | cpStandardDate(['time']) }}</td>
                </tr>
            </tbody>
        </table>
        <div class="align-center">
            <img class="loading" :src="$getGlobal('loading_icon').value" v-if="loading">
            <cp-pagination :pagination="pagination" :callback="getHistory" :options="{offset:2}"></cp-pagination>
        </div>
    </div>
</template>

<script>
const History = require('../../resources/history.js')

module.exports = {
  data: function () {
    return {
      loading: true,
      history: [],
      pagination: {},
      asc: false,
      indexRequest: {
        order: 'ASC',
        column: 'name',
        per_page: 15,
        search_term: '',
        page: 1
      },
      reverseSort: false
    }
  },
  computed: {},
  mounted: function () {
    this.getHistory()
  },
  methods: {
    getHistory: function () {
      this.indexRequest.page = this.pagination.current_page
      History.index(this.indexRequest)
              .then((response) => {
                if (response.error) {
                  return this.$toast(response.message, {error: true})
                }
                this.loading = false
                this.history = response.data
                this.pagination = response
              })
    }
  }
}
</script>

<style lang="scss">
.cp-history-index {
  .history-sublist {
    list-style: none;
    text-align: left;
  }
  td {
    max-width: 200px;
    word-wrap: break-word;
  }
}
</style>
