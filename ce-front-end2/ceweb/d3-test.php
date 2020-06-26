<div id='chart'></div>
<script src="/ce/js/d3.v4.min.js"></script>
<script language='javascript'>
url = "my-downline-json.php?userid=4";
d3.json(url, function(error, response) {
    
    // Now use response to do some d3 magic
    alert(response['users'][0].toSource());
});
</script>