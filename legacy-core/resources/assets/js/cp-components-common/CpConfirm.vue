<template lang="html">
  <div class="confirm-wrapper">
    <transition name="modal">
      <section class="cp-modal-standard" v-if="show">
          <div class="cp-modal-body" @click="$event.stopPropagation();">
              <p>{{ message }}</p>
              <button class="cp-button-standard close-confirm" @click="$emit('input', false), callback(params)">{{ configOptions.buttonTextOne || 'Confirm'}}</button>
              <button class="cp-button-standard close-confirm" @click="$emit('input', false), onCancelled(params)">{{ configOptions.buttonTextTwo || 'Cancel'}}</button>
          </div>
      </section>
    </transition>
  </div>
</template>

<script>
module.exports = {
  data: function () {
    return {
    }
  },
  props: {
    message: {
      type: String,
      required: true
    },
    show: {
      type: Boolean,
      required: true
    },
    configOptions: {
      type: Object,
      required: false,
      default () {
        return {
          buttonTextOne: 'Confirm',
          buttonTextTwo: 'Cancel'
        }
      }
    },
    callback: {
      type: Function,
      required: true
    },
    onCancelled: {
      type: Function,
      required: false,
      default: function () {
      }
    },
    params: {
      type: [Object, Array],
      required: true
    }
  },
  watch: {
    'show': function () {
      if (this.show === true) {
        window.scrollTo(0, 0)
      }
    }
  }
}
</script>

<style lang="scss">
.confirm-wrapper {
  .close-confirm {
    float: right;
    margin: 4px;
  }
  p {
      text-align: center;
  }
}
</style>
