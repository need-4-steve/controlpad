<template>
    <div class="CpCareerTitle">
        <h5>{{title}}</h5>
          <h3 v-if="levelbonus.length == 0">None</h3>
          <h3 v-for="line in levelbonus">Level {{ line.generation }}: {{ line.amount | currency }}</h3>
    </div>
</template>
 <script id="level-bonus">
 module.exports = {
  data () {
    return {
    }
  },
  props: ['levelbonus', 'title'],
  mounted () {
  },
  methods: {
  }
}
</script>
 <style lang="scss">
 </style>
