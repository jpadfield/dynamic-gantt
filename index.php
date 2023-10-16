<?php

/*
 
 Config settings can be passed to Mermaid live editor and the image generator rather than just packaging up the mermaid data a json blob in the following format needs to be packaged up with pako
 * 
 {
  "code":"gantt\n    dateFormat  YYYY-MM-DD\n    ....",
  "mermaid":"{\"theme\": \"default\",\n  \"logLevel\": \"warn\",\n  
    \"flowchart\": {\n    \"curve\": \"basis\",\n    \"htmlLabels\": true\n  }\n}",
  }
  
  
  
 
 */
$versions = array(
  "jquery" => "3.7.0",
  "jquery-ui" => "1.13.2",
  "bootstrap" => "5.3.2",
  "mermaid" => "10.5.0", 
  "tether" => "2.0.0",
  "pako" => "2.1.0",
  "base64" => "3.7.5"
  );

if (isset($_GET["debug"])) {}

if (isset($_GET["simple"])) {$simple = true;}
else {$simple = false;}
  
if (isset($_SERVER["SCRIPT_URI"]))
  {$thisPage = $_SERVER["SCRIPT_URI"];}
else
  {$thisPage = "./";}

$start = false;
$examples = array();
$pako = false;
$mermaid = "";
$customConfig = array();

// Expects pako compressed data and pulls image directly from https://mermaid.ink
if (isset($_GET["image"]))
  {getModelImage($_GET["image"]);
   exit;}  
// Default process of using the tool - receiving data from POST form.
else if (isset($_POST["triplesTxt"]) and $_POST["triplesTxt"])
  {$cleanTxt = $_POST["triplesTxt"];
   $mermaid = tsv2MermaidStr ($_POST["triplesTxt"]);}
else if (isset($_GET["url"]))
  {
  $cleanTxt = getRemoteURL ($_GET["url"]);
  if (!$cleanTxt)
    {$cleanTxt = "gantt\n//Sorry supplied URL not found.\n".
      "Data Missing	0	6	crit";}  
  $mermaid = tsv2MermaidStr ($cleanTxt);      
  }
else if (isset($_GET["data"]) and preg_match("/^[p][a][k][o][:](.+)$/", $_GET["data"], $m))
  {
  $cleanTxt = "Please wait  tooltip  Processing supplied data";
  $pako = $m[1];   
  }
else if (isset($_POST["triples"]))
  {
  $cleanTriplesTxt = $_POST["triples"];
  $mermaid = tsv2MermaidStr ($_POST["triples"]);
	
  header('Content-Type: application/json');
  header("Access-Control-Allow-Origin: *");
  echo json_encode(array("triples" => $cleanTriplesTxt, "mermaid" => $mermaid));
  exit;
  }   
else
  {$default = file_get_contents("default.csv");
   $cleanTxt = $default;
   $mermaid = tsv2MermaidStr ($default);}


//prg(0, $start);
//prg(0, dA ("0,10"));
//prg(0, dA (1));
//prg(0, dA ("1,10"));
//prg(0, dA ("-0,10"));
//prg(0, dA (-1));
//prg(0, dA ("-1,10"));

if ($simple)
  {$html = buildPageSimple ($cleanTxt, $mermaid);}
else
  {$html = buildPage ($cleanTxt, $mermaid);}
echo $html;
exit;

////////////////////////////////////////////////////////////////////////

// Processes simple site json data
function createMermaidStr ($dets)
  {
  global $start;
  
  if (!isset($dets["start date"]))
      {die("ERROR: $d[file] format problems - 'start date' not found\n");}
    
  $start = $dets["start date"];
  $prefs = array_keys($dets["groups"]);
  $first = $prefs[0];

  if (!isset($dets["project"])) {$dets["project"] = "Please add a project title";}
  if (!isset($dets["margin"])) {$dets["margin"] = -3;}
    
  array_unshift($dets["groups"][$first]["stages"],
  array("Add as a margin", "", $dets["margin"], $dets["margin"]));
    
  $str = "";
  $jsstr = "var rectIDs = [];\n";

  foreach ($dets["groups"] as $pref => $ga)
    {
    $str .= "\tsection $ga[title]\n";
    $no = 0;
    foreach ($ga["stages"] as $k => $a)
      {
      if ($a[1]) {$a[1] = "$a[1], ";}
      $jsstr .= "rectIDs[\"".$pref.$no."\"] = \"".dA($a[2])." - ".dA($a[3])."\";\n";
      $str .= "\t\t".$a[0]." :$a[1]$pref$no, ".dA($a[2]).
        ", ".dA($a[3])."\n";
      $no++;
      }
    }
    
      ob_start();
    echo <<<END
  gantt
    dateFormat  YYYY-MM-DD
    title $dets[project]  
    $str
END;
    $result = ob_get_contents();
    ob_end_clean(); // Don't send output to client
    
  return ($result);
  }
  
