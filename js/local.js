$(document).ready(function(){
    $("#clear").click(function(){
        $("#triplesTxt").text("")
    });
});

$(function () {

  $(document).on('keydown', 'textarea.detectTab', function(e) { 
    var keyCode = e.keyCode || e.which; 

    if (keyCode == 9) { 
      e.preventDefault(); 
    // call custom function here
    var start = this.selectionStart;
    var end = this.selectionEnd;

    // set textarea value to: text before caret + tab + text after caret
    $(this).val($(this).val().substring(0, start)
                + "\t"
                + $(this).val().substring(end));

    // put caret at right position again
    this.selectionStart =
    this.selectionEnd = start + 1;    
    } 
  });
});

var panZoom;

function modelZoom ()
    {
    console.log("modelZoom"); 
    console.log($('zoom-toggle'));   
    
    if (typeof panZoom !== 'undefined') {
      // Variable is defined
      // Your code here
      panZoom.destroy();
      panZoom = undefined;
      $(window).off('resize'); 
      $('#zoom-toggle').prop('checked', false);
      } 
    else {
      // Variable is not defined
      // Your code here
      var md = $( "svg" ).first();      
      $(md).width("100%");
      $(md).height("100%");
      
      // Check if max-width is set
      if ($(md).css('max-width')) {
	// Remove the max-width
	$(md).css('max-width', '');
	}
    
      // Expose to window namespase for testing purposes
      panZoom = svgPanZoom('#' + $(md).attr('id'), {
        zoomEnabled: true,
        minZoom: 0.1,
        maxZoom: 75,
        controlIconsEnabled: true,
        fit: true,
        center: true
        });
        
      $('#zoom-toggle').prop('checked', true);
      
      $(window).resize(function(){
        modelResize ();
        });    
      
        }    
    }
    
function modelResize ()
  {
  console.log("modelResize");
  panZoom.resize();
  panZoom.fit();
  panZoom.center();  
  }
    
function togglefullscreen (b, divID)
  {
  var src = $('#'+b).children('img')[0].src;
  var filename = src.substring(src.lastIndexOf('/')+1);

  if (filename == "view-fullscreen.png") {
    $('#'+b).html("<img src=\"graphics/view-restore.png\" width=\"26\" />"); }
  else {
    $('#'+b).html("<img src=\"graphics/view-fullscreen.png\" width=\"26\" />");  }
  
  console.log(divID)    
  $('#'+divID).toggleClass('fullscreen');
  
  if($('#'+divID).hasClass('fullscreen')) {
    $('#'+divID).css('position', 'absolute');
  } else {
    $('#'+divID).css('position', 'relative'); 
  }
  
  $(':focus').blur();
  modelResize ();
  }


