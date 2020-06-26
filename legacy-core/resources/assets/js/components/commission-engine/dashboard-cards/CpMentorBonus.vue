<template>
    <div class="CpCareerTitle">
        <h5>{{title}}</h5>
          <h3 v-if="mentorBonus.length == 0">None</h3>
          <h3 v-for="line in mentorBonus">Level {{line.generation}}: {{ line.amount | currency }}</h3>
    </div>
</template>
 <script id="mentor-bonus">
 module.exports = {
  data () {
    return {
    }
  },
  props: ['mentorBonus', 'title'],
  mounted () {
  },
  methods: {
  }
}
</script>
 <style lang="scss">
 </style>
