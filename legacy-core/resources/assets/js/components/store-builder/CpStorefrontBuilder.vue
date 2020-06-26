<template lang="html">

  <div class="storefront-builder-wrapper">
    <div class="toggle">
      <label>Show Store Banner</label>
      <input type="checkbox" class="toggle-switch" v-model="store.settings.show_store_banner"  @change="saveSetting('show_store_banner', store.settings.show_store_banner)">
    </div>
      <!-- STORE OWNER AND SLOGAN SECTION -->
      <section class="rep-name-title" v-if="Auth.hasAnyRole('Rep')">
          <div class="slogan">
            <h1 v-if="store.rep.role_id === 5"><input type="text" class="store-input-display-name" v-model="store.settings.display_name" @change="saveSetting('display_name', store.settings.display_name) "></h1>
             <h1 v-else>{{ $getGlobal('company_name').value }}</h1>
              <p><input type="text" class="store-input-slogan" v-model="store.settings.store_slogan" @change="saveSetting('store_slogan', store.settings.store_slogan) "></p>
          </div>
      </section>
      <!-- STORE BANNER SECTION-->
      <div id="container">
        <div class="slider_container">
          <ul>
            <li v-for="(item, index) in bannerimages" class="current_slide">
              <p>position {{item.possition}}</p>
            </li>
          </ul>
          <span class="prev_slide"><i class="fa fa-angle-left" aria-hidden="true"></i></span>
          <span class="next_slide"><i class="fa fa-angle-right" aria-hidden="true"></i></span>
        <div class="buttons">
        </div>
        <span class="play_pause"><i class="fa fa-pause"></i></span>
      </div>
    </div>
    <!-- PHOTO UPLOAD -->
    <div class="toggle">
      <label>Show Possition 1</label>
      <input type="checkbox" class="toggle-switch" v-model="store.settings.show_banner_image_1"  @change="saveSetting('show_banner_image_1', store.settings.show_banner_image_1)">
    </div>
    <cp-image-upload
      title="Image for position 1"
      tooltip="Please select your desired image and upload it below. You can drag a file directly into the area or click to allow you to find the image on your device. For best results, we recommend an image size of at least 670 x 450 pixels."
      :current-image="store.settings.banner_image_1"
      api_src="/api/v1/media/process"
      file_type=".jpg, .jpeg, .png, .gif"
      drop-zone-id="banner_image_1"
      @new-media="saveImage"></cp-image-upload>
    <div class="toggle">
      <label>Show Possition 2</label>
      <input type="checkbox" class="toggle-switch" v-model="store.settings.show_banner_image_2"  @change="saveSetting('show_banner_image_2', store.settings.show_banner_image_2)">
    </div>
    <cp-image-upload
    tooltip="Please select your desired image and upload it below. You can drag a file directly into the area or click to allow you to find the image on your device. For best results, we recommend an image size of at least 360 x 225 pixels."
    title="Image for position 2"
    :current-image="store.settings.banner_image_2"
    api_src="/api/v1/media/process"
    file_type=".jpg, .jpeg, .png, .gif"
    drop-zone-id="banner_image_2"
    @new-media="saveImage"></cp-image-upload>
    <div class="toggle">
      <label>Show Possition 3</label>
      <input type="checkbox" class="toggle-switch" v-model="store.settings.show_banner_image_3"  @change="saveSetting('show_banner_image_3', store.settings.show_banner_image_3)">
    </div>
    <cp-image-upload
    title="Image for position 3"
    tooltip="Please select your desired image and upload it below. You can drag a file directly into the area or click to allow you to find the image on your device. For best results, we recommend an image size of at least 360 x 225 pixels."
    :current-image="store.settings.banner_image_3"
    api_src="/api/v1/media/process"
    file_type=".jpg, .jpeg, .png, .gif"
    drop-zone-id="banner_image_3"
    @new-media="saveImage"></cp-image-upload>
    <!-- CATEGORIES -->
    <section class="category-carousel">
        <ul class="category-list">
            <li class="categories backoffice-categories" v-for="category in store.categories">
                <div>
                    <div class="category-image">
                        <img v-if="category.media[0]" :src="category.media[0].url_sm">
                    </div>
                    <span>{{ category.name }}</span><br/>
                    <span>
                        <input
                          type="text"
                          class="story-input-header text-align-center"
                          v-model="category.header"
                          @keyup="saveCategoryCaption(category.id, category.header)">
                      </span>
                </div>
            </li>
        </ul>
    </section>
  </div>
</template>