$(document).ready(function()
  {
  
  console.log ("local stuff");
  ///////////////////////////////////////////////////////////////////////
  // Based on https://bbbootstrap.com/snippets/modal-multiple-tabs-89860645
  ///////////////////////////////////////////////////////////////////////
          
  $(".tabs").click(function(){
    $(".tabs").removeClass("active");
    $(".tabs h6").removeClass("font-weight-bold");
    $(".tabs h6").addClass("text-muted");
    $(this).children("h6").removeClass("text-muted");
    $(this).children("h6").addClass("font-weight-bold");
    $(this).addClass("active");

    current_fs = $(".active");
    next_fs = $(this).attr('id');
    next_fs = "#" + next_fs + "1";

    $("fieldset").removeClass("show");
    $(next_fs).addClass("show");

    current_fs.animate({}, {
      step: function() {
        current_fs.css({
          'display': 'none',
          'position': 'relative'
          });
        next_fs.css({
          'display': 'block'
          });
        }
      });
    });
  //////////////////////////////////////////////////////////////////////
  
  $(document).ready(function() {
        $("#mermaidCode").click(function(e) {
            e.preventDefault(); // Prevent the default link behavior
            
            var htmlContent = $("#modelDivTxt").html(); // Get the HTML content to copy
            var decodedContent = $("<textarea>").html(htmlContent).text(); // Decode HTML entities
            var tempInput = $("<textarea>"); // Create a temporary textarea element
            $("body").append(tempInput);
            tempInput.val(decodedContent).select(); // Set the decoded content and select it
            document.execCommand("copy"); // Copy the selected content to the clipboard
            tempInput.remove(); // Remove the temporary textarea
            //alert("Content copied to clipboard:\n" + decodedContent);
        });
    });
    
  //////////////////////////////////////////////////////////////////////
  // Compressing data and AJAX calls
  //////////////////////////////////////////////////////////////////////
  //console.log("Passed pcode");
  //console.log(pcode);  
  if (pcode) {
    //pcode = "eNp9VMmO4jAQ_RXL5wTFDlnIrSVmORANEkitGeVSxCZ4lNhRYmhoxL-PswINjE9lv1fPr8rLGaeKcRzhDKTWiURmMND8u6oK0Aj9NsOOY3s-7zAtdM4RWiuoe3bNUy2URO9LRJCNlpX6a1ZQDBIyXnDZ08x4YwxBjQAVUGVCouijJGVHL0a2YyHq0KntBLZDbuNRZthhpaHSKCpEzmutJLfAGDlwK62EttAz7UGPOFftNh6110bKlPHUGX3I9kzwkE1RxBo3Tx24Qx61qTPEvk1mD72k_-9lu5XbGKUvWtibG2LvwegURX3HBr_0RcdaKUKHOLQd917Ku9ZMX_TtqQa2cMHNRRPM3MBzp5ngAo5rftQr8ckTHKGZ0wxrQOvm2H_JhQLWoLra8yvG0705_dOCH3jeoAnOlaqNjGXgnpSrbMSnY-o2Vx_pzki3aUN16HwNE2zED7yT3UAtaiN7C-9rHsPxXTC9a0hbyGt-R9jpIl_Ahuf14PyKXkYn3Ut87aJ9gmtVxu0raojUu9tmA9VPLrJdK0Kdr9gPKG9L70VVuQTGhMwazLtPqgXjr1GT-nYUY0k3ti-JvJgj3pfNj_KNCa0qHHVtwbDXanWS6bjQseYCsgqKcbUE-UcpM2_PuZni6IyPODJvhk5oOPMD6rq-H7rEwicceSSczDw3oF7oe0HghhcLf7YCZOI6NJjOaOATn5CZG1qYt57i7gdMldyKDF_-AUBGdZI"
    const d1 = Base64.toUint8Array(pcode);
    const d2 = pako.inflate(d1, { to: 'string' })
    console.log("Pako String");
    console.log(d2);    
    processTriples (d2);    
    }
  else {
    console.log("F1")
    f1(); 
    }
  /*
  fetch('mermaid-config.json')
  .then(response => response.json())
  .then(data => {
    // Use the 'data' variable to work with the JSON content
    console.log("data content");
    console.log(data);
    
    let code = "%%%%{init: " + JSON.stringify(data) + "}%%%%\n" + $("#modelDivTxt").text().trim();
    console.log(code)
    //let compressed = pako.deflate("{code: "+ JSON.stringify(code) + "}", { level: 9 });
    let compressed = pako.deflate(JSON.stringify($("#modelDivTxt").text().trim()), { level: 9 });
    let send = Base64.fromUint8Array(compressed, true) 
    
    let mc = "eNp9VNuO2jAQ_RXLEm8JcpxbydtK9PJAVKRFWrXKyyw2wVViR4nDwiL-vZMb2xaon8bHM2eOxzM-060RkiZ0NjsrrWxCzhkt4biRR_us3mVGkwXrlpPRxkJtv-uVAYGwrVvZgXLb1sqeVvIgC4QzWhjTYJzTWfkEB7jdFeZtu0cO3GMWjDvIPuIVGtX0EW0jUzi-KGH3eLKDouly7G1ZrOBVFs2Y94JgDtqOTFbZQm5MlUKdK40YD52Otf4mVb7vnDgbgK9QjWKsqdYghNI5AmF_PSXkPxA6PR3VNevlMptlekisCS4BVn4xdQmWkB-43DR1l8vhrBdFyMZAM3pjqawymrysiUdcsq7NL0RIChpyWUo9uuF6EoJAQ4CU_Y1I8lZ51eBeXr2ZQzjjgctil3l_2leaKcNz93AkKVUhG2u0dACFHKSzxYdzyD3uic9jH9y9feXeIBVe464yfhMdonETzUkiOjV3FfhTHHc5m-zI9RY3teT_r2Wfyu-E8gclHMVNdngjNCDJWLFJL39QsZ7K45P9yWX-31Thx535g7rd4aAOLSW2mRI4qeeOEXtzjzHYmWgKuYO2wD7P9AVd26rry89CWVOPc0qhteb5pLfTfvBZKshrKKdJoxXon8aUkxNucb7okSau73nzMI6D0A8WzI9ZFDn0hJMUxPOIs2jB4yDwuXdx6HtP4M1jxv3QD4OIhV7IeeBQ2StKh_-m_3YuvwFj9l-Z"
    let d1a = Base64.toUint8Array(mc);
    let d2a = pako.inflate(d1a, { to: 'string' })
    console.log("Pako String");
    console.log(JSON.parse(d2a));
    let mleURL = "https://mermaid-js.github.io/mermaid-live-editor/edit#pako:"+send;
    let mImURL = "https://mermaid.ink/img/pako:"+send;
    let imURL = "./?image="+send;
    console.log (imURL);
    console.log (mImURL);
  
  })
  .catch(error => {
    console.error('Error:', error);
  });*/
  
    
	const bmCompressed = pako.deflate($("#triplesTxt").text().trim(), { level: 9 });
	const bmData = Base64.fromUint8Array(bmCompressed, true);
	const bmURL = "./?data=pako:"+bmData;
	$("#bookmark").attr("href", bmURL);

  console.log ("Code");
  console.log (code);
  const compressed = pako.deflate(code, { level: 9 });
  const send = Base64.fromUint8Array(compressed, true) 
  const mleURL = "https://mermaid-js.github.io/mermaid-live-editor/edit#pako:"+send;
  const imURL = "./?image="+send;
  $("#mermaidLink").attr("href", mleURL);
  $("#downloadLink").attr("href", imURL);
    
});

