<template lang="html">
  <div class="cp-multi-carousel-wrapper">
    <div class="image-selectors mdi mdi-chevron-left" :class="{ 'disabled-arrow': currentStartIndex === 0 }"  @click="previousSlide()"></div>
    <section class="variant-selection-section cp-grid-standard flex1">
      <section
        class="cp-cell-10 variant-image-cell"
        @click="selectVariant(variant, index)"
        :class="{ active: activeVariant[index]}"
        v-for="(variant, index) in currentSlides">
          <img
          v-if="variant.images && variant.images.length > 0"
          :src="variant.images[0].url| imageSize('url_sm')"
          :alt="variant.name">
      </section>
    </section>
      <div class="image-selectors mdi mdi-chevron-right" :class="{ 'disabled-arrow': currentSlides.length < 9 }" @click="nextSlide()"></div>
  </div>
</template>

<script>
module.exports = {
  data () {
    return {
      slides: {},
      currentSlides: [],
      currentStartIndex: 0,
      nextStartIndex: 0
    }
  },
  props: ['variants', 'selectVariant', 'activeVariant'],
  mounted () {
    this.setSelected(0)
  },
  methods: {
    setSelected (startIndex) {
      this.currentSlides = []
      this.currentStartIndex = startIndex
      let counter = 0
      for (var i = startIndex; i < this.variants.length; i++) {
        if (counter > 9) {
          this.nextStartIndex = i
          return
        }
        this.currentSlides.push(this.variants[i])
        counter++
      }
    },
    nextSlide () {
      this.setSelected(this.nextStartIndex)
    },
    previousSlide () {
      this.nextStartIndex = this.nextStartIndex - 18
      if (this.nextStartIndex < 0) {
        this.nextStartIndex = 0
      }
      this.setSelected(this.nextStartIndex)
    }
  }
}
</script>

<style lang="scss">
.cp-multi-carousel-wrapper {
  display: flex;
  .flex1 {
    flex: 1;
  }
  .image-selectors {
    display: flex;
    align-items: center;
    width: 40px;
    justify-content: center;
    &.disabled-arrow {
      color: lightgrey;
    }
    &:hover {
      color: lightgrey;
    }
  }
  .variant-selection-section {
    .active {
      border: 1px solid grey;
    }
    .variant-image-cell {
      cursor: pointer;
      &:hover {
        border: 1px solid grey;
      }
    }
  }
}
</style>
