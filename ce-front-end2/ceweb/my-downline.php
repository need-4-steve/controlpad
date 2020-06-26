<?php

include 'includes/inc.ce-comm.php';
include 'includes/inc.affiliate.php';
include 'includes/inc.header.php';
include 'includes/inc.display.php';
include 'includes/inc.select.php';
include 'includes/inc.pagination.php';

SystemSelectedCheck();
$_POST["userid"] = $_SESSION['user_id'];
$fields[] = "userid";
$json = BuildAndPOST(AFFILIATE, "mydownlinelvlone", $fields);

$canvasheight = ($json['count']*20)+600;

?>    
</div>

<div class="col-md-12 col-sm-12 col-xs-12">
<div class="x_panel">
<div class="x_title">
	<h2>My Downline</small></h2>
<div class="clearfix"></div>
</div>

<div class="x_content">
	<table id="datatable-checkbox" class="table table-striped table-bordered bulk_action">

	<svg width="1400" height="<?=$canvasheight?>"></svg>
<?php

include 'includes/inc.footer.php';

?>

<script src="//d3js.org/d3.v4.min.js"></script>
<script>

var svg = d3.select("svg"),
    width = +svg.attr("width"),
    height = +svg.attr("height"),
    g = svg.append("g").attr("transform", "translate(100,0)");

var tree = d3.cluster()
    .size([height, width - 300]);
    //.size([height, width - 160]);

var stratify = d3.stratify()
    .parentId(function(d) { 
      if (d.parentid == 0)
        return;
      return d.parentid; });
    //.parentId(function(d) { return d.id.substring(0, d.id.lastIndexOf(".")); });

var jsonstr;
var clickID;

// Do initial building of downline tree //
var url = "ajax-downline.php?userid=<?=$_SESSION['user_id']?>";
d3.json(url, function(error, data)
{
    if (error) throw error;

    jsonstr = JSON.stringify(data);

    ReDraw(data);
});

// Handle each individual node click //
function NodeClick(d) 
{
    // Make the circle larger //
    //var basenode = d3.select(this);
    //basenode.append("circle")
    //    .attr("r", 5.5);    

    clickID = d.id;

    console.log("NodeClick");

    // Grab the next set of node data and append //
    var url = "ajax-downline.php?userid="+d.id;
    d3.json(url, function(error, data) {

        // Prevent no downline deadlock //
        if (data == null)
            return;

        if (error) throw error;

        console.log(data);

        // Check to make sure object not already added //
        var flagduplicate = false;
        var tmpobj = JSON.parse(jsonstr);
        var index;
        for (index=0; index < tmpobj.length; index++)
        {
            //if (tmpobj[index].id == data)
            //console.log(tmpobj[index].id);
            var index2;
            for (index2=0; index2 < data.length; index2++)
            {
                var obj2 = data[index2];
                
                console.log("Before Compare");
                if (tmpobj[index].id == obj2.id)
                {
                    tmpobj.splice(index, 1);
                    flagduplicate = true;
                }
            }
        }

        if (flagduplicate == false)
        {
            // Rebuild useable json string //
            jsonstr = jsonstr.substr(1, jsonstr.length - 2);
            var newstr = JSON.stringify(data);
            newstr = newstr.substr(1, newstr.length - 2);
            jsonstr = "["+jsonstr+","+newstr+"]";

            // Convert to an object //
            var jsonobj = JSON.parse(jsonstr);

            console.log("Before ReDraw #1");

            ReDraw(jsonobj);
        }
        else if (flagduplicate == true)
        {
            console.log("Before ReDraw #2");
            console.log(tmpobj);

            ReDraw(tmpobj);

            jsonstr = JSON.stringify(tmpobj);
        }
    });

    //g.selectAll(".node")
    //    .enter()
    //    .attr("r", function(d) { return 5.5; });
}

///////////////////////////////////
// Redraw the whole tree for now //
///////////////////////////////////
function ReDraw(data)
{
    d3.selectAll(".node").remove(); // Clear out everything //
    d3.selectAll(".link").remove(); // Clear out everything //

    var root = stratify(data)
        .sort(function(a, b) {
            return (a.height - b.height)
        });
          //return (a.height - b.height) || a.id.localeCompare(b.id); });

    console.log("ReDraw Initial Load");
   
    tree(root);

    var link = g.selectAll(".link")
        .data(root.descendants().slice(1))
        .enter().append("path")
        .attr("class", "link")
        .attr("d", function(d) {
          return "M" + d.y + "," + d.x
              + "C" + (d.parent.y + 100) + "," + d.x
              + " " + (d.parent.y + 100) + "," + d.parent.x
              + " " + d.parent.y + "," + d.parent.x;
        });

    var node = g.selectAll(".node")
        .data(root.descendants())
        .enter().append("g")
        .attr("class", function(d) { return "node" + (d.children ? " node--internal" : " node--leaf"); })
        .attr("transform", function(d) { return "translate(" + d.y + "," + d.x + ")"; })

    node.append("circle")
        .style("fill", function (d) { 
            if (d.data.count > 0) 
                return '#0fba00'; 
            else
                return '#707070'; }) // Fill red on empty downline //
        .attr("r", 5); // 2.5

    node.append("text")
        .style("font-size", "14px")
        .attr("dy", 3)
        .attr("x", function(d) { return d.children ? -8 : 8; })
        .style("text-anchor", function(d) { return d.children ? "end" : "start"; })
        .text(function(d) { return d.data.firstname+" "+d.data.lastname+" ("+d.data.id+")"; })
        //.text(function(d) { return d.id.substring(d.id.lastIndexOf(".") + 1); });

    node.on("click", NodeClick);

    //node.on("mouseover" : function(d) { d3.select(this).style("cursor", "pointer"); })
}

</script>