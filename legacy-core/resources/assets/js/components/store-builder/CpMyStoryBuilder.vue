<template lang="html">
    <div class="about-me-wrapper">
        <div class="my-story">
            <span>MY STORY</span>
        </div>
        <div class="images-wrapper">
            <div class="cp-left-col main-img">
                <img class="main-img pointer" :src="settings.story_profile_image" alt="profile" @click="changeImage('story_profile_image', { viewport: { width: 625, height: 938 }, boundary: { width: 750, height: 1125 } })"/>
                <div class="main-mobile-view">
                    <p><input class="story-input-header" type="text" v-model="settings.story_title" @keyup="saveSetting('story_title', settings.story_title)"></p>
                    <p><textarea name="name" rows="4" cols="110" v-model="settings.story_intro" @keyup="saveSetting('story_intro', settings.story_intro)">{{ settings.story_intro }}</textarea></p>
                </div>
            </div>
            <div class="cp-left-col grid">
                <div class="grid-img-wrapper">
                    <div class="mobile-grid">
                        <div class="grid-item top">
                             <img class="img-grid pointer" :src="settings.story_grid_image_1" :alt="settings.story_heading_1" @click="changeImage('story_grid_image_1', gridCropSize)"/>
                            <h5><input class="story-input-image-text" type="text" v-model="settings.story_heading_1" @keyup="saveSetting('story_heading_1', settings.story_heading_1)" maxlength="15"></h5>
                         </div>
                        <div class="mobile-view">
                            <p><input class="story-input-header" type="text" v-model="settings.story_heading_1" @keyup="saveSetting('story_heading_1', settings.story_heading_1)"></p>
                            <p><textarea name="name" rows="4" cols="110" v-model="settings.story_text_1" @keyup="saveSetting('story_text_1', settings.story_text_1)">{{ settings.story_text_1 }}</textarea></p>
                        </div>
                     </div>
                     <div class="mobile-grid">
                         <div class="grid-item top">
                             <img class="img-grid pointer" :src="settings.story_grid_image_2" :alt="settings.story_heading_2" @click="changeImage('story_grid_image_2', gridCropSize)"/>
                            <h5><input class="story-input-image-text" type="text" v-model="settings.story_heading_2" @keyup="saveSetting('story_heading_2', settings.story_heading_2)" maxlength="15"></h5>
                         </div>
                            <div class="mobile-view">
                                <p><input class="story-input-header" type="text" v-model="settings.story_heading_2" @keyup="saveSetting('story_heading_2', settings.story_heading_2)"></p>
                                <p><textarea name="name" rows="4" cols="110" v-model="settings.story_text_2" @keyup="saveSetting('story_text_2', settings.story_text_2)" maxlength="15">{{ settings.story_text_2 }}</textarea></p>
                            </div>
                    </div>
                    <div class="mobile-grid">
                         <div class="grid-item">
                             <img class="img-grid pointer" :src="settings.story_grid_image_3" :alt="settings.story_heading_3" @click="changeImage('story_grid_image_3', gridCropSize)"/>
                            <h5><input class="story-input-image-text" type="text" v-model="settings.story_heading_3" @keyup="saveSetting('story_heading_3', settings.story_heading_3)" maxlength="15"></h5>
                        </div>
                        <div class="mobile-view">
                            <p><input class="story-input-header" type="text" v-model="settings.story_heading_3" @keyup="saveSetting('story_heading_3', settings.story_heading_3)" maxlength="15"></p>
                            <p><textarea name="name" rows="4" cols="110" v-model="settings.story_text_3" @keyup="saveSetting('story_text_3', settings.story_text_3)">{{ settings.story_text_3 }}</textarea></p>
                        </div>
                    </div>
                    <div class="mobile-grid">
                         <div class="grid-item">
                             <img class="img-grid pointer" :src="settings.story_grid_image_4" :alt="settings.story_heading_4" @click="changeImage('story_grid_image_4', gridCropSize)"/>
                            <h5><input class="story-input-image-text" type="text" v-model="settings.story_heading_4" @keyup="saveSetting('story_heading_4', settings.story_heading_4)" maxlength="15"></h5>
                        </div>
                        <div class="mobile-view">
                            <p><input class="story-input-header" type="text" v-model="settings.story_heading_4" @keyup="saveSetting('story_heading_4', settings.story_heading_4)"></p>
                            <p><textarea name="name" rows="4" cols="110" v-model="settings.story_text_4" @keyup="saveSetting('story_text_4', settings.story_text_4)" >{{ settings.story_text_4 }}</textarea></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="life-wrapper">
            <div class="desktop-view">
                <span><input class="story-input-header" type="text" v-model="settings.story_title" @keyup="saveSetting('story_title', settings.story_title)" maxlength="15"></span>
                <p><textarea name="name" rows="4" cols="80" v-model="settings.story_intro" @keyup="saveSetting('story_intro', settings.story_intro)">{{ settings.story_intro }}</textarea></p>
            </div>
            <div class="desktop-view">
                <span><input class="story-input-header" type="text" v-model="settings.story_heading_1" @keyup="saveSetting('story_heading_1', settings.story_heading_1)" maxlength="15"></span>
                <p><textarea name="name" rows="4" cols="80" v-model="settings.story_text_1" @keyup="saveSetting('story_text_1', settings.story_text_1)">{{ settings.story_text_1 }}</textarea></p>
            </div>
            <div class="desktop-view">
                <span><input class="story-input-header" type="text" v-model="settings.story_heading_2" @keyup="saveSetting('story_heading_2', settings.story_heading_2)" maxlength="15"></span>
                <p><textarea name="name" rows="4" cols="80" v-model="settings.story_text_2" @keyup="saveSetting('story_text_2', settings.story_text_2)">{{ settings.story_text_2 }}</textarea></p>
            </div>
            <div class="desktop-view">
                <span><input class="story-input-header" type="text" v-model="settings.story_heading_3" @keyup="saveSetting('story_heading_3', settings.story_heading_3)" maxlength="15"></span>
                <p><textarea name="name" rows="4" cols="80" v-model="settings.story_text_3" @keyup="saveSetting('story_text_3', settings.story_text_3)">{{ settings.story_text_3 }}</textarea></p>
            </div>
            <div class="desktop-view">
                <span><input class="story-input-header" type="text" v-model="settings.story_heading_4" @keyup="saveSetting('story_heading_4', settings.story_heading_4)" maxlength="15"></span>
                <p><textarea name="name" rows="4" cols="80" v-model="settings.story_text_4" @keyup="saveSetting('story_text_4', settings.story_text_4)" >{{ settings.story_text_4 }}</textarea></p>
            </div>
        </div>
        <transition name="modal">
          <section class="cp-modal-standard" v-show="showImageModal">
                  <div class="cp-modal-body upload">
                      <cp-media-upload-modal
                      :show-modal="showImageModal"
                      @show-modal="val => showImageModal = val"
                      :setting-key="settingKey"
                      :handle-selection="handleSelection"
                      :crop-size="selectedCropSize"></cp-media-upload-modal>
                  </div>
          </section>
      </transition>
    </div>