function line2array ($line)
  {
  $line = trim($line);

  if (preg_match("/^[\/][\/].*$/", $line, $m))
    {$arr = array();}
  else if (preg_match("/^.+\t.+$/", $line, $m))
    {$arr = explode ("\t", $line);}
  else if (preg_match("/^.+[,].+$/", $line, $m))
    {$arr = explode (",", $line);}
  else
    {$arr = array($line);}   
    
  return ($arr);
  }
  
function sectionCode($string) {
  $string = preg_replace('/[^A-Za-z0-9]/', '', $string); 
  return (strtolower($string));
}
  
function tsv2MermaidStr ($data)
  {
  global $start, $customConfig;
  
  $json = json_decode($data, true);
  
  $mermaidStr = "";

  if($json)
    {
      foreach ($data as $k => $line) 
    {
    if (preg_match("/^.+\t.+\t.+$/", $line, $m))
      {$trip = explode ("\t", $line);}
    else if (preg_match("/^.+[,].+[,].+$/", $line, $m))
      {$trip = explode (",", $line);}
    else
      {$trip = array($line);}  
    }
  
  if (!isset($dets["start date"]))
      {die("ERROR: $d[file] format problems - 'start date' not found\n");}
    
  $start = $dets["start date"];
  $prefs = array_keys($dets["groups"]);
  $first = $prefs[0];

  if (!isset($dets["project"])) {$dets["project"] = "Please add a project title";}
  if (!isset($dets["margin"])) {$dets["margin"] = -3;}
    
  array_unshift($dets["groups"][$first]["stages"],
  array("Add as a margin", "", $dets["margin"], $dets["margin"]));
    
  $str = "";
  $jsstr = "var rectIDs = [];\n";

  foreach ($dets["groups"] as $pref => $ga)
    {
    $str .= "\tsection $ga[title]\n";
    $no = 0;
    foreach ($ga["stages"] as $k => $a)
      {
      if ($a[1]) {$a[1] = "$a[1], ";}
      $jsstr .= "rectIDs[\"".$pref.$no."\"] = \"".dA($a[2])." - ".dA($a[3])."\";\n";
      $str .= "\t\t".$a[0]." :$a[1]$pref$no, ".dA($a[2]).
        ", ".dA($a[3])."\n";
      $no++;
      }
    }
    
      ob_start();
    echo <<<END
  gantt
    dateFormat  YYYY-MM-DD
    title $dets[project]  
    $str
END;
    $mermaidStr = ob_get_contents();
    ob_end_clean(); // Don't send output to client
    }
  else
    {
    $data = explode("\n", $data);

    $fl = explode(" ", trim(array_shift($data), "/"));

    if (trim($fl[0]) == "gantt")
      {
      $mermaidStr .=  "gantt\n    dateFormat  YYYY-MM-DD\n";
      $title = "Mermaid Gantt Chart";
      $margin = 1;
      $first = true;
      $todayMarker = false;
            
      foreach ($data as $k => $line) 
        {       
        $arr = line2array ($line);
	$arr = array_pad($arr, 4, 0);
        
        if ($arr and $arr[0])
          {
          if (in_array(strtolower($arr[0]), array("start date", "start")))
            {$start = $arr[1];}
          else if (strtolower($arr[0]) == "title")  
            {$title = $arr[1];}
          else if (strtolower($arr[0]) == "today")  
            {$todayMarker = "todayMarker off\n";}
          else if (strtolower($arr[0]) == "width")  
            {$customConfig["gantt"]["useWidth"] = intval($arr[1]);}
          else if (strtolower($arr[0]) == "margin")  
            {$margin = $arr[1];
	     if ($margin > 10) {$margin = 10;}
	     if ($margin < 1) {$margin = 1;}
	     $customConfig["gantt"]["leftPadding"] = 50 * $margin;}
          else if (strtolower($arr[0]) == "group")  
            {
            $sno = 0;
            $scode = sectionCode($arr[1]);
            if ($first)
              {$first = false;
               $mermaidStr .=  "    title  $title\n".$todayMarker;
               $mermaidStr .=  "    section $arr[1]\n";
               $sno++;}
            else
              {$mermaidStr .=  "    section $arr[1]\n";}
            }
          else
            {if ($arr[3]) {$arr[3] = "$arr[3], ";}
             $mermaidStr .=  "       $arr[0] :$arr[3]$scode$sno, ".dA($arr[1]).", ".dA($arr[2])."\n";
             $sno++;}
          }
        }
      } 

    }
    
  

    
  return ($mermaidStr);
  }
  
