<template lang="html">
    <div class="markdown-wrapper">
        <div class="editor-wrapper">
          <p>Please edit the subject and body of the email in the fields below. You may use the following variables to personalize the email for the recipient.</p>
          <ul v-for="variable in variables">
            <li>{{variable}}</li>
          </ul>
            <form class="cp-form-standard" @submit.prevent>
              <div class="">
                <h1>{{ email.display_name }}</h1>
                <span v-show="errorMessages.subject" class="cp-warning-message">{{ errorMessages.subject }}</span>
                <label>Subject</label>
                <input :class="{error: errorMessages.subject}" type="text" v-model="email.subject">
                <span v-show="errorMessages.subject" class="cp-warning-message">{{ errorMessages.subject }}</span>
              </div>
              <cp-editor v-if="loaded" v-model="email.body"></cp-editor>
              <button class="cp-button-standard save-button" @click="saveEmail()">Save</button>
              <button class="cp-button-standard save-button" @click="showPreviewModal = true, emailContent()">Preview Document</button>
            </form>
        </div>
        <section class="cp-modal-standard" v-if="showPreviewModal" @click="showPreviewModal = false" transition="modal">
            <div class="cp-modal-body markdown-preview" @click.stop.prevent>
              <div class="cp-panel-standard">
                <p>Please Note: The following display is a preview of how the email will be sent to the recipient. The actual display of the content may vary based on different email clients, browsers, operating systems, or other factors. </p>
              </div>
              <div class="live-preview" v-html="content"></div>
              <div class="cp-modal-controls">
                <button class="cp-button-standard right" @click="showPreviewModal = false">Close</button>
              </div>
            </div>
        </section>
    </div>
</template>

<script>
const Settings = require('../../resources/settings.js')
const marked = require('marked')

module.exports = {
  data () {
    return {
      errorMessages: {},
      showPreviewModal: false,
      content: '',
      variables: {},
      email: {},
      loaded: false
    }
  },
  props: {
    slug: null
  },
  mounted () {
    this.getEmail()
  },
  methods: {
    getEmail () {
      Settings
        .getCustomEmailBySlug(this.slug)
        .then((res) => {
          if(res.error) {
            this.errorMessages = res.message
            return
          }
          this.email = res
          this.loaded = true
          this.emailContent()
        })
    },
    saveEmail () {
      Settings.saveCustomEmail(this.email.title, this.email)
        .then((response) => {
          if (response.error) {
            this.errorMessages = response.message
            return
          }
          this.$toast('Saved successfully.', { dismiss: false })
        })
    },
    emailContent () {
      Settings.showcontent(this.slug, this.email)
        .then((response) => {
          if (!response.error) {
            this.content = response.content
            this.variables = response.var
          }
        })
    }
  },
  filters: {
    marked: marked
  },
  components: {
    CpEditor: require('../../cp-components-common/inputs/CpEditor.vue')
  }
}
</script>

<style lang="scss">
    @import "resources/assets/sass/var.scss";
    .markdown-wrapper {
        .panel-heading {
            background: #f2f2f2 !important;
        }
        .note-palette-title {
            text-align: center;
            padding: 5px;
            color: black;
        }
        .note-color-reset {
            text-align: center;
            color: black !important;
        }
        .save-button {
            float: right;
            margin-top: 10px;
            margin-left: 5px;
        }
        .editor-wrapper {
          margin-top: 10px;
        }
        .markdown-preview {
          height: auto;
          max-height: 85%;
          overflow-y: auto;
        }
    }
</style>