<script>
const Settings = require('../../resources/settings.js')
const Auth = require('auth')
const _ = require('lodash')

module.exports = {
  data: function () {
    return {
      validationErrors: {},
      Auth: Auth,
      selectedCategory: [],
      showImageModal: false,
      settingKey: null,
      selectedCropSize: {
        viewport: { width: 625, height: 938 },
        boundary: { width: 750, height: 1125 }
      },
      mainImageCropSize: {
        viewport: { width: 894, height: 600 },
        boundary: { width: 1043, height: 700 }
      },
      sideImageCropSize: {
        viewport: { width: 1121, height: 600 },
        boundary: { width: 1307, height: 700 }
      }
    }
  },
  props: {
    store: {
      type: Object,
      required: true
    },
     bannerimages : {
            type: Array,
            required: false
     }
  },
  methods: {
    saveImage (value, key) {
      this.saveSetting(key, value)
    },
    saveCategoryCaption: _.debounce(function (categoryId, caption) {
      var request = { id: categoryId, header: caption }
      Settings.saveCategoryCaption(request)
          .then((response) => {
            if (response.error) {
              this.$toast(response.message, {error: true})
            }
            return response
          })
    }, 850),
    saveSetting: _.debounce(function (key, value) {
      var request = { key: key, value: value }
      Settings.saveStoreSetting(request)
          .then((response) => {
            if (response.error) {
              return this.$toast(response.message, {error: true})
            }
            this.store.settings[request.key] = response.value
          })

          "use strict"

        jQuery(function($) {
          var slideCache = [];
          var sliderInterval = 0;
          var i = 0;
          var markup = "";
          
          $(".slider_container img").each(function(index) {
            // preload images
            slideCache[index] = new Image();
            slideCache[index].src = $(this).attr("src");
            
            // create image navigation buttons
            if(i < 1) {
              markup += $(".buttons").append("<span class=\"slide_counter_" + i + " current_slide_counter\"></span>");
            }
            else {
              markup += $(".buttons").append("<span class=\"slide_counter_" + i + "\"></span>");
            }
            
            i++;
          });
  
      function changeImage(currentSlide, stopSliderInterval, direction, button) {
        if(currentSlide.length > 0) {
          var currentPosition = 0;
          var newPosition = 0;
          var newSlide = {};
          var fadeOut = {};
          
          // stop slide interval
          if(stopSliderInterval) {
            clearInterval(sliderInterval);
          }
          
          // get current slide position
          currentPosition = currentSlide.index() + 1;
          
          if(direction == 'prev') {
            if(currentSlide.prev().length > 0) {
              newSlide = currentSlide.prev();
              newPosition = currentPosition - 1;
            }
            else {
              newSlide = currentSlide.parent('ul').children('li').last();
              newPosition = newSlide.index() + 1;
            }
          }
          else if(direction == "next") {
            if(currentSlide.next().length > 0) {
              newSlide = currentSlide.next();
              newPosition = currentPosition + 1;
            }
            else {
              newSlide = $(".slider_container li").first();
              newPosition = 1;
            }
          }
          else if(direction == "button") {
            newPosition = button.index() + 1;
            newSlide = $(".slider_container li:nth-of-type(" + newPosition + ")");
          }
          
          fadeOut = currentSlide.fadeOut(1000).promise().done(function() {
            currentSlide.removeClass("current_slide");
            newSlide.addClass("current_slide");
            newSlide.fadeIn(1000);
            $(".current_slide_counter").removeClass("current_slide_counter");
            $(".buttons span:nth-of-type(" + currentPosition + ")").removeClass("current_slide_counter");
            $(".buttons span:nth-of-type(" + newPosition + ")").addClass("current_slide_counter");
          });
        }
        else {
          return false;
        }
      }
      
      function slider() {
        changeImage($(".current_slide"), false, "next");
      }
  
    // set interval
    sliderInterval = setInterval(slider, 5000);
    
    // when the next slide button is clicked change image and stop slide interval
    $(".next_slide").click(function() {
      // cancel all queued slider animations
      $('.slider_container li').finish();
      
      // switch pause button with play button
      $(".play_pause i").removeClass("fa-pause").addClass("fa-play");
      
      // change image
      changeImage($(".current_slide"), true, "next");
    });
    
    // when the previous slide button is clicked change image and stop slide interval
    $(".prev_slide").click(function() {
      // cancel all queued slider animations
      $('.slider_container li').finish();
      
      // switch pause button with play button
      $(".play_pause i").removeClass("fa-pause").addClass("fa-play");
      
      
    // change image
    changeImage($(".current_slide"), true, "prev");
  });
  
  // when circle button is clicked
  $(".buttons").on('click', "span", function() {
    // cancel all queued slider animations
    $('.slider_container li').finish();
    
    // switch pause button with play button
    $(".play_pause i").removeClass("fa-pause").addClass("fa-play");
    
    // change image
    changeImage($(".current_slide"), true, "button", $(this));
  });
  
  $(".play_pause i").click(function() {
    if($(this).hasClass("fa-pause")) {
      clearInterval(sliderInterval);
      $(this).removeClass('fa-pause').addClass('fa-play');
    }
    else {
      sliderInterval = setInterval(slider, 5000);
      $(this).removeClass('fa-play').addClass('fa-pause');
    }
  });
});
    }, 850),
    handleSelection: function (key, media) {
      var url = media.url_md
      // determine image size to store with what setting
      if (key === 'banner_image_1' && media.url_lg !== undefined) {
        url = media.url_lg
      }
      // save the setting
      this.saveSetting(key, url)
    },
    changeImage: function (key, cropSize) {
      this.showImageModal = true
      window.scrollTo(0, 0)
      this.settingKey = key
      this.selectedCropSize = cropSize
    }
  },
  components: {
    CpImageUpload: require('../../cp-components-common/images/CpImageUpload.vue')
  },
   mounted () {
   "use strict"

jQuery(function($) {
  var slideCache = [];
  var sliderInterval = 0;
  var i = 0;
  var markup = "";
  
  $(".slider_container img").each(function(index) {
    // preload images
    slideCache[index] = new Image();
    slideCache[index].src = $(this).attr("src");
    
    // create image navigation buttons
    if(i < 1) {
      markup += $(".buttons").append("<span class=\"slide_counter_" + i + " current_slide_counter\"></span>");
    }
    else {
      markup += $(".buttons").append("<span class=\"slide_counter_" + i + "\"></span>");
    }
    
    i++;
  });
  
  function changeImage(currentSlide, stopSliderInterval, direction, button) {
    if(currentSlide.length > 0) {
      var currentPosition = 0;
      var newPosition = 0;
      var newSlide = {};
      var fadeOut = {};
      
      // stop slide interval
      if(stopSliderInterval) {
        clearInterval(sliderInterval);
      }
      
      // get current slide position
      currentPosition = currentSlide.index() + 1;
      
      if(direction == 'prev') {
        if(currentSlide.prev().length > 0) {
          newSlide = currentSlide.prev();
          newPosition = currentPosition - 1;
        }
        else {
          newSlide = currentSlide.parent('ul').children('li').last();
          newPosition = newSlide.index() + 1;
        }
      }
      else if(direction == "next") {
        if(currentSlide.next().length > 0) {
          newSlide = currentSlide.next();
          newPosition = currentPosition + 1;
        }
        else {
          newSlide = $(".slider_container li").first();
          newPosition = 1;
        }
      }
      else if(direction == "button") {
        newPosition = button.index() + 1;
        newSlide = $(".slider_container li:nth-of-type(" + newPosition + ")");
      }
      
      fadeOut = currentSlide.fadeOut(1000).promise().done(function() {
        currentSlide.removeClass("current_slide");
        newSlide.addClass("current_slide");
        newSlide.fadeIn(1000);
        $(".current_slide_counter").removeClass("current_slide_counter");
        $(".buttons span:nth-of-type(" + currentPosition + ")").removeClass("current_slide_counter");
        $(".buttons span:nth-of-type(" + newPosition + ")").addClass("current_slide_counter");
      });
    }
    else {
      return false;
    }
  }
  
  function slider() {
    changeImage($(".current_slide"), false, "next");
  }
  
  // set interval
  sliderInterval = setInterval(slider, 5000);
  
  // when the next slide button is clicked change image and stop slide interval
  $(".next_slide").click(function() {
    // cancel all queued slider animations
    $('.slider_container li').finish();
    
    // switch pause button with play button
    $(".play_pause i").removeClass("fa-pause").addClass("fa-play");
    
    // change image
    changeImage($(".current_slide"), true, "next");
  });
  
  // when the previous slide button is clicked change image and stop slide interval
  $(".prev_slide").click(function() {
    // cancel all queued slider animations
    $('.slider_container li').finish();
    
    // switch pause button with play button
    $(".play_pause i").removeClass("fa-pause").addClass("fa-play");
    
    
    // change image
    changeImage($(".current_slide"), true, "prev");
  });
  
  // when circle button is clicked
  $(".buttons").on('click', "span", function() {
    // cancel all queued slider animations
    $('.slider_container li').finish();
    
    // switch pause button with play button
    $(".play_pause i").removeClass("fa-pause").addClass("fa-play");
    
    // change image
    changeImage($(".current_slide"), true, "button", $(this));
  });
  
  $(".play_pause i").click(function() {
    if($(this).hasClass("fa-pause")) {
      clearInterval(sliderInterval);
      $(this).removeClass('fa-pause').addClass('fa-play');
    }
    else {
      sliderInterval = setInterval(slider, 5000);
      $(this).removeClass('fa-play').addClass('fa-pause');
    }
  });
});
   }
}
</script>