function dA ($v)
  {
  global $start;
  
  if (preg_match("/^([-]*)([0-9]+)[,.]*([0-9]*)$/", $v, $a))
    {
    $m = intval($a[2]);
    
    if($a[3])
      {$d = intval($a[3]-1);}
    else
      {$d = 0;}
    
    $date=new DateTime($start); // date object created.

    $invert = 0;
    if ($a[1])
      {$invert = 1;
       $m = abs($m);
       if ($d) {$d = abs($d + 1);}}
       
    $di = new DateInterval('P'.$m.'M'.$d.'D');
    $di->invert = $invert;
    $date->add($di); // inerval of 1 year 3 months added
    $out = $date->format('Y-m-d'); // Output is 2020-Aug-30
    }
  else
    {$time = strtotime($v);
     $out = strftime("%Y-%m-%d",  $time);}
     
  return($out);
  }
   
function buildExamplesDD ()
  {
  global $examples;

  if ($examples) {
    ob_start();
    echo <<<END
  <li class="nav-item dropdown">
    <a class="nav-link dropdown-toggle" href="#" id="dropdownMenuExamples" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
      Examples
    </a>
  <div class="dropdown-menu  dropdown-menu-end" aria-labelledby="dropdownMenuExamples">
END;
    $html = ob_get_contents();
    ob_end_clean(); // Don't send output to client

    foreach ($examples as $k => $a)
      {$html .= "<a class=\"dropdown-item\" href=\"./?example=$k\">$a[title]</a>\n";}

    $html .= "</div></li>";
    }
  else
    {$html = "";}

  return ($html);
  }
  
function buildLinksDD ()
  {
  global $bookmark;

  $date = date('Y-m-d_H-i-s');
  
  ob_start(); //style="margin-right: 8px; float:right; margin-bottom: 16px;" 
  
  // 
  echo <<<END
  <li class="nav-item dropdown">
    <a class="nav-link dropdown-toggle" href="#" id="dropdownMenuLinks" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
      Links
    </a>
  <div class="dropdown-menu  dropdown-menu-end" aria-labelledby="dropdownMenuLinks">
    <a class="dropdown-item" id="downloadLink" title="Mermaid Get PNG" href="" download="model_$date.png">Download Image</a>    
    <!-- <a class="dropdown-item" title="Bookmark Link" href="$bookmark" target="_blank">Bookmark Link</a> -->
    <a class="dropdown-item" id="bookmark" title="Bookmark Link" href="" target="_blank">Bookmark Link</a>
    <a class="dropdown-item" id="mermaidLink" title="Edit further in the Mermaid Live Editor" href="" target="_blank">Mermaid Editor</a>
    <a class="dropdown-item" id="mermaidCode" title="Copy Mermaid Code to Clipboard" onclick="copyMermaid()" href="">Mermaid Code</a>
END;
  $html = ob_get_contents();
  ob_end_clean(); // Don't send output to client

  $html .= "</div></li>";

  return ($html);
  }
  
