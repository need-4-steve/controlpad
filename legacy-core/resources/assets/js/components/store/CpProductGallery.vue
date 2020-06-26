<template lang="html">
  <div class="product-gallery-wrapper">
    <img
      @click.stop.prevent="showGallery = !showGallery"
      style="max-width: 75px"
      :src="defaultImage"
      :alt="alt" />
      <section class="cp-modal-standard" v-if="showGallery" @click="showGallery = false">
        <div class="cp-modal-body" @click.stop.prevent>
          <span class="close-modal"><i class="mdi mdi-close" @click="showGallery = false"></i></span>
          <cp-carousel :default-image="defaultImage" :images="images"></cp-carousel>
        </div>
      </section>
  </div>
</template>

<script>
module.exports = {
  data () {
    return {
      showGallery: false,
      slides: {},
      currentSlide: 0
    }
  },
  props: ['alt', 'defaultImage', 'images'],
  methods: {
    nextSlide () {
      if (this.currentSlide >= this.images.length - 1) {
        this.currentSlide = 0
      } else {
        this.currentSlide += 1
      }
    },
    previousSlide () {
      if (this.currentSlide === 0) {
        this.currentSlide = this.images.length - 1
      } else {
        this.currentSlide = this.currentSlide - 1
      }
    }
  },
  components: {
    CpCarousel: require('../../cp-components-common/images/CpCarousel.vue')
  }
}
</script>

<style lang="scss">
.product-gallery-wrapper {
  img[src=""] {
    display: none;
  }
  .cp-modal-standard {
    overflow-y: auto;
    .cp-modal-body {
      overflow: auto;
    }
  }

  #slides {
    position: relative;
    padding: 0px;
    margin: 0px;
    list-style-type: none;
    text-align: center;
    img {
      max-width: 100%;
    }
}
// TODO: adjust to make transistions work
  .slide {
      position: relative;
      display: none;
      left: 0px;
      top: 0px;
      width: 100%;
      height: 100%;
      opacity: 0;
      z-index: 1;
      -webkit-transition: opacity 1s;
      -moz-transition: opacity 1s;
      -o-transition: opacity 1s;
      transition: opacity 1s;
  }
  .showing {
      display: block !important;
      opacity: 1;
      z-index: 2;
  }
}
</style>
