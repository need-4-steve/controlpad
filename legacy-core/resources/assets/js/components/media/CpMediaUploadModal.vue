<template lang="html">
    <div class="upload-modal-wrapper">
        <div v-show="!saving">
            <div class="sidebar">
                <ul class="upload-modal-menu">
                    <li @click="type = null, getMedia()" :class="{ active: activeFilter.all }">All Media Files</li>
                    <li @click="type = 'user', getMedia()" :class="{ active: activeFilter.user }">Your Media Files</li>
                    <li @click="type = 'shared', getMedia()" :class="{ active: activeFilter.shared }">Corporate Media Files</li>
                </ul>
            </div>
            <section class="upload-main-content">
                <div class="title-wrapper">
                    <span class="title-text">Select or Upload an Image</span>
                    <i class="mdi mdi-close pointer x" @click="closeModal()"></i>
                </div>
                <section class="selection-zone" v-show="!showCropper">
                    <form action="/api/v1/media/process" method="POST" class="dropzone" id="createMediaZone">
                    </form>
                    <div class="upload-wrapper" v-show="uploadButton">
                        <button class="cp-button-standard upload" type="button" @click="generateCropper()">Upload</button>
                    </div>
                    <div class="grid-wrapper">
                        <div class="cp-image-grid-standard upload-image-grid" v-if="!loading">
                            <div class="image-list cp-image-grid-box" v-for="media in media">
                              <img class="cp-grid-image" :src="media.url_xs" alt="" @click="selectImage(media)" />
                            </div>
                            <span class="image-list cp-image-grid-box" v-if="media <= 0">No media was found.</span>
                        </div>
                    </div>
                    <div class="align-center">
                        <img class="loading" :src="$getGlobal('loading_icon').value" v-if="loading">
                        <cp-pagination :pagination="pagination" :callback="getMedia" :offset="2"></cp-pagination>
                    </div>

                </section>
                <section class="cropper-wrapper" v-show="showCropper"> <!-- needs be show not if -->
                    <div id="cropper-container" width="100px">
                        <img id="crop-image" src="" />
                    </div>
                    <div class="crop-btn-wrapper">
                        <button class="cp-button-standard crop" @click="processMedia()" v-show="!saving">Crop</button>
                    </div>
                </section>
            </section>
        </div>
        <section class="align-center saving" v-show="saving">
            <p>Please Wait</p>
            <img class="loading" :src="$getGlobal('loading_icon').value" v-if="saving">
            <br />
            <br />
        </section>
    </div>
</template>

<script>
/* global FormData */

const Media = require('../../resources/media.js')
const Croppie = require('../media/croppie.js')
const Dropzone = require('dropzone')
Dropzone.autoDiscover = false

