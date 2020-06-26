<template lang="html">
    <div class="media-upload-wrapper">
        <h4>Upload Media File</h4>
        <section class="media-file">
            <div>
                <form action="/api/v1/media/process" method="POST" class="dropzone" id="createMediaZone">
                    <input type="hidden" name="title" :value="media.title">
                    <input type="hidden" name="description" :value="media.description">
                    <input type="hidden" name="is_public" :value="media.is_public">
                </form>
            </div>
        </section>
        <form class="cp-form-standard media-details">
            <label>Title</label>
            <input type="text" name="title" v-model="media.title">
            <span v-show="errorMessages.title" class="cp-warning-message">{{ errorMessages.title }}</span>
            <label>Description</label>
            <input type="text" name="description" v-model="media.description">
            <span v-show="errorMessages.description" class="cp-warning-message">{{ errorMessages.description }}</span>
            <div class="media-checkbox" v-if="Auth.hasAnyRole('Superadmin', 'Admin')">
                <input type="checkbox" name="is_public" v-model="media.is_public"><label>Make available for {{$getGlobal('title_rep').value}}s</label>
            </div>
        </form>
        <button class="cp-button-standard close-button" @click="uploadMedia()" v-if="!uploading">Upload</button>
        <button class="cp-button-standard close-button" v-else>Uploading . . .</button>
    </div>
</template>

<script>
const Dropzone = require('dropzone')
const Media = require('../../resources/media.js')
const Auth = require('auth')

module.exports = {
  data: function () {
    return {
      media: {
        description: '',
        title: '',
        is_public: 0
      },
      errorMessages: {},
      uploading: false,
      uploadZone: {},
      Auth: Auth
    }
  },
  props: {
    modalDisplay: {
      type: Boolean,
      required: false,
      default: false
    },
    getMedia: {
      type: Function
    }
  },
  mounted: function () {
    this.uploadDropzone()
  },
  methods: {
    uploadDropzone: function () {
      var $this = this
      var uploadZoneConfig = Media.dropzoneConfig('.mp4, .MP4, .mp3')
      uploadZoneConfig.success = function (file, response) {
        $this.media = response
        $this.uploading = false
        $this.$toast('File successfully uploaded.')
        $this.$emit('input', false)
      }
      uploadZoneConfig.queuecomplete = function (file, resposne) {
        $this.getMedia()
      }
      uploadZoneConfig.error = function (file, response) {
        $this.uploading = false
        $this.$toast('There was an error uploading your image. Try again or contact support.', { error: true })
      }
      uploadZoneConfig.autoProcessQueue = false
      this.uploadZone = new Dropzone('#createMediaZone', uploadZoneConfig)
    },
    uploadMedia: function () {
      if (this.uploadZone.files.length < 1) {
        this.$toast('No file selected.', { error: true })
        return
      }
      this.uploading = true
      this.uploadZone.processQueue()
    }
  },
  components: {
  }
}
</script>

<style lang="scss">
.media-upload-wrapper {
    .media-details {
        margin-bottom: 15px;
        input {
            margin-top: 5px;
            margin-bottom: 10px;
        }
        .media-checkbox {
            overflow: hidden;
            width: 50%;
            input {
                margin: 5px;
                width: auto;
                display: inline-block;
            }
            label {
                width: 48%;
                margin: 5px;
                display: inline-block;
            }
        }
    }
    .media-file {
        margin-bottom: 15px;
        text-align: center;
    }
}
</style>
