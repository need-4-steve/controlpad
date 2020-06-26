<template lang="html">
  <div class="cp-carousel-wrapper">
    <div class="carousel-image-contianer">
      <div class="col-arrow" @click="previousSlide()">
        <div class="mdi mdi-chevron-left mdi-36px"></div>
      </div>
      <div class="col-images">
          <ul id="slides">
            <li
            v-for="(image, index) in images"
            @click="nextSlide()"
            class="slide"
            :class="{ 'showing': currentSlide === index }">
            <img :src="image.url | imageSize('url_lg')">
            <div class="image-information-modal">
              <div class="col-modal">
                <label for="">{{variantLabel}}:</label>
                <span class="">{{ image.name }}</span>
              </div>
              <div class="col-modal" v-if="image.available">
                <label for="">Starting at:</label>
                <span>
                  <span class="">${{ image.price }}</span>
                </span>
              </div>
              <div class="col-modal" v-if="image.available">
                <label for="">Available in: </label>
                <div class="">
                  <span v-for="(option, index) in image.available">
                    <span class="option-box" v-if="option.quantity_available > 0"> {{ option.option }}</span>
                  </span>
                </div>
              </div>
            </div>
            <div class="variant-description" v-if="image.description">
              <label for="variant">Description</label>
              <p>{{image.description}}</p>
            </div>
          </li>
        </ul>
      </div>
      <div class="col-arrow" @click="nextSlide()">
        <div class="mdi mdi-chevron-right mdi-36px"></div>
      </div>
    </div>
    <div v-if="images.length > 0">
      <div class="align-center">
        {{ currentSlide + 1 }} / {{ images.length }}
      </div>
    </div>
  </div>
</template>

<script>
module.exports = {
  data () {
    return {
      slides: {},
      currentSlide: 0
    }
  },
  props: {
    images: {},
    variantLabel: {
      type: String,
      default () {
        return ''
      }
    }
  },
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
  }
}
</script>

<style lang="scss">

.cp-carousel-wrapper {
  img[src=""] {
    display: none;
  }

  .variant-description {
    font-size: 20px;
  }

  .carousel-image-contianer {
    display: flex;
    .col-images {
      flex: 1;
    }
    .col-arrow {
      width: 50px;
      font-size: 50px;
      display: flex;
      align-items: center;
      cursor: pointer;
      justify-content: center;
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
  .cp-carousel-buttons {
    width: 100%;
    .right {
      float: right;
    }
    .left {
      float: left;
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

  .option-box {
    border: 1px solid black;
    padding: 1px;
    padding-right: 5px;
    padding-left: 5px;
    margin: 2px;
  }
  .image-information-modal {
    label {
      margin: 0px 5px 0px 5px;
    }
    margin: 5px;
    font-size: 20px;
    display: flex;
    justify-content: center;
    .col-modal {
      display: flex;
      margin: 5px;
      justify-content: space-between;
    }
  }
  @media (max-width: 1024px) {
    .image-information-modal {
      label {
        margin: 0px;
      }
      font-size: 12px;
      display: block;
      justify-content: space-between;
      .col-modal {
        display: flex;
        justify-content: space-between;
      }
    }
    .variant-description {
      font-size: 12px;
    }
  }
  @media (max-width: 450px) {
    .image-information-modal {
      font-size: 10px;
      .col-modal {
        .option-box {
          padding-left: 2px;
          padding-right: 2px;
          font-size: 8px;
        }
      }
    }
    .variant-description {
      font-size: 12px;
    }
  }
}
</style>
