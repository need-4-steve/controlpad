<template>
    <div class="CpCareerTitle">
        <h5>{{title}}</h5>
          <h3>New Recruit: {{ (myfaststartbonus ? myfaststartbonus.bonus : 0) | currency }}</h3>
    </div>
</template>
 <script id="fast-start-bonus">
 module.exports = {
  data () {
    return {
    }
  },
  props: ['myfaststartbonus', 'title'],
  mounted () {
  },
  methods: {
  }
}
</script>
 <style lang="scss">
 </style>
