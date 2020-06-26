<script id="CpDialog">
module.exports = {
  props: { open: Boolean },
  data () {
    return { parent }
  },
  mounted () {
    this.parent = this.$el.parentNode
    this.$refs.overlay.addEventListener('click', (e) => {
      if (e.target === this.$refs.overlay) {
        this.triggerClose()
      }
    })
  },
  methods: {
    triggerClose () {
      this.$emit('close')
    },
    docEscape (e) {
      if (e.which === 27) {
        this.triggerClose()
      }
    },
    fadeIn () {
      $(this.$el).css('display', 'flex').hide().fadeIn(100)
    },
    fadeOut () {
      $(this.$el).fadeOut(100)
    },
    hasHeader () {
      return !!this.$slots.header
    },
    hasFooter () {
      return !!this.$slots.footer
    }
  },
  watch: {
    open () {
      if (this.open) {
        document.body.appendChild(this.$el)
        this.fadeIn()
        document.addEventListener('keyup', this.docEscape)
      } else {
        this.fadeOut()
        this.parent.appendChild(this.$el)
        document.removeEventListener('keyup', this.docEscape)
      }
    }
  }
}
</script>

<template>
  <v-cp-dialog ref="overlay">
    <v-cp-dialog-wrapper>
      <cp-dialog-header v-if="hasHeader()">
        <slot name="header"></slot>
        <span class="flex"></span>
        <span class="mdi-close icon-button" @click="triggerClose()"></span>
      </cp-dialog-header>
      <cp-dialog-content>
        <slot name="content"></slot>
      </cp-dialog-content>
      <cp-dialog-footer v-if="hasFooter()">
        <slot name="footer"></slot>
      </cp-dialog-footer>
    </v-cp-dialog-wrapper>
  </v-cp-dialog>
</template>

<style lang="scss">
  v-cp-dialog{
    position: fixed;
    top: 0; right: 0; bottom: 0; left: 0;
    display: none;
    align-items: center;
    justify-content: center;
    z-index: 9999;
    -webkit-transform: translate3d(0,0,0);
    background: rgba(0, 0, 0, .8);
    & > v-cp-dialog-wrapper {
      display: flex;
      flex-direction: column;
      background: $cp-main-inverse;
      width: 80%;
      margin: 10px auto;
      max-height: 95%;
      max-width: 1410px;
      @media (max-width: 768px) {
        width: 100%;
        height: 100%;
        max-height: 100%;
        max-width: 100%;
        margin: 0;
      }

      cp-dialog-header{
        flex-shrink: 0;
        display: flex;
        flex-direction: row;
        align-items: center;
        justify-content: flex-end;
        background-color: $cp-main;
        color: $cp-main-inverse;
        padding: 0px 25px;

        & .flex{
          flex: 1;
        }
        & :last-child{
          cursor: pointer;
          padding: 5px;
          margin: 18px 0;
          &::before{
            font-size: 32px;
          }
        }
      }
      cp-dialog-content{
        flex: 1;
        overflow: auto;
        padding: 15px 25px;
      }
      cp-dialog-footer{
        flex-shrink: 0;
        display: flex;
        padding: 15px 25px;
        background: $cp-grey;
        justify-content: flex-end;
        button,a{
          margin-left: 10px;
        }
      }
    }
  }
</style>