<style lang="scss" scoped>

.text-align-center
{
  text-align: center;
}
.store-builder-wrapper {
    @import "resources/assets/sass/var.scss";
    @import "resources/assets/sass/store-styles.scss";
    .home-blog{
      display: flex;
      padding-bottom: 10px;
    }

  
body{padding:0; margin:0;}
#container { text-align: center; }

#container .slider_container {
  margin: 0 auto;
  overflow: hidden;
  background: #ccc;
  width:100%;
  position: relative;
  display: table;
    height: 400px;
	vertical-align:middle;
}

#container .slider_container ul {
   display: table-cell;
    margin: 0;
    padding: 0;
    vertical-align: middle;
}

#container .slider_container ul li {
  display: none;
  list-style: none;
}

#container .slider_container ul li img {
  width: 100%;
  display: block;
}

#container .slider_container ul li:first-child { display: block; }

#container .slider_container .prev_slide, #container .slider_container .next_slide {
  top: 50%;
  width: 40px;
  opacity: 0.5;
  height: 40px;
  cursor: pointer;
  background: #fff;
  margin-top: -13px;
  position: absolute;
  border-radius: 20px;
}

#container .slider_container .prev_slide i, #container .slider_container .next_slide i {
  margin-top: 7px;
  font-size: 25px;
}

