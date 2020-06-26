<template>
  <div id="cp-image-upload-wrapper" class="cp-box-standard">
    <div class="cp-box-body">

      <span style="display: flex;"><h1 v-if="title !== undefined">{{title}}</h1> <cp-tooltip v-if="tooltip" :options="{ content: tooltip}"></cp-tooltip></span>
      <div v-if="currentImage">
        <img class="currentImage" :src="currentImage" alt="">
      </div>
      <section class="">
        <hr />
        <form action="/upload-media" method="POST" class="dropzone" :id="dropZoneId" :class="{ 'image-error': validationErrors['images'] }">
            <input type="hidden" name="title">
            <input type="hidden" name="description">
            <input type="hidden" name="is_public">
        </form>
        <span
          :class="{ 'cp-validation-errors': validationErrors['images'] }"
          v-if="validationErrors['images']">{{ validationErrors['images'][0] }}</span>
      </section>
    </div>
  </div>
</template>
<script>
const Dropzone = require('dropzone')
const Media = require('../../resources/media.js')

module.exports = {
  data: () => ({
    media: '',
    validationErrors: {}
  }),
  props: {
    dropZoneId: {
      type: String,
      default: 'createMediaZone'
    },
    currentImage: {
      type: String,
      default: ''
    },
    title: {
      type: String
    },
    tooltip: {
      type: String
    }
  },
  mounted () {
    this.uploadDropzone()
    Dropzone.autoDiscover = false
  },
  methods: {
    deleteMedia (image, index) {
      this.media = ''
      this.$toast('Image has been deleted.')
      this.$emit('new-media', this.media)
    },
    uploadDropzone () {
      let $this = this
      var uploadZoneConfig = Media.dropzoneConfig('.mp4, .MP4, .mp3')
      uploadZoneConfig.maxFiles = 1
      uploadZoneConfig.success = function (file, response) {
        $this.media = response
        $this.$toast('File successfully uploaded.')
        $this.$emit('new-media', $this.media.url, $this.dropZoneId)
      }
      uploadZoneConfig.error = function (file, response) {
        $this.$toast('There was an error uploading your image. Try again or contact support.', { error: true })
      }
      $this.uploadZone = new Dropzone('#' + $this.dropZoneId, uploadZoneConfig)
    }
  },
  components: {
    CpTooltip: require('../../custom-plugins/CpTooltip.vue')
  }
}
</script>
<style lang="scss" scoped>
#cp-image-upload-wrapper {
  .currentImage {
    height: auto;
    width: 120px;
  }
}
</style>