function buildPage ($triplesTxt, $mermaid)
  {
  global  $thisPage, $versions, $pako, $customConfig;
  
  $exms = buildExamplesDD ();
  $links = buildLinksDD ();
  $modal = buildModal ();
  
  // configs can also be passed t the top of the mermaid code using the 
  // format: '%%{init: { "maxTextSize": 900000, "startOnLoad": true } }%%
  
  $config = array(
    "maxTextSize" => 900000,
    "startOnLoad" => true,
    "theme" => "default",
    "logLevel" => 4,
    "securityLevel" => "loose", 
    "logLevel" => "warn",
    "flowchart" => array( 
      "curve" => "basis",
      "useMaxWidth" => false,
      "htmlLabels" => true),
    "gantt" => array(
      "titleTopMargin" => 25,
      "barHeight" => 20,
      "barGap" => 4,
      "topPadding" => 50,
      "leftPadding" => 50,
      "topAxis" => true 
      )
    );
  
  $config = array_update($config, $customConfig);
  $config_json = json_encode($config);
  $config_json = trim($config_json, "{}");  
  
  unset($config["securityLevel"]); //this can upset the mermaid live editor
  
  $code = array(
    "code" => $mermaid,
    "mermaid" => $config
    );
 
  $code = array_update($code, $customConfig);
  $json_code = json_encode($code);

  $bw = "26px";
  
  $vs[0] = $versions["bootstrap"];
  $vs[1] = $versions["jquery"];
  $vs[2] = $versions["jquery-ui"];
  $vs[3] = $versions["bootstrap"];
  $vs[4] = $versions["mermaid"];
  $vs[5] = $versions["tether"];
  $vs[6] = $versions["pako"];
  $vs[7] = $versions["base64"];
  
  //$jslib = "https://unpkg.com";
  $jslib = "https://cdn.jsdelivr.net/npm";
  ob_start();
  echo <<<END

<!DOCTYPE html>
<html lang="en">
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=Edge">
  <meta charset="utf-8">
  <title>Dynamic Simple Modelling</title>
  <link href="$jslib/bootstrap@$vs[0]/dist/css/bootstrap.min.css" rel="stylesheet" type="text/css">
  <link href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@5.15.4/css/all.min.css" rel="stylesheet" type="text/css">
  <link href="css/local.css" rel="stylesheet" type="text/css">
  <style>
  

/* Added to get the hover texts or tooltips to appear and be formatted.
based on values in https://unpkg.com/browse/mermaid@6.0.0/dist/mermaid.css */
div.mermaidTooltip {
  position: absolute;
  text-align: center;
  max-width: 300px;
  padding: 5px;
  font-family: 'trebuchet ms', verdana, arial;
  font-size: 1rem;
  background: #ffffde;
  border: 1px solid #aaaa33;
  border-radius: 5px;
  pointer-events: none;
  z-index: 10000;
}

/* Style for the navigation bar */
nav {
  background-color: #333;
}  
  
  


.handle {
  width: 10px;
  height: 10px;
  top: 10px;
  left: 33%; 
}

  </style>
</head>
<body>

<div id="page" class="container-fluid h-100">

  <nav class="navbar navbar-expand-lg navbar-light bg-light">

      <a title="GitHub Dynamic Gantt" href="https://github.com/jpadfield/dynamic-gantt"  target="_blank"  class="imbutton" style="float:right;" >
  <img alt="GitHub Logo" aria-label="GitHub Logo" src="graphics/GitHub-Mark-64px.png" style="margin-left:10px;" width="32" /></a>
  
      <h1 class="navbar-brand" style="font-size:1.5rem;margin:0px 16px 0px 16px;">Simple Dynamic Gantt</h1>
      
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span></button>
      
      <div class="collapse navbar-collapse float-end" id="navbarSupportedContent">
      
      <span class="navbar-text w-100">
      
  <ul class="navbar-nav ml-auto float-end">
    $exms
    $links
    <li class="nav-item">
      <a href="#myModal" data-bs-toggle="modal" data-bs-target="#helpModalCenter" class="nav-link me-4">Info</a></li>
  </ul>
  </span>
      </div>
      
  </nav>
  
  <div class="row h-100 flex-grow-1">
    <div class="col-4 d-flex flex-column h-100" id="left_panel">
      
        <div  id="textholder" class="textareadiv form-group flex-grow-1 d-flex flex-column">
        <form id="triplesFrom" class="flex-grow-1"action="$thisPage" method="post">
        
          <textarea class="h-100 form-control flex-grow-1 rounded-0 detectTab" id="triplesTxt" name="triplesTxt"  style="overflow-y:scroll;" aria-label="Textarea for triples">$triplesTxt</textarea>
          <div class="tbtns" style="">
            <button title="Refresh Model" class="btn btn-default textbtn" id="refreshM" type="submit"  aria-label="Refresh Model"><img aria-label="Refresh Model"  alt="Refresh Model" src="graphics/view-refresh.png" width="$bw" /></button>
            <button title="Clear Text" class="btn btn-default textbtn" id="clear" type="button"  aria-label="Clear Textarea"><img aria-label="Clear Text" alt="Clear Text" src="graphics/clear-text.png" width="$bw" /></button>
            <button title="Help" class="btn btn-default textbtn" id="help" type="button" data-bs-toggle="modal" data-bs-target="#helpModalCenter" aria-label="Open Help Modal"><img alt="Help" aria-label="Help" src="graphics/help.png" width="$bw" /></button>
            <button title="Toggle Text Fullscreen" class="btn btn-default textbtn" id="tfs" type="button"  aria-label="Toggle Textarea Full-screen" onclick="togglefullscreen('tfs', 'textholder')"><img alt="Toggle Fullscreen" aria-label="Toggle Fullscreen" src="graphics/view-fullscreen.png" width="$bw" /></button>
          </div>
          </form> 
        </div>
      
    </div>

    <div class="col-8 d-flex flex-column h-100" id="holder" style="position:relative;background-color:white;">
    
      <div id="modelDiv" style="height:100%" class="mermaid">$mermaid</div>
      <div id="modelDivTxt" style="display:none">$mermaid</div>
      <div class="tbtns" style="">
        <div class="form-check form-switch">
          <input title="Toggle Pan & Zoom function" class="form-check-input" type="checkbox" role="switch" id="zoom-toggle" style="margin-right:0.5em; margin-bottom:2px; margin-top:8px; width:3em; height:1.5em;" onclick="modelZoom()">  
          <button title="Toggle Model Fullscreen" class="btn btn-default nav-button textbtn" id="fs"  aria-label="Toggle Model Full-screen"  style="top:0px;left:0px;" onclick="togglefullscreen('fs', 'holder')"><img   alt="Toggle Fullscreen"  aria-label="Toggle Fullscreen" src="graphics/view-fullscreen.png" width="$bw" /></button>
        </div>
      </div>
     
      
    </div>

    <div class="handle position-absolute bg-warning"></div>
  </div>
  
  $modal
  
</div>
      
  <script src="$jslib/jquery@$vs[1]/dist/jquery.min.js"></script> 
  <script src="$jslib/jquery-ui@$vs[2]/dist/jquery-ui.min.js"></script>  
  <script src="$jslib/tether@$vs[5]/dist/js/tether.min.js"></script>
  <script src="$jslib/bootstrap@$vs[3]/dist/js/bootstrap.bundle.min.js"></script>
  <!-- <script src="$jslib/mermaid@$vs[4]/dist/mermaid.min.js"></script> -->
  <script type="module">
  import mermaid from 'https://cdn.jsdelivr.net/npm/mermaid@10/dist/mermaid.esm.min.mjs';    
    let config = {
      $config_json},
      mermaid: {
	callback:function(id) {modelZoom ()}
	}}
      
  mermaid.initialize(config);

