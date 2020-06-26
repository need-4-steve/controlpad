<template>
    <div class="CpCareerTitle">
        <h5>{{title}}</h5>
          <h3>{{ leadershipBonus | currency }}</h3>
    </div>
</template>
 <script id="leadership-bonus">
 module.exports = {
  data () {
    return {
    }
  },
  props: ['leadershipBonus', 'title'],
  mounted () {
  },
  methods: {
  }
}
</script>
 <style lang="scss">
 </style>
