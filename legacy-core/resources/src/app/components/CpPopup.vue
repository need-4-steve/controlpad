<script id="CpPopup">
module.exports = {
  props: {
    visible: {
      type: Boolean,
      default: false
    },
    position: {
      type: String,
      default: 'bottom'
    },
    alignment: {
      type: String,
      default: 'left'
    }
  },
  data () {
    return { }
  },
  mounted () {
    this.positionElement()
    document.addEventListener('click', this.onDocClick)
  },
  methods: {
    onDocClick (e) {
      if (!this.visible) return
      let popupEl = this.$el
      let clickedEl = e.target
      if (popupEl !== clickedEl && !popupEl.contains(clickedEl)) {
        this.cancelPopup()
      }
    },
    cancelPopup () {
      this.$emit('cancel')
    },
    positionElement () {
      const element = this.$el
      const parent = element.parentNode
      switch (this.alignment) {
        case 'top':
          element.style.top = '0px'
          break
        case 'right':
          element.style.right = '0px'
          break
        case 'bottom':
          element.style.bottom = '0px'
          break
        case 'left':
        default:
          element.style.left = '0px'
      }
      switch (this.position) {
        case 'top':
          element.style.top = `-${element.offsetHeight}px`
          break
        case 'left':
          element.style.left = `-${element.offsetWidth}px`
          break
        case 'right':
          element.style.left = `${parent.offsetWidth}px`
          break
        case 'bottom':
        default:
          element.style.top = `${parent.offsetHeight}px`
      }
      this.ensureVisible()
    },
    ensureVisible () {
      setTimeout(() => {
        const element = this.$el
        const rect = element.getBoundingClientRect()
        const rightLimit = (window.innerWidth || document.documentElement.clientWidth)
        const bottomLimit = (window.innerHeight || document.documentElement.clientHeight)
        if (rect.right > rightLimit) {
          element.style.left = `${element.offsetLeft - (rect.right - rightLimit)}px`
        }
        if (rect.bottom > bottomLimit) {
          element.style.top = `${element.offsetTop - (rect.bottom - bottomLimit)}px`
        }
        if (rect.left < 0) {
          element.style.left = `${element.offsetLeft - rect.left}px`
        }
        if (rect.top < 0) {
          element.style.top = `${element.offsetTop - rect.top}px`
        }
      })
    }
  }
}
</script>
<template>
  <div class="scoped-cp-popup" v-show="visible">
    <slot></slot>
  </div>
</template>
<style lang="scss">
  .scoped-cp-popup{
    z-index: 99999;
    position: absolute;
    padding: 10px 12px;
    box-shadow: 0 0 10px #aaa;
    background: #fff;
  }
</style>