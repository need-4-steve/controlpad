<template lang="html">
    <div class="cp-box-standard">
    <div class="cp-box-heading">
      PHOTOS
    </div>
    <div class="cp-box-body">
      <section class="current-media product-grid edit" v-if="currentMedia.length > 0">
        <h4>CURRENT</h4>
        <hr />
        <div class="item" v-for="(media, index) in newCurrentMedia" :key="media.id">
          <img v-if="media.url_sm" class="item_image" :src="media.url_sm">
          <img v-else class="item_image" :src="media.url">
          <span href="#" class="tile-overlay pointer" @click="deleteMedia(media, index)">DELETE</span>
        </div>
      </section>
      <h4 class="add-new">ADD NEW</h4>
      <hr />
      <form action="/upload-media" method="POST" class="dropzone" :id="dropZoneId" :class="{ 'image-error': validationErrors['images'] }">
          <input type="hidden" name="title">
          <input type="hidden" name="description">
          <input type="hidden" name="is_public">
      </form>
      <span
        :class="{ 'cp-validation-errors': validationErrors['images'] }"
        v-if="validationErrors['images']">{{ validationErrors['images'][0] }}</span>
    </div>
  </div>
</template>

<script>
const Dropzone = require('dropzone')
const Media = require('../../resources/media.js')
Dropzone.autoDiscover = false

module.exports = {
  data () {
    return {
      newMedia: [],
      newCurrentMedia: []
    }
  },
  props: {
    dropZoneId: {
      type: String,
      default () {
        return 'createMediaZone'
      }
    },
    currentMedia: {
      type: Array,
      default () {
        return []
      }
    },
    media: {
      type: Array,
      default () {
        return []
      }
    },
    validationErrors: {}
  },
  mounted () {
    this.newMedia = this.media
    this.newCurrentMedia = this.currentMedia
    this.uploadDropzone()
    this.initCurrentMedia()
  },
  methods: {
    initCurrentMedia () {
      for (var i = 0; i < this.newCurrentMedia.length; i++) {
        this.newMedia.push(this.newCurrentMedia[i])
      }
      this.$emit('new-media', this.newMedia)
    },
    deleteMedia (image, index) {
      for (var i = 0; i < this.newMedia.length; i++) {
        if (this.newMedia[i].id === image.id) {
          this.newMedia.splice(i, 1)
          this.newCurrentMedia.splice(index, 1)
        }
        this.$toast('Image has been deleted.')
        this.$emit('new-media', this.newMedia)
      }
    },
    uploadDropzone () {
      var $this = this
      var uploadZoneConfig = Media.dropzoneConfig('.mp4, .MP4, .mp3')
      uploadZoneConfig.maxFiles = 8
      uploadZoneConfig.success = function (file, response) {
        file.imageId = response.id
        $this.newMedia.push(response)
        $this.$toast('File successfully uploaded.')
        $this.$emit('new-media', $this.newMedia)
      }
      uploadZoneConfig.error = function (file, response) {
        $this.$toast('There was an error uploading your image. Try again or contact support.', { error: true })
      }
      uploadZoneConfig.removedfile = function (file, response) {
        var _ref
        if (file.previewElement) {
          if ((_ref = file.previewElement) != null) {
            _ref.parentNode.removeChild(file.previewElement)
          }
        }
        for (var i = 0; i < $this.newMedia.length; ++i) {
          if ($this.newMedia[i].id === file.imageId) {
            $this.newMedia.splice(i, 1)
          }
        }
        $this.$emit('new-media', this.newMedia)
        return $this.uploadZone._updateMaxFilesReachedClass()
      }
      this.uploadZone = new Dropzone('#' + this.dropZoneId, uploadZoneConfig)
    }
  }
}
</script>

<style lang="scss" scoped>
.current-media {
  margin-bottom: 25px;
}
.image-error {
  border-color: tomato;
}
</style>