#container .slider_container .prev_slide .fa-angle-left, #container .slider_container .next_slide .fa-angle-left { margin-right: 4px; }

#container .slider_container .prev_slide .fa-angle-right, #container .slider_container .next_slide .fa-angle-right { margin-left: 4px; }

#container .slider_container .prev_slide { left: 16px; }

#container .slider_container .next_slide { right: 16px; }

#container .slider_container .buttons {
  left: 0;
  right: 0;
  bottom: 16px;
  text-align: center;
  position: absolute;
}

#container .slider_container .buttons span {
  width: 16px;
  height: 16px;
  opacity: 0.5;
  margin: 0 10px;
  cursor: pointer;
  background: #fff;
  border-radius: 8px;
  display: inline-block;
  box-sizing: border-box;
}

#container .slider_container .buttons .current_slide_counter { opacity: 1; }

#container .slider_container .play_pause {
  top: 15px;
  right: 15px;
  position: absolute;
}

#container .slider_container .play_pause i {
  color: #fff;
  opacity: 0.5;
  cursor: pointer;
}

/*# sourceMappingURL=main.css.map */

    .main-img{
      width: 50%;
    }
    .position-1 {
      text-align: center;
      height: 300px;
      background: #ccc;
      border: solid 1px;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    .side-position{
      background: $cp-lightGrey;
      height: 50%;
      justify-content: center;
      align-items: center;
      display: flex;
      border: solid 1px;
    }
    .side-image {
			display: flex;
			-webkit-display: flex;
			justify-content: space-around;
			-webkit-justify-content: space-around;
			flex-direction: column;
			-webkit-flex-direction: column;
			width: 50%;
      text-align: center;
    }
    .category-list{
      display: flex;
      flex-wrap: wrap;
      list-style: none;
      padding-left: 15px;
    }
    .category-image{
      max-height: 205px;
      height: 205px;
      img {
        max-width: 145px;
      }
    }
    .store-input-slogan {
        background: transparent;
        border: 1.5px dashed $cp-lightGrey;
        text-align: center;
        width: 100%;
    }
    .store-input-display-name {
        background: transparent;
        border: 1.5px dashed $cp-lightGrey;
        text-align: center;
        width: 60%;
    }
    .backoffice-categories {
      width: 140px !important;
      text-align: center;
    }
}
.cp-modal-body {
    &.upload {
        padding: 0;
        width: 95%;
        overflow-y: scroll;
    }
}
</style>