</script>
  <script src="$jslib/pako@$vs[6]/dist/pako.min.js"></script>
  <script src="$jslib/js-base64@$vs[7]/base64.min.js"></script>
  <script src="./js/svg-pan-zoom.js" crossorigin="anonymous"></script> 
  <script src="./js/local.js"></script>
  <script>
 
  let code = JSON.stringify($json_code);
  let pcode = '$pako';
  
///////////////////////////
$(document).ready(function() 
  {
  console.log ("handle stuff");
  var rightPad = $('#holder').outerWidth() - $('#holder').width();
  
var minLeft = 20; 
var maxLeft = 80;
  
  // Set handle size and position 
  $('.handle').css({
    'padding': '0px',
    'width': '10px',
    'height': '10px',
    top: $('#holder').offset().top
  });
  
  var handWidth = $('.handle').outerWidth();    
  // Set handle size and position 
  $('.handle').css({
    left: $('#holder').offset().left - (handWidth/2)
  });


  // Make handle draggable to resize columns 
  $('.handle').draggable({
    axis: 'x',
    containment: '.row',
    start: function(event, ui) {
      // Set initial widths on drag start
      var leftWidth = $('#left_panel').outerWidth();
      ui.originalPosition = {left: leftWidth + rightPad};
      },
    drag: function(event, ui) {
      // Calculate new widths based on handle position
      var leftWidth = ui.position.left + rightPad;      
      var viewportWidth = $(window).width();
      
      var leftPct = (leftWidth / viewportWidth) * 100;
      leftPct = Math.min(Math.max(leftPct, minLeft), maxLeft); 
      var rightPct = 100 - leftPct;
      
      $('#left_panel').css('width', leftPct + '%'); 
      $('#holder').css('width', rightPct + '%');
      },
    stop: function() {
      $('.handle').css({
        left: $('#holder').offset().left - (handWidth/2)
        });
      }
    });
    
  // Refresh on resize
  $(window).resize(function() {
    handleLeft = $('#holder').offset().left - (handWidth/2);
    $('.handle').css('left', handleLeft);
    $('.handle').css('top', $('#holder').offset().top); 
      });
  });

//start = "2024-10-01"
//console.log(start);
//console.log(dA ("0,10"));
//console.log(dA (1));
//console.log(dA ("1,10"));
//console.log(dA ("-0,10"));
//console.log(dA (-1));
//console.log(dA ("-1,10"));

////////////////////////////////
    
  </script>  
  </body>
</html>

END;
$html = ob_get_contents();
ob_end_clean(); // Don't send output to client

