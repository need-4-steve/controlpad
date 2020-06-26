<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Vue Grid Layout Example 1 - Basic Responsive</title>
    <meta name="viewport" content="initial-scale=1, maximum-scale=1, user-scalable=no">
    <link rel="stylesheet" href="js/vue/app.css">
</head>
<body>

<?php

if ($_POST['submitted'] == "true")
{
    // Rebuild the json //
    $json = '[';

    for ($index=1; $index <= 9; $index++)
    {
        $json .= '{"x":'.$_POST['x-'.$index].',';
        $json .= '"y":'.$_POST['y-'.$index].',';
        $json .= '"w":'.$_POST['w-'.$index].',';
        $json .= '"h":'.$_POST['h-'.$index].',';
        $json .= '"i":"'.$_POST['i-'.$index].'",';
        $json .= '"name":"'.$_POST['name-'.$index].'",';
        $json .= '"enabled":"'.$_POST['enabled-'.$index].'"},';
    }

    $json = rtrim($json, ",");
    $json .= ']';

    // Store in the database //
}

// Default json menu //
if (empty($json))
{
    $json = '[
        {"x":0,"y":0,"w":2,"h":6,"i":"1","name":"My Personal Volume","enabled":"checked"},
        {"x":2,"y":0,"w":2,"h":6,"i":"2","name":"Personally Sponsored Qualified","enabled":"checked"},
        {"x":4,"y":0,"w":2,"h":6,"i":"3","name":"Site Sales","enabled":"checked"},
        {"x":0,"y":7,"w":2,"h":6,"i":"4","name":"My Team Volume","enabled":"checked"},
        {"x":2,"y":7,"w":2,"h":6,"i":"5","name":"Level 1 (Rank Name)","enabled":"checked"},
        {"x":4,"y":7,"w":2,"h":6,"i":"6","name":"Carrer Title","enabled":"checked"},
        {"x":0,"y":9,"w":2,"h":6,"i":"7","name":"Enterprise Volume","enabled":"checked"},
        {"x":2,"y":9,"w":2,"h":6,"i":"8","name":"(Rank Name) Legs","enabled":"checked"},
        {"x":4,"y":9,"w":2,"h":6,"i":"9","name":"Current Title","enabled":"checked"},
        ]';
}

?>
    <form method='POST' action=''>

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
                    <input type='edit' :name="'name-'+item.i" :value='item.name'>
                    

                    </span>
                </grid-item>
            </grid-layout>
        </div>

    </div>

    <br>
    <input type=submit value='Save'>
    <input type=hidden name='submitted' value='true'>
    </form>

    <script src="js/vue/vue.min.js"></script>
    <script src="js/vue/vue-grid-layout.min.js"></script>
 
    <script Language="Javascript">

    var testLayout = <?php echo $json;?>

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
    });
    </script>

</body>
</html>