function resolveAfterTime(x) {
  return new Promise((resolve) => {
    setTimeout(() => {
      resolve(x);
    }, x);
  });
}

async function f1() {
  const x = await resolveAfterTime(1000);
  modelZoom();
}

async function f2(period) {
  const x = await resolveAfterTime(period);
}

function processTriples (triples)
  { 
  console.log("processTriples");
  console.log(triples);
  
  let jscode = tsv2MermaidStr(triples)
  console.log(jscode);

  $("#triplesTxt").html(triples);
  $("#modelDiv").html(jscode);

  const bmCompressed = pako.deflate($("#triplesTxt").text().trim(), { level: 9 });
  const bmData = Base64.fromUint8Array(bmCompressed, true);
  const bmURL = "./?data=pako:"+bmData;
  $("#bookmark").attr("href", bmURL);
  f1();  
  }
  
let start = false;

let customConfig = {};

function tsv2MermaidStr(data) {

  let mermaidStr = "";
  let dataLines = data.split("\n");
  
  let firstLine = dataLines.shift().trim().split(" ");
  if (firstLine[0] === "gantt") {
    
    mermaidStr += "gantt\n dateFormat YYYY-MM-DD\n";
    
    let title = "Mermaid Gantt Chart";
    let margin = 1;
    let first = true;
    
    dataLines.forEach((line, index) => {
      let arr = line2array(line);
      arr = fill(arr, 4);
      console.log(arr)
      
      if (arr && arr[0]) {
	let sno = 0;
	let scode = "";
	  
        if (["start date", "start"].includes(arr[0].toLowerCase())) {
          start = arr[1]; 
        } else if (arr[0].toLowerCase() === "title") {
          title = arr[1];
        } else if (arr[0].toLowerCase() === "margin") {
          margin = arr[1];
          if (margin > 10) margin = 10;
          if (margin < 1) margin = 1;
          
          // Initialize if falsey 
	  customConfig.gantt ??= {};

	  // Set property
	  customConfig.gantt.leftPadding = 50 * margin;
        } else if (arr[0].toLowerCase() === "group") {
          sno = 0;
          scode = sectionCode(arr[1]);
          
          if (first) {
            first = false;
            mermaidStr += " title " + title + "\n";
            mermaidStr += " section " + arr[1] + "\n";
            sno++;
          } else {
            mermaidStr += " section " + arr[1] + "\n";
          }
        } else {
          if (arr[3]) arr[3] = arr[3] + ", ";
          
          mermaidStr += " " + arr[0] + " :" + arr[3] + scode + sno + ", " + dA(arr[1]) + ", " + dA(arr[2]) + "\n";
          sno++;
        }
      }
    });
 
  }
  
  return mermaidStr;
}

function fill(arr, len, fill = false) {
  return arr.concat(Array(Math.max(0, len - arr.length)).fill(fill));
}


function sectionCode(string) {
  return string.replace(/[^A-Za-z0-9]/g, '').toLowerCase();
}

function line2array(line) {
  line = line.trim();
  
  if (/^[/][/].*$/.test(line)) {
    return [];
  } else if (/^.+[\t].+$/.test(line)) {
    return line.split("\t");  
  } else if (/^.+[,].+$/.test(line)) {
    return line.split(",");
  } else {
    return [line];
  }
}

function dA(v) {
  //start -- should be defined as a global variable.
  v = v.toString(); // force v to be a string in case it is a number
  var a = v.match(/^([-]*)([0-9]+)[,.]*([0-9]*)$/);

  if (a) {
    var m = parseInt(a[2], 10);
    var d = a[3] ? parseInt(a[3]) - 1 : 0;
    var date = new Date(start);

    if (a[1]) {
      m = m * -1;
      if (d) {d = (d + 1) * -1;}
      }

    date.setMonth(date.getMonth() + m);
    date.setDate(date.getDate() + d);
      
    var out = date.toISOString().slice(0, 10);
    } 
  else {
    var time = Date.parse(v);
    var date = new Date(time);
    var out = date.toISOString().slice(0, 10);    
    }

  return out;
  }


function dAOLD(v) {
  console.log(start)
  if (/^([-]*[0-9]+)[,.]*([0-9]*)$/.test(v)) {
    let m = parseInt(RegExp.$1);
    let d = RegExp.$2 ? parseInt(RegExp.$2) - 1 : 0;
    console.log(m)
    console.log(d)
    let date = new Date(start); 
    let invert = m < 0 || d < 0;
    if (invert) {
      m = Math.abs(m);
      d = Math.abs(d);
    }
    
    let di = {
      unit: 'month',
      value: m,
      day: d
    };
    di.invert = invert;
    date.set(di); 
    
    return date.toISOString().split('T')[0];
  } else {
    let time = Date.parse(v);
    return new Date(time).toISOString().split('T')[0];
  }
}
