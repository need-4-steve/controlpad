<template lang="html">
  <div class="release-notes-wrapper">
    <h2>Release Notes:</h2>
    <div class="cp-panel-standard">
        <ul>
          <div v-for="(note, index) in releaseNotes">
            <li v-if="note.merged_at">{{ note.title }} - ({{ note.updated_at | cpStandardDate }})</li>
          </div>
        </ul>
    </div>
    <div class="align-center">
        <img class="loading" :src="$getGlobal('loading_icon').value" v-if="loading">
    </div>
  </div>
</template>

<script>
const Git = require('../../resources/gitlab.js')
module.exports = {
  data () {
    return {
      releaseNotes: [],
      loading: false
    }
  },
  mounted () {
    this.gitReleaseNotes()
  },
  methods: {
    gitReleaseNotes () {
      this.loading = true
      Git.gitlabReleaseNotes()
        .then((response) => {
          this.releaseNotes = response
          this.loading = false
        })
    }
  },
  components: {
  }
}
</script>

<style lang="scss">
@import "resources/assets/sass/var.scss";
.release-notes-wrapper {

}

</style>
