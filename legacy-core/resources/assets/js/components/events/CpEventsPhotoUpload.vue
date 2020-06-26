<template>
 <div id="events-photo-upload-wrapper">
   <section class="current-media product-grid edit" v-if="media">
     <h4>CURRENT {{ $getGlobal('events_title').value.single.toUpperCase() }} IMAGE</h4>
     <hr />
     <div class="item">
       <img class="item_image" :src="media">
       <span href="#" class="tile-overlay pointer" @click="media = null, $emit('new-media', null)">DELETE</span>
     </div>
   </section>
     <form v-show="$getGlobal('allow_reps_events_img').show && Auth.hasAnyRole('Rep') || Auth.hasAnyRole('Superadmin','Admin')" action="/upload-media" method="POST" class="dropzone" id="dropzoneEventsImport" :class="{ 'image-error': validationErrors['images'] }">
         <input type="hidden" name="title">
         <input type="hidden" name="description">
         <input type="hidden" name="is_public">
     </form>
   <span
     :class="{ 'cp-validation-errors': validationErrors['images'] }"
     v-if="validationErrors['images']">{{ validationErrors['images'][0] }}</span>
 </div>
</template>
<script>
const Dropzone = require('dropzone')
const Media = require('../../resources/media.js')
const Auth = require('auth')

module.exports = {
  data: () => ({
    media: '',
    Auth: Auth
  }),
  props: {
    validationErrors: {},
    currentMedia: {
      default () {
        return ''
      }
    }
  },
  mounted () {
    this.uploadDropzone()
    this.initCurrentMedia()
  },
  methods: {
    initCurrentMedia () {
      this.media = this.currentMedia
    },
    uploadDropzone () {
      var $this = this
      var uploadZoneConfig = Media.dropzoneConfig('.mp4, .MP4, .mp3')
      uploadZoneConfig.success = function (file, response) {
        $this.media = response.url
        $this.$toast('File successfully uploaded.')
        $this.$emit('new-media', $this.media)
      }
      uploadZoneConfig.error = function (file, response) {
        $this.$toast(response, { error: true })
      }
      uploadZoneConfig.removedfile = function (file, response) {
        var _ref
        if (file.previewElement) {
          $this.media = ''
          if ((_ref = file.previewElement) != null) {
            _ref.parentNode.removeChild(file.previewElement)
          }
        }
        $this.media = null
        $this.$emit('new-media', null)
        return $this.uploadZone._updateMaxFilesReachedClass()
      }
      this.uploadZone = new Dropzone('#dropzoneEventsImport', uploadZoneConfig)
    }
  }
}
 </script>
 <style lang="scss" scoped>
 </style>
