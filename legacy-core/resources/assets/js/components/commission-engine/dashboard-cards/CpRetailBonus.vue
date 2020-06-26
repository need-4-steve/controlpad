<template>
    <div class="CpCareerTitle">
        <h5>{{title}}</h5>
          <h3>{{ retailBonus.amount | currency }}</h3>
    </div>
</template>
 <script id="retail-bonus">
 module.exports = {
  data () {
    return {
    }
  },
  props: ['retailBonus', 'title'],
  mounted () {
  },
  methods: {
  }
}
</script>
 <style lang="scss">
 </style>
