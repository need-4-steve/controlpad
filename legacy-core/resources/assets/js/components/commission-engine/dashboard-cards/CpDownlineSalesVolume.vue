<template>
    <div class="CpCareerTitle">
        <h5>{{title}}</h5>
          <h3 v-if="mydsvperlevel.length == 0">None</h3>
          <h3 v-for="line in mydsvperlevel">Level {{line.level}}: {{ line.amount | currency }}</h3>
    </div>
</template>
 <script id="downline-sales-volume">
 module.exports = {
  data () {
    return {
    }
  },
  props: ['mydsvperlevel', 'title'],
  mounted () {
  },
  methods: {
  }
}
</script>
 <style lang="scss">
 </style>
