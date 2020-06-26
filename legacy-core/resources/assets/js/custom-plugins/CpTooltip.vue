<template>
  <div class="tooltip-wrapper">
    <span class="tooltip-content">{{ options.content }}</span>
    <span class="mdi-help-circle"></span>
  </div>
</template>

<script>
module.exports = {
  props: {
    options: {
      type: Object,
      required: true
    }
  }
}
</script>

<style lang="scss">

.tooltip-wrapper{
  display: inline-block;
  width: auto !important;
  margin: 0 3px;
  &:hover .tooltip-content{
    display: block;
  }
  .tooltip-content{
    display: none;
    position: absolute;
    z-index: 1000;
    max-width: 300px;
    margin-top: 12px;
    border: solid 1px $cp-lightGrey;
    border-radius: 2px;
    padding: 5px 10px;
    background: #fff;
    color: $cp-main;
  }
}
</style>
