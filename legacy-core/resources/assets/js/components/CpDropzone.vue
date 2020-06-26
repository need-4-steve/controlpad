<template lang="html">
  <div class="dropzone-wrapper">
      <div id="dropzoneImport">
          <form action="{{api_src}}" method="POST" class="dropzone" :id="id">
              <input type="hidden" name="userId" :value="selected.id" :selected="selected">
              <input type="hidden" name="replace" v-model="replace">
              <input type="hidden" id="token" name="_token">
          </form>
      </div>
  </div>
</template>

<script>
const Dropzone = require('dropzone')

module.exports = {
  data: function () {
    return {
      addedFile: [],
      uploadZone: {}
    }
  },
  props: {
    imported: {
      type: Boolean,
      twoWay: true
    },
    import_steps: {
      type: Boolean,
      twoWay: true
    },
    selected: {
      type: Object
    },
    replace: {
      type: Boolean,
      twoWay: true
    },
    api_src: {
      required: true,
      type: String
    },
    default_message: {
      default: 'Drop your file to upload or click to browse',
      type: String
    },
    file_type: {
      required: true,
      type: String
    },
    id: {
      required: true,
      type: String
    },
    remove: {
      default: 'Remove file',
      type: String
    },
    setting_value: {
      default: '',
      twoWay: true,
      type: String
    }
  },
  computed: {},
  ready: function () {
    this.getDropzone()
  },
  methods: {
    getDropzone: function () {
      var thisDropzone = this
      var dropzoneConfig = {
        maxFileSize: 10,
        maxFiles: 1,
        acceptedFiles: this.file_type,
        dictDefaultMessage: this.default_message,
        dictMaxFilesExceeded: 'You may only upload one file at a time.',
        addRemoveLinks: true,
        dictRemoveFile: this.remove.default,
        headers: {},
        init: function () {
          this.on('error', function (file, response) {
            Dropzone.forElement('#' + thisDropzone.id).removeAllFiles()
            thisDropzone.$toast(response, { dismiss: false, error: true })
          })
        },
        success: function (file, response) {
          if (file.status === 'success') {
            thisDropzone.imported = true
            thisDropzone.import_steps = false
            Dropzone.forElement('#' + thisDropzone.id).removeAllFiles()
            thisDropzone.$toast('Import successful.', {dismiss: false, error: false})
          }
          thisDropzone.fileId = response.id
          thisDropzone.addedFile.push({id: response.id})
          thisDropzone.setting_value = response.url_sm
        }
      }
      this.uploadZone = new Dropzone('#' + this.id, dropzoneConfig)

      Dropzone.options.dropzoneInventoryImport = dropzoneConfig
      Dropzone.options.dropzoneMySettings = dropzoneConfig
    }
  }
}
</script>

<style lang="sass">
    .dropzone-wrapper {

    }
</style>
