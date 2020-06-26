<template>
    <div class="CpCareerTitle">
        <h5>{{title}}</h5>
          <h3>{{myTitle.carrertitle}}</h3>
    </div>
</template>
 <script id="career-title">
 module.exports = {
  data () {
    return {
    }
  },
  props: ['myTitle', 'title'],
  mounted () {
  },
  methods: {
  }
}
</script>
 <style lang="scss">
 </style>