return($html);
}

function buildPageSimple ($triplesTxt, $mermaid)
  {
  global  $thisPage, $versions, $pako, $customConfig;
  
  $exms = buildExamplesDD ();
  $links = buildLinksDD ();
  $modal = buildModal ();
  
  // configs can also be passed t the top of the mermaid code using the 
  // format: '%%{init: { "maxTextSize": 900000, "startOnLoad": true } }%%
  
  $code = array(
    "code" => $mermaid,
    "mermaid" => array(
      "maxTextSize" => 900000,
      "startOnLoad" => true,
      "theme" => "default",
      "logLevel" => 4,
      //"securityLevel" => "loose", This option forces an alert in the live editor
      "logLevel" => "warn",
      "flowchart" => array( 
	"curve" => "basis",
        "useMaxWidth" => false,
	"htmlLabels" => true),
      "gantt" => array(
        "titleTopMargin" => 25,
        "barHeight" => 20,
        "barGap" => 4,
        "topPadding" => 50,
        "leftPadding" => 50,
        "topAxis" => true 
        )
      ));
 
  $json_code = json_encode($code);

  $bw = "26px";
  
  $vs[0] = $versions["bootstrap"];
  $vs[1] = $versions["jquery"];
  $vs[2] = $versions["bootstrap"];
  $vs[3] = $versions["mermaid"];
  $vs[4] = $versions["tether"];
  $vs[5] = $versions["pako"];
  $vs[6] = $versions["base64"];
  
  $jslib = "https://unpkg.com";
  $jslib = "https://cdn.jsdelivr.net/npm";
  ob_start();
  echo <<<END

<!DOCTYPE html>
<html lang="en">
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=Edge">
  <meta charset="utf-8">
  <title>Dynamic Simple Modelling</title>
  <link href="$jslib/bootstrap@$vs[0]/dist/css/bootstrap.min.css" rel="stylesheet" type="text/css">
  <link href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@5.15.4/css/all.min.css" rel="stylesheet" type="text/css">
  <link href="css/local.css" rel="stylesheet" type="text/css">
  <style>
  

/* Added to get the hover texts or tooltips to appear and be formatted.
based on values in https://unpkg.com/browse/mermaid@6.0.0/dist/mermaid.css */
div.mermaidTooltip {
  position: absolute;
  text-align: center;
  max-width: 300px;
  padding: 5px;
  font-family: 'trebuchet ms', verdana, arial;
  font-size: 1rem;
  background: #ffffde;
  border: 1px solid #aaaa33;
  border-radius: 5px;
  pointer-events: none;
  z-index: 10000;
}  
  </style>
</head>
<body class="vh-100" style="overflow:hidden" >
  <div class="tbtns" style="">
    <div class="form-check form-switch">
      <input title="Toggle Pan & Zoom function" class="form-check-input" type="checkbox" role="switch" id="zoom-toggle" style="margin-right:0.5em; margin-bottom:2px; margin-top:8px; width:3em; height:1.5em;" onclick="modelZoom()">  
    </div>
</div>
<div id="modelDiv" style="height:100%" class="mermaid">$mermaid</div>
<div id="modelDivTxt" style="display:none">$mermaid</div>
      
  <script src="$jslib/jquery@$vs[1]/dist/jquery.min.js"></script>  
  <script src="$jslib/tether@$vs[4]/dist/js/tether.min.js"></script>
  <script src="$jslib/bootstrap@$vs[2]/dist/js/bootstrap.bundle.min.js"></script>
  <!-- <script src="$jslib/mermaid@$vs[3]/dist/mermaid.min.js"></script> -->
  <script type="module">
  import mermaid from 'https://cdn.jsdelivr.net/npm/mermaid@10/dist/mermaid.esm.min.mjs';    
    let config = {
    maxTextSize: 900000,
    startOnLoad:true, 
    securityLevel: "loose",    
    gantt: { titleTopMargin:25, barHeight:20, barGap:4, topPadding:50, sidePadding:50, topAxis:true },
    mermaid: {
      callback:function(id) {modelZoom ()}
      }}
      
  mermaid.initialize(config);

</script>
  <script src="$jslib/pako@$vs[5]/dist/pako.min.js"></script>
  <script src="$jslib/js-base64@$vs[6]/base64.min.js"></script>
  <script src="./js/svg-pan-zoom.js" crossorigin="anonymous"></script> 
  <script src="./js/local.js"></script>
  <script>
 
  let code = JSON.stringify($json_code);
  let pcode = '$pako';
    
  </script>  
  </body>
</html>

END;
$html = ob_get_contents();
ob_end_clean(); // Don't send output to client

return($html);
}


