<template lang="html">
    <div class="markdown-wrapper">
        <div class="editor-wrapper">
            <form class="cp-form-standard" @submit.prevent>
                <label>Title</label>
                <input :class="{error: errorMessages.title}" type="text" v-model="page.title">
                <span v-show="errorMessages.title" class="cp-warning-message">{{ errorMessages.title }}</span>
                <div class="editor-wrapper">
                    <label for="">Page Content</label>
                    <cp-editor v-if="loaded" v-model="page.content"></cp-editor>
                </div>
                <button  v-show="isRepTerms" class="cp-button-standard save-button" @click="saveRequireDocument()">Save and Require Acceptance</button>
                <button class="cp-button-standard save-button" @click="saveDocument()">Save</button>
                <button class="cp-button-standard save-button" @click="showPreviewModal = true">Preview Document</button>
            </form>
        </div>
        <section class="cp-modal-standard" v-if="showPreviewModal" @click="showPreviewModal = false" transition="modal">
            <div class="cp-modal-body markdown-preview" @click.stop.prevent>
              <div class="live-preview" v-html="page.content"></div>
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
    data: function () {
      return {
        errorMessages: {},
        showPreviewModal: false,
        loaded: false,
        isRepTerms: false,
        page: {
          title: '',
          content: ''
        }
      }
    },
    props: {
      slug: null
    },
    mounted () {
      this.slug === 'rep-terms' ? this.isRepTerms = true : this.isRepTerms = false
      this.getPages()
    },
    methods: {
      getPages () {
        Settings
          .getCustomPage(this.slug)
          .then(page => {
            this.loaded = true
            this.page = page
          })
      },
      saveDocument: function () {
        Settings.saveCustomPage(this.page)
          .then((response) => {
            if (response.error) {
              this.errorMessages = response.message
              return
            }
            this.$toast('Saved successfully.', { dismiss: false })
          })
      },
      saveRequireDocument () {
        Settings.saveRequireCustomPage(this.page)
          .then((response) => {
            if (response.error) {
              this.errorMessages = response.message
              return
            }
            this.$toast('Saved successfully.', { dismiss: false })
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
  // @import "resources/assets/sass/var.scss";
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
      display: flex;
      flex-direction: column;
      .live-preview {
        flex: 1;
        overflow: auto;
        padding: 10px;
        border:solid 1px #ddd;
      }
      .cp-modal-controls {
        padding-top: 10px;
      }
    }
  }
</style>
