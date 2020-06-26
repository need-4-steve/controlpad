<template lang="html">
    <div class="events-wrapper">
        <div class="cp-accordion">
            <div class="cp-accordion-head black-header">
                <h5>Event Settings</h5>
            </div>
            <div class="cp-accordion-body">
                <div class="cp-accordion-body-wrapper">
                    <div class="cp-left-col">
                      <h4>Event Options</h4>
                      <hr />
                      <div class="line-wrapper">
                          <label>Allow images by reps</label>
                          <input class="toggle-switch" type="checkbox" v-model="events_settings.allow_reps_events_img.show">
                      </div>
                      <div class="line-wrapper">
                          <label>Allow reps to have events on or off</label>
                          <input class="toggle-switch" type="checkbox" v-model="events_settings.allow_reps_events.show">
                      </div>
                      <div class="line-wrapper">
                          <label>Make events page the landing page for replicated sites</label>
                          <input class="toggle-switch" type="checkbox" v-model="events_settings.events_as_replicated_site.show">
                      </div>
                      <div class="line-wrapper">
                          <label>Events Title Plural</label>
                          <input class="input-class" type="text" v-model="events_settings.events_title.value.plural">
                      </div>
                      <div class="line-wrapper">
                        <label>Events Title Singular</label>
                        <input class="input-class" type="text" v-model="events_settings.events_title.value.single">
                      </div>
                    </div>
                    <div class="cp-right-col">
                        <div>
                            <h5>Set Default Events Image</h5>
                            <div id="dropzoneImport">
                                <form action="/api/v1/media/process" method="POST" class="dropzone" id="dropzone">
                                    <input type="hidden" name="userId" :value="events_settings.id" :selected="events_settings">
                                    <input type="hidden" name="replace" v-model="replace">
                                    <input type="hidden" id="token" name="_token">
                                    <input type="hidden" name="image_type" value="events_default_img">
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="save-settings-button">
                        <input class="cp-button-standard" type="button" @click="saveEventsSettings()" value="Save">
                    </div>
                </div>
            </div>
            <br>
        </div>
    </div>
</template>

<script>
const Settings = require('../../../resources/settings.js')
const Dropzone = require('dropzone')
const Media = require('../../../resources/media.js')

module.exports = {
  data: function () {
    return {
      closed: true,
      showConfirm: false,
      events_settings: {
        allow_reps_events_img: {},
        events_as_replicated_site: {},
        allow_reps_events: {},
        events_title: {
          value: {
            plural: null,
            single: null
          }
        },
        events_default_img: ''
      },
      replace: {}
    }
  },
  computed: {},
  mounted () {
    this.getEventSettings()
    Dropzone.autoDiscover = false
    this.buildDropzone()
    this.hostName = window.location.host
  },
  methods: {
    getEventSettings: function () {
      Settings.getEventSettings()
              .then((response) => {
                this.events_settings = response
                this.getDefaultImage()
              })
    },
    saveEventsSettings: function () {
      Settings
        .update(this.events_settings)
        .then((response) => {
          if (response.error) {
            this.$toast(response.message)
          } else {
            this.$updateGlobal(this.events_settings)
            this.$toast('Store settings saved successfully.')
          }
        })
    },
    buildDropzone: function () {
      var thisevents = this
      var dropzoneConfig = Media.dropzoneConfig()
      dropzoneConfig.success = function (file, response) {
        thisevents.events_settings.events_default_img.value = response.url
      }
      dropzoneConfig.error = function (file, response) {
        thisevents.$toast('There was an error uploading your image. Try again or contact support.', { error: true })
      }
      dropzoneConfig.removedfile = function (file, response) {
        if (file.previewElement) {
          var _ref
          if ((_ref = file.previewElement) != null) {
            _ref.parentNode.removeChild(file.previewElement)
            thisevents.events_settings.events_default_img.value = ''
          }
        }
        return
      }
      this.dropzone = new Dropzone('#dropzone', dropzoneConfig)
    },
    getDefaultImage: function () {
      if (this.events_settings.events_default_img.value !== '') {
        var imageFile = {
          url: this.events_settings.events_default_img.value
        }
        Dropzone.forElement('#dropzone').emit('addedfile', imageFile)
        Dropzone.forElement('#dropzone').emit('thumbnail', imageFile, imageFile.url)
        Dropzone.forElement('#dropzone').emit('complete', imageFile)
      }
    }
  },
  components: {
  }
}

</script>

<style lang="sass">
    @import "resources/assets/sass/var.scss";
    .events-wrapper {
        .confirm {
          float: right;
        }
        .cp-accordion-head {
            &.black-header {
                position: relative;
                padding: 10px;
                height: auto;
                h5 {
                    margin: 0;
                    display: inline-block;
                    margin-right: 0px;
                    font-weight: 300;
                    font-size: 1.2em;
                }
                background-color: $cp-main;
                color: $cp-main-inverse;
            }
        }
        .cp-left-col {
            width: 48%;
        }
        .cp-right-col {
            width: 48%;
        }
        .line-wrapper {
            display: flex;
            -webkit-display: flex;
            justify-content: space-between;
            -webkit-justify-content: space-between;
            label {
                font-size: 14px;
                font-weight: 300;
                margin-bottom: 0;
            }
            .input-class {
                height: 100%;
                width: 50%;
                height: 30px;
                text-indent: 10px;
                margin: 5px 0;

            }
            &.toggle-switch {
                width: 40px;
            }
        }
    }
</style>