function buildModal ()
  {
  // Based on https://bbbootstrap.com/snippets/modal-multiple-tabs-89860645
  $tabs = array(
    "Information" => 'This is an interactive live modelling system which can automatically convert simple <b>tab</b> separated triples or JSON-LD (experimental) into graphical models and flow diagrams using the <a href="https://mermaid-js.github.io/">Mermaid Javascript library</a>. It has been designed to be very simple to use. The tab separated triples can be typed directly into the web-page, but users can also work and prepare data in three (or four columns if applying formatting) of a online spreadsheet and then just copy the relevant columns and paste them directly into the data entry text box.<br/><br/>In general the tool makes use of a simple set of predefined formats for the flow diagrams, taken from the Mermaid library, but a <a href="?example=example_formats">series of additional predefined formats</a> have also be provided and can be defined as a fourth "triple".<br/><br/>The <a href="./">default landing page</a> presents an example set or data, and the generated model demonstrates the functionality provided. As a new user it is recommended that you try editing this data to see how the diagrams are built. Additional examples are also available via the <b>Examples</b> menu option in the upper right.<br/><br/> The system has also be defined to allow models to be shared via automatically generate, and often quite long, URLs. This can be accessed via the <b>Links</b> menu option, as the <b>Bookmark Link</b>. A static image version of any given model can be saved by following the <b>Download Image</b> option and using the tools provide by the <a href="https://mermaid.ink/">Mermaid Ink</a> system. It is also possible to further edit a model using the full options of the Mermaid library using the <a href="https://mermaid-js.github.io/mermaid-live-editor">Mermaid Live Editor</a>, via the <b>Mermaid Editor</b> link.
    <br/><br/>
    <h5>Acknowledgements:</h5>
This tool was originally developed within the National Gallery, but its continue development and public presentation has also been supported by:
<br/><br/>
    <h6></a>The H2020 <a href="https://www.iperionhs.eu/" rel="nofollow">IPERION-HS</a> project</h6>
<p dir="auto"><a href="https://www.iperionhs.eu/" rel="nofollow"><img height="42px" src="./graphics/IPERION-HS%20Logo.png" alt="IPERION-HS" style="max-width: 100%;"></a>&nbsp;&nbsp;
<a href="https://www.iperionhs.eu/" rel="nofollow"><img height="32px" src="./graphics/iperionhs-eu-tag2.png" alt="IPERION-HS" style="max-width: 100%;"></a></p>
<br/>
<h6>The H2020 <a href="https://sshopencloud.eu/" rel="nofollow">SSHOC</a> project</h6>
<p><a href="https://sshopencloud.eu/" rel="nofollow"><img height="48px" src="./graphics/sshoc-logo.png" alt="SSHOC" style="max-width: 100%;"></a>&nbsp;&nbsp;
<a href="https://sshopencloud.eu/" rel="nofollow"><img height="32px" src="./graphics/sshoc-eu-tag2.png" alt="SSHOC" style="max-width: 100%;"></a></p>
<br/>
<h6>The AHRC Funded <a href="https://linked.art/" rel="nofollow">Linked.Art</a> project</h6>
<p><a href="https://ahrc.ukri.org/" rel="nofollow"><img height="48px" src="./graphics/UKRI_AHR_Council-Logo_Horiz-RGB.png" alt="Linked.Art" style="max-width: 100%;"></a></p>',
    //"Blank Nodes" => 'Details to be added',
    //"Formatting" => 'Details to be added',
    //"Aliases" => 'Details to be added'
    );
  
  $tabHeaders = false;
  $tabContents = false;

  $no = 1;
  $active = "active";
  foreach ($tabs as $k => $ht)
    {
    $dno = sprintf('%02d', $no);

    $tabHeaders .= "  
      <li class=\"nav-item\">
        <a href=\"#tab$dno\" class=\"nav-link $active\" data-bs-toggle=\"tab\">$k</a>
      </li>";
    
    $tabContents .= "  
      <div class=\"tab-pane fade show $active\" id=\"tab$dno\">
        <h5 class=\"text-center mb-4 mt-0 pt-4\">$k</h5>
        <div class=\"m-4\">$ht</div>
      </div>";
    
    $active = "";  
    $no++;
    }
  
  ob_start();
  echo <<<END
  <!-- Modal-->
  <div id="helpModalCenter" tabindex="-1" role="dialog" aria-label="Help Modal" aria-hidden="true" class="modal fade text-left">
    <div role="document" class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content">
        
        <ul class="nav nav-tabs" id="myTab">
          $tabHeaders
        </ul>
        <div class="tab-content">
          $tabContents
        </div>
      <div class="line"></div>
      <div class="modal-footer d-flex flex-column justify-content-center border-0">
        <p class="text-muted">More questions or issues? - <a href="https://github.com/jpadfield/dynamic-modelling/issues">Try Github</a>.</p>
      </div>
        
      </div>
    </div>
    
  </div>
END;
  $html = ob_get_contents();
  ob_end_clean(); // Don't send output to client

  return ($html);
  }