</template>

<script>
const Settings = require('../../resources/settings.js')
const _ = require('lodash')

module.exports = {
  data: function () {
    return {
      showImageModal: false,
      settingKey: null,
      updatedValue: '',
      loading: false,
      selectedCropSize: {
        viewport: { width: 625, height: 938 },
        boundary: { width: 750, height: 1125 }
      },
      gridCropSize: {
        boundary: { width: 857, height: 600 },
        viewport: { width: 750, height: 500 }
      }
    }
  },
  props: {
    settings: {
      type: Object,
      required: true
    }
  },
  methods: {
    handleSelection: function (key, media) {
      var url = media.url_md
      // determine image size to store with what setting
      if (key === 'story_profile_image' && media.url_lg !== undefined) {
        url = media.url_lg
      }
      // save the setting
      this.saveSetting(key, url)
    },
    saveSetting: _.debounce(function (key, value) {
      var request = { key: key, value: value }
      Settings.saveStoreSetting(request)
        .then((response) => {
          if (response.error) {
            this.$toast(response.message, {error: true})
          }
          this.settings[request.key] = response.value
        })
    }, 850),
    changeImage: function (key, cropSize) {
      window.scroll(0, 0)
      this.showImageModal = true
      this.settingKey = key
      this.selectedCropSize = cropSize
      this.$emit('drop')
    }
  },
  components: {
    'CpMediaUploadModal': require('../media/CpMediaUploadModal.vue')
  }
}
</script>

<style lang="scss" scoped>
@import "resources/assets/sass/var.scss";
@import "resources/assets/sass/store-styles.scss";

.story-input-image-text {
    background: transparent;
    border: 1.5px dashed $cp-lightGrey;
    text-align: center;
    width: 100%;
    text-shadow: 1px 1px 3px black;
}
.story-input-header {
    @extend .story-input-image-text;
    text-shadow: none;
    width: inherit;
    text-align: left;
}
.cp-modal-body {
    &.upload {
        padding: 0;
        width: 85%;
    }
}

</style>
