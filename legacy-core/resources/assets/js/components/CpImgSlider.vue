<template>
    <div class="product-slider">
        <div class="main-img">
            <ul class="main-slider">
                <li v-for="img in media">
                    <img v-bind:src="img.url_lg">
                </li>
            </ul>
        </div>
        <div class="img-carousel">
            <ul class="slider-nav">
                <li v-for="img in media">
                    <img class="" v-bind:src="img.url_xs">
                </li>
            </ul>
        </div>
    </div>
</template>
<script>
module.exports = {
    data: function () {
        return {
            test: true
        }
    },
    props: {
      media: {
          type: Array,
          required: true
      }
    },
    replace: false,
    ready: function(){
      this.initSlick();
    },
    computed: {},
    methods: {
      initSlick: function(){
        console.log("init called")
        this.$nextTick(function(){
            $('.main-slider').slick({
              slidesToShow: 1,
              slidesToScroll: 1,
              arrows: false,
              fade: true,
              asNavFor: 'slider-nav'
            });
            $('.slider-nav').slick({
              slidesToShow: 4,
              slidesToScroll: 1,
              asNavFor: '.main-slider',
              focusOnSelect: true

            });
        })
      }
    }
}
</script>
<style lang="sass">
  .product-slider {
    ul {
      padding-left: 0;
      list-style-type: none;
    }
    li {
      height: 100%;
      display: inline-block;
    }
    .main-slider {
      height: 350px;
      width: 350px;
      margin: 0 auto;
    }
    .slider-nav {
      height: 100px;
      width: 350px;
      margin: 0 auto;
    }
  }
</style>