function prg($exit=false, $alt=false, $noecho=false)
  {
  if ($alt === false) {$out = $GLOBALS;}
  else {$out = $alt;}
  
  ob_start();
  echo "<pre class=\"wrap\">";
  if (is_object($out))
    {var_dump($out);}
  else
    {print_r ($out);}
  echo "</pre>";
  $out = ob_get_contents();
  ob_end_clean(); // Don't send output to client
  
  if (!$noecho) {echo $out;}
    
  if ($exit) {exit;}
  else {return ($out);}
  }


function cleanNewlines ($str)  
  {
  $str = preg_replace('/\r\n|\r/', '\n', $str);
  $str = preg_replace('/\t/', ' ', $str);

  return ($str);
  }
  
  
function getsslfile ($uri, $decode=true)
  {
  $arrContextOptions=array(
    "ssl"=>array(
        "verify_peer"=>false,
        "verify_peer_name"=>false,),);  

  $response = @file_get_contents($uri, false, stream_context_create($arrContextOptions));
  
  if ($decode)
    {return (json_decode($response, true));}
  else
    {return ($response);}
  }
  
function getRemotePage ($uri)
  {
  // Initialize a connection with cURL (ch = cURL handle, or "channel")
$ch = curl_init();

// Set the URL
curl_setopt($ch, CURLOPT_URL, $uri);

// Set the HTTP method
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');

// Return the response instead of printing it out
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

// Send the request and store the result in $response
$response = curl_exec($ch);

//echo 'HTTP Status Code: ' . curl_getinfo($ch, CURLINFO_HTTP_CODE) . PHP_EOL;
//echo 'Response Body: ' . $response . PHP_EOL;

// Close cURL resource to free up system resources
curl_close($ch);  
    
  return ($response);
  }
  
function getRemoteURL ($url)
  {
  $bits = explode("/", $url);
  $b1 = array_shift($bits);
  
  foreach ($bits as $k => $v)
    {$bits[$k] = rawurlencode($v);}
    
  $url = $b1."/".implode("/", $bits);
  
  $fc = file_get_contents($url);  
  
  return ($fc);
  }
  
function getRemoteJsonDetails ($uri, $format=false, $decode=false)
  {
  
  
  if ($format) {$uri = $uri.".".$format;}
   $fc = file_get_contents($uri);
   if ($decode)
    {$output = json_decode($fc, true);}
   else
    {$output = $fc;}
   return ($output);}
   
function check_string($my_string)
  {
  // Excluded: ";", '#59;' - "#", '#35;' - "@", '#64;' - ":", '#58;' - "/", '#47;'
  $chars = array('!', '*', '"', "'", "(", ")", "&", "=", "+", 
    "$", ",", "?", "%", "[", "]", "\\", "<", ">");
  $codes = array('#33;', '#42;', '#34;', '#39;', '#40;', '#41;', '#38;', '#61;',
    '#43;', '#36;', '#44;', '#63;', '#37;', '#91;', '#93;', 
    '#92;', '&#60;', '&#62;');
  
  $my_string = trim ($my_string);
  
  if (preg_match('/^.*[^a-zA-Z0-9 |_#-].*$/', $my_string))
    {$my_string = trim ($my_string, '"');
     $my_string = '"'.str_replace($chars, $codes, $my_string).'"';}
    
  return ($my_string);
  }
  
function getModelImage ($code)
  {  
  $live_img_link = 'https://mermaid.ink/img/pako:'.$code.'?type=png';
  //echo $live_img_link;
  //exit;
  $im = imagecreatefrompng($live_img_link);
  imageAlphaBlending($im, true);
  imageSaveAlpha($im, true);
  
  header('Content-Type: image/png');
  imagepng($im);
  imagedestroy($im);
  exit;  
  }

function array_update($default, $custom) {

  foreach ($custom as $key => $value) {
    
    if (is_array($value)) {
      $default[$key] = array_update($default[$key], $value);
    } else {
      $default[$key] = $value; 
    }

  }

  return $default;

}
?>
