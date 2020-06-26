<template lang="html">
  <div class="">
    <div id="editor">
      <p v-html="value"></p>
    </div>
  </div>
</template>

<script>
const Quilljs = require('quill')

module.exports = {
  data () {
    return {
      editor: {},
      newContent: null
    }
  },
  props: {
    value: null
  },
  mounted () {
    let vm = this
    Quilljs.register(Quilljs.import('attributors/style/align'), true)
    Quilljs.register(Quilljs.import('attributors/style/font'), true)
    let options = {
      theme: 'snow',
      modules: {
        toolbar: ['bold', 'italic', 'underline', 'strike', 'link', { 'align': [] }, { 'font': [] }]
      }
    }
    this.editor = new Quilljs('#editor', options)
    this.editor.on('text-change', (delta, old, source) => {
      if (source === 'user') {
        vm.$emit('input', vm.editor.container.querySelector('.ql-editor').innerHTML)
      }
    })
  }
}
</script>

<style lang="scss">

</style>
