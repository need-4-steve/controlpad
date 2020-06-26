 <?php

include 'includes/inc.ce-comm.php';
include 'includes/inc.affiliate.php';
include 'includes/inc.header.php';
include 'includes/inc.pagination.php';
include 'includes/inc.display.php';

SystemSelectedCheck();

?>
<link rel="stylesheet" href="js/vue/app.css">
</div>

<div class="col-md-12 col-sm-12 col-xs-12">
<div class="x_panel">
<div class="x_title">
	<h2>Affiliate Home Configuration</small></h2>
<div class="clearfix"></div>
</div>
<div class="x_content">
	<table border=1>
	
<?php
	
	//////////////////////////////////
	// Grab the affiliatehome value //
	//////////////////////////////////
    $fields[] = "varname";
    $_POST['varname'] = "affiliatehome";
	$retvaljson = BuildAndPOST(CLIENT, "settingsget", $fields, $pagvals);
    if (($retvaljson['errors'][status] == "400") && ($retvaljson['errors']['detail'] == "There are no records"))
	{
        $jsonhome = AffilDefaultHomeJson();
	}
	else
	{
		$jsonhome = json_decode($retvaljson['settings'][0]['value']);
        $jsonhome = json_encode($jsonhome->layout);
	}

	///////////////////////////////////////
	// Do vue2 display of affiliate home //
	///////////////////////////////////////
?>
    <div id="app" style="width: 100%;">

        <div id="content">

            <grid-layout :layout="layout"
                         :col-num="12"
                         :row-height="30"
                         :is-draggable="draggable"
                         :is-resizable="false"
                         :vertical-compact="true"
                         :use-css-transforms="true"
                    >
                <grid-item v-for="item in layout"
                           :x="item.x"
                           :y="item.y"
                           :w="item.w"
                           :h="item.h"
                           :i="item.i"
                        >
                    
                    <input type='hidden' :name="'i-'+item.i" :value='item.i'>
                    <input type='hidden' :name="'x-'+item.i" :value='item.x'>
                    <input type='hidden' :name="'y-'+item.i" :value='item.y'>
                    <input type='hidden' :name="'w-'+item.i" :value='item.w'>
                    <input type='hidden' :name="'h-'+item.i" :value='item.h'>
                    <span class="text">{{item.i}}-<font size='3'>Enabled</font><input type='checkbox' v-model="item.enabled" :name="'enabled-'+item.i" :value='item.enabled'>
                    <font size=2><input type='edit' :name="'name-'+item.i" :value='item.name'></font>
                    
                    </span>
                </grid-item>
            </grid-layout>
        </div>

        <input type=submit value='Save' @click="getEvents">

    </div>

    <br>
    <!--<input type=submit value='Save' v-on="click: addEvent">-->
    <!--<input type=hidden name='submitted' value='true'>-->

    <script src="js/vue/vue.min.js"></script>
    <script src="js/vue/vue-grid-layout.min.js"></script>
    <script src='https://cdnjs.cloudflare.com/ajax/libs/vue-resource/1.5.0/vue-resource.min.js'></script>

    <script Language="Javascript">

   	var testLayout = <?php echo $jsonhome;?>

    //var Vue = require('vue');

    Vue.config.debug = true;
    Vue.config.devtools = true;

    var GridLayout = VueGridLayout.GridLayout;
    var GridItem = VueGridLayout.GridItem;


    new Vue({
        el: '#app',
        components: {
            "GridLayout": GridLayout,
            "GridItem": GridItem
        },
        data: {
            layout: testLayout,
            draggable: true,
            resizable: true,
            index: 0
        },
        methods: {
           getEvents () {

            console.log("Before: endpoint = save-affil-home.php");

            var endpoint = "save-affil-home.php";
            var params = {
                layout: this.layout,
                submitted: "true"
            }; //""; // Json object property I want to post //

             this.$http.post(endpoint, params)
               .then(function (response) {
                 this.events = response.data.data
               }, function (error) {
                 console.log(error.statusText)
               })
           }
         }
    });

    </script>


	</table>
</div>

<?php

include 'includes/inc.footer.php';

?>