module.exports = {
  data: function () {
    return {
      loading: false,
      pagination: {},
      showCropper: false,
      uploadButton: false,
      uploadZone: null,
      media: {},
      type: null,
      activeFilter: {
        all: true,
        user: false,
        shared: false
      },
      newFile: {
        fileSet: false,
        file: null
      },
      selectedMedia: '',
      mediaRequest: {
        type: null,
        searchTerm: '',
        limit: 10
      },
      successful: false,
      uploading: false,
      croppieImage: null,
      saving: false
    }
  },
  props: {
    showModal: {
      type: Boolean
    },
    cropSize: {
      type: Object,
      default: function () {
        return {
          boundary: { width: 625, height: 938 },
          viewport: { width: 750, height: 1125 }
        }
      }
    },
    settingKey: {
      type: String
    },
    handleSelection: {
      type: Function
    }
  },
  mounted: function () {
    this.getMedia(null)
    this.uploadDropzone()
  },
  methods: {
    closeModal: function () {
      this.showCropper = false
      this.$emit('show-modal', false)
      this.newFile.fileSet = false
      if (this.croppieImage) {
        this.croppieImage.destroy()
        this.croppieImage = null // must do this to completely destroy croppie object
      }
      if (this.uploadZone) {
        // remove any images if selected
        this.uploadZone.removeAllFiles()
      }
    },
    generateCropper: function () {
      var urlCreator = window.URL || window.webkitURL
      var imageUrl = this.selectedMedia
      var element = document.getElementById('crop-image')
      // validate file
      if (this.newFile.fileSet) {
        imageUrl = urlCreator.createObjectURL(this.newFile.file)
      }
      if (!imageUrl) {
        this.closeModal()
        return this.$toast('There was an error selecting that image', {error: true})
      }
      if (this.cropSize.boundary && this.cropSize.viewport) {
        this.showCropper = true
        this.croppieImage = new Croppie(element, {
          viewport: this.cropSize.viewport,
          boundary: this.cropSize.boundary
        })
        this.croppieImage.bind({
          url: imageUrl,
          orientation: 1
        })
      }
    },
    // sets active css class to active menu item
    setActiveClass: function (type, classObject) {
      for (var key in classObject) {
        if (type === key || (key === 'all' && type === null)) {
          classObject[key] = true
        } else {
          classObject[key] = false
        }
      }
    },
    getMedia: function () {
      this.loading = true
      this.mediaRequest.page = this.pagination.current_page
      this.mediaRequest.type = this.type
      this.setActiveClass(this.type, this.activeFilter) // set active class css
      Media.indexWithFilters(this.mediaRequest)
        .then((response) => {
          this.loading = false
          if (response.error) {
            this.$toast(response.message, {error: true})
            return
          }
          this.media = response.data
          response.per_page = parseInt(response.per_page)
          this.pagination = response
        })
    },
    processMedia: function () {
      this.uploadZone.removeAllFiles() // remove files if any
      window.scrollTo(0, 0) // direct to top of page to show loading gif
      var vm = this
      // crop image
      this.croppieImage.result({type: 'blob'})
        .then(function (blob) {
          // prepare request to be proccessed by the server
          var fileName = Math.random().toString(36).substr(2, 12) + '.png' // generate unique file name
          var processRequest = new FormData()
          processRequest.append('file', blob, fileName)
          vm.saving = true
          // process media and reset things as necessary
          Media.process(processRequest, { headers: { 'Content-Type': '' } })
            .then((response) => {
              vm.saving = false
              vm.showCropper = false
              if (response.error) {
                vm.$toast(response.message, {error: true})
                return
              }
              vm.$emit('show-modal', false)
              vm.showCropper = false
              if (vm.newFile.fileSet) {
                vm.newFile.fileSet = false
              }
              vm.handleSelection(vm.settingKey, response)
            })
        })
      this.croppieImage.destroy()
    },
    uploadDropzone: function () {
      var thisComponent = this
      var uploadZoneConfig = Media.dropzoneConfig()
      uploadZoneConfig.init = function () {
        this.on('addedfile', function (file) {
          thisComponent.newFile = { fileSet: true, file: file }
          thisComponent.uploadButton = true
        })
        this.on('removedfile', function (file) {
          thisComponent.uploadButton = false
          thisComponent.newFile = { fileSet: false, file: null }
        })
      }
      uploadZoneConfig.autoProcessQueue = false
      this.uploadZone = new Dropzone('#createMediaZone', uploadZoneConfig)
      this.uploadZone.autoDiscover = false
    },
    selectImage: function (media) {
      this.selectedMedia = media.url
      this.generateCropper()
      this.newFile.fileSet = false
    }
  }
}
</script>

<style lang="scss">
    @import "resources/assets/sass/var.scss";
    .upload-modal-wrapper {
        position: relative;
        .saving {
          padding: 2%;
        }
        .upload-modal-menu {
            cursor: pointer;
        }
        .sidebar {
            position: absolute;
            top: 0;
            left: 0;
            width: 200px;
            height: 100%;
            background: $cp-main;
            color: #fff;
            ul {
                list-style-type: none;
                padding-left: 0;
            }
            li {
                padding: 10px;
                -webkit-transition: all 0.3s ease 0s;
            }
            .active {
              color: $cp-main;
              background-color: $cp-lighterGrey;
            }
            li:hover {
                background-color: lighten($cp-main, 15%);
                &.active {
                    color: $cp-main;
                    background-color: $cp-lighterGrey;
                }
            }
        }
        .image-list {
            list-style-type: none;
        }
        .upload-main-content {
            padding-left: 200px;
            .title-wrapper {
                padding: 15px;
            }
            .title-text {
                font-size: 22px;
                margin-left: 15px;
            }
            .upload-wrapper {
                margin-right: 15px;
                padding: 15px;
                display: flex;
                justify-content: flex-end;
            }
            .upload {
                float: right;
            }
            .x {
                font-size: 15px;
                float: right;
                padding: 7.5px;
                margin-right: 15px;
            }
        }
        .grid-wrapper {
            margin-top: 15px;
            background: $cp-lighterGrey;
            max-width: 800px;
            margin: 0 auto;
        }
        .cropper-wrapper {
            .crop-btn-wrapper {
                margin-right: 15px;
                padding: 15px;
            }
            .crop {
                float: right;
                margin-bottom: 15px;

            }
            #cropper-container {
                margin-top: 25px;
            }
            #image {
                max-width: 100%;
            }
        }
    }
</style>
