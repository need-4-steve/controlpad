<template lang="html">
  <div class="dropzone-wrapper">
      <div id="dropzoneImport">
          <form :action="api_src" method="POST" class="dropzone" :id="id">
              <input type="hidden" name="userId" :value="selected.id" :selected="selected">
              <input type="hidden" name="replace" v-model="replace">
              <input type="hidden" id="token" name="_token">
          </form>
      </div>
  </div>
</template>

<script>
const Dropzone = require('dropzone')

Dropzone.autoDiscover = false

module.exports = {
  data: function () {
    return {
      addedFile: [],
      uploadZone: {}
    }
  },
  props: {
    imported: {
      type: Boolean
    },
    import_steps: {
      type: Boolean
    },
    selected: {
      type: Object
    },
    replace: {
      type: Boolean
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
      type: String
    }
  },
  computed: {},
  mounted: function () {
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
        beforeCreate: function () {
          this.on('removedfile', function (id, index) {
            thisDropzone.addedFile.splice(index, 1)
          })
        },
        success: function (file, response) {
          if (file.status === 'success') {
            thisDropzone.imported = true
            thisDropzone.import_steps = false
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
