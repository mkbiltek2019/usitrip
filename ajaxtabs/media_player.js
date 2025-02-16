
// ********************************************
//   Default Values:

// If you want your viewers to have to click the play button even after they clicked the image set autoStart =false, otherwise let the movies autostart. 
var autoStart = true;

// Choose when the script should build out the players.
//   buildPlayers = "onClick";   will build out a player when the user clicks a link to the media file.  
//   buildPlayers = "onLoad";   will build out a player for all media links as the page loads. (Note you should set autoStart = false if you use this option)
var buildPlayers = "onClick";  // set to "onClick" or "onLoad"

// if you want this script to only take action on links that have rel=enclosure, set requireRelEnclosure = true, otherwise leave it at false and the script will give a player to all supported media links.
var requireRelEnclosure = false;

// to size the player based on the size of the still image set the next line to true.  To always set players to a specific size set it to false
 var useImageSize = true;

// If the above is true, set the next to lines to the default size for your players.
 var videoHeight = 240;
 var videoWidth = 320;

// displayHelpText allows you to toggle the text message that appears beneath the media players
// this is the text that says something like "The media file should begin automatically.  If you are having problems playing the media file, you can download it here" 
 var displayHelpText = true;
 
 var debug = false;
// ********************************************

function addEvent( obj, type, fn ) {
  if ( obj.attachEvent ) {
     obj['e'+type+fn] = fn;
     obj[type+fn] = function(){obj['e'+type+fn]( window.event );}
     obj.attachEvent( 'on'+type, obj[type+fn] );
   } else
     obj.addEventListener( type, fn, false );
 }
 function removeEvent( obj, type, fn ) {
   if ( obj.detachEvent ) {
     obj.detachEvent( 'on'+type, obj[type+fn] );
     obj[type+fn] = null;
   } else
     obj.removeEventListener( type, fn, false );
 }

function mediaLink() {
  if (debug) {alert('looking for links...'); }
  links = document.getElementsByTagName('a');
  for (i=0;i<links.length;i++) {
	
  if (debug) {alert('Found Link!\nhref='+links[i].href); }
	
    if ( (!requireRelEnclosure) || ((requireRelEnclosure) && (links[i].rel == 'enclosure')) ) {
    if (buildPlayers == "onClick" ) {
  	   links[i].onclick=function() {	
        return showplayer(this);
       } 
		}
		else if (buildPlayers == "onLoad" ) {
		    showplayer(links[i]);
		}
		
    }
		
	if (debug) {alert('Setting new event function:\n\n' + links[i].onclick);}
  }
}

function showplayer(linkobject)
{
			
 if (debug) {alert('in showplayer function!\nrel='+linkobject.href); }

file_url = linkobject.href;

function file_name_only(str) { 
  if (str.match(/\?/)) {
	 endpos = Math.min(str.lastIndexOf('?'), str.length);	
	}
	else {
	 endpos = str.length;
	}
	//endpos = Math.max(str.lastIndexOf('?'), str.length);
  var slash = '/'
  if (str.match(/\\/)) {
	  slash = '\\'
  }  
  return str.substring(str.lastIndexOf(slash) + 1, endpos)
}

function file_ext_only(str) {
	return str.substring(str.lastIndexOf('.') + 1, str.length)
}

base_name = file_name_only(file_url); 
file_type = file_ext_only(base_name);

if (debug) { alert ('basename = ' + base_name + '\nfile type = '+file_type); }

if ((useImageSize) && (linkobject.firstChild.nodeName.toLowerCase() == "img")) {
  videoHeight = linkobject.firstChild.height;
  videoWidth = linkobject.firstChild.width;
 if (debug) { alert ('image found!'); }
}
 if (debug) { alert ('width = ' + videoWidth + '\nheight = ' + videoHeight); }
 
var div = document.createElement("div");
div.setAttribute("class", "media-window");
var object = document.createElement("object");
var embed = document.createElement("embed");
file_type_name = "media file";
switch (file_type)
{
case 'm4a':
case 'audio/mp4':
		  if (debug) { alert ("Running Audio branch for:\n"+file_url); }			
			object.setAttribute("width", videoWidth);
			object.setAttribute("height", "50");
			
			var param0 = document.createElement("param");
			param0.setAttribute("name", "src");
			param0.setAttribute("value", file_url);
			var param1 = document.createElement("param");
			param1.setAttribute("name", "fileName");
			param1.setAttribute("value", file_url);
			var param2 = document.createElement("param");
			param2.setAttribute("name", "animationatStart");
			param2.setAttribute("value", "true");
			var param3 = document.createElement("param");
			param3.setAttribute("name", "transparentatStart");
			param3.setAttribute("value", "true");
			var param4 = document.createElement("param");
			param4.setAttribute("name", "autoStart");
			param4.setAttribute("value", autoStart);
			var param5 = document.createElement("param");
			param5.setAttribute("name", "showControls");
			param5.setAttribute("value", "true");
			var param6 = document.createElement("param");
			param6.setAttribute("name", "loop");
			param6.setAttribute("value", "false");
			var param7 = document.createElement("param");
			param7.setAttribute("name", "controller");
			param7.setAttribute("value", "true");			
			/*
			var param8 = document.createElement("param");
			param8.setAttribute("name", "content-type");
			param8.setAttribute("value", "audio/mp4");
			*/				
			object.appendChild(param0);
			object.appendChild(param1);
			object.appendChild(param2);
			object.appendChild(param3);
			object.appendChild(param4);
			object.appendChild(param5);
			object.appendChild(param6);
			object.appendChild(param7);
//			object.appendChild(param8);			
			embed = document.createElement("embed");
			embed.setAttribute("src", file_url);
			embed.setAttribute("loop", "false");
		//	embed.setAttribute("pluginspage", "http://microsoft.com/windows/mediaplayer/en/download/");
			embed.setAttribute("autostart", autoStart);
			embed.setAttribute("showcontrols", "true");
			embed.setAttribute("controller", "true");
			embed.setAttribute("width", videoWidth);
			embed.setAttribute("height", videoHeight);
//			embed.setAttribute("content-type", "audio/mp4");			
			type = "audio";
	    file_type_name = "audio file";			
     break;
case 'wav':
case 'au':
case 'ogg':
case 'mp3':
case 'm3u':
case 'audio/mpeg':
		  if (debug) { alert ("Running Audio branch for:\n"+file_url); }			
			object.setAttribute("width", videoWidth);
			object.setAttribute("height", "50");
			
			var param0 = document.createElement("param");
			param0.setAttribute("name", "src");
			param0.setAttribute("value", file_url);
			var param1 = document.createElement("param");
			param1.setAttribute("name", "fileName");
			param1.setAttribute("value", file_url);
			var param2 = document.createElement("param");
			param2.setAttribute("name", "animationatStart");
			param2.setAttribute("value", "true");
			var param3 = document.createElement("param");
			param3.setAttribute("name", "transparentatStart");
			param3.setAttribute("value", "true");
			var param4 = document.createElement("param");
			param4.setAttribute("name", "autoStart");
			param4.setAttribute("value", autoStart);
			var param5 = document.createElement("param");
			param5.setAttribute("name", "showControls");
			param5.setAttribute("value", "true");
			var param6 = document.createElement("param");
			param6.setAttribute("name", "loop");
			param6.setAttribute("value", "false");
			var param7 = document.createElement("param");
			param7.setAttribute("name", "controller");
			param7.setAttribute("value", "true");		
			object.appendChild(param0);
			object.appendChild(param1);
			object.appendChild(param2);
			object.appendChild(param3);
			object.appendChild(param4);
			object.appendChild(param5);
			object.appendChild(param6);
			object.appendChild(param7);			
			embed = document.createElement("embed");
			embed.setAttribute("src", file_url);
			embed.setAttribute("loop", "false");
		//	embed.setAttribute("pluginspage", "http://microsoft.com/windows/mediaplayer/en/download/");
			embed.setAttribute("autostart", autoStart);
			embed.setAttribute("showcontrols", "true");
			embed.setAttribute("controller", "true");			
			embed.setAttribute("width", videoWidth);
			embed.setAttribute("height", "50");
			type = "audio";	
	    file_type_name = "audio file";			
  break
case 'wmv':
case 'avi':
case 'video/x-ms-wmv':
		  if (debug) { alert ("Running WMP branch for:\n"+file_url); }			
			object.setAttribute("classid", "CLSID:22d6f312-b0f6-11d0-94ab-0080c74c7e95");
			object.setAttribute("codebase", "http://activex.microsoft.com/activex/controls/mplayer/en/nsmp2inf.cab#Version=5,1,52,701");
			object.setAttribute("standby", "Loading Microsoft Windows Media Player components...");
			object.setAttribute("type", "application/x-oleobject");
			object.setAttribute("width", videoWidth);
			object.setAttribute("height", videoHeight+45);
			var param1 = document.createElement("param");
			param1.setAttribute("name", "fileName");
			param1.setAttribute("value", file_url);
			var param2 = document.createElement("param");
			param2.setAttribute("name", "animationatStart");
			param2.setAttribute("value", "true");
			var param3 = document.createElement("param");
			param3.setAttribute("name", "transparentatStart");
			param3.setAttribute("value", "true");
			var param4 = document.createElement("param");
			param4.setAttribute("name", "autoStart");
			param4.setAttribute("value", autoStart);
			var param5 = document.createElement("param");
			param5.setAttribute("name", "showControls");
			param5.setAttribute("value", "true");
			var param6 = document.createElement("param");
			param6.setAttribute("name", "loop");
			param6.setAttribute("value", "false");		
			object.appendChild(param1);
			object.appendChild(param2);
			object.appendChild(param3);
			object.appendChild(param4);
			object.appendChild(param5);
			object.appendChild(param6);		
			embed = document.createElement("embed");
			embed.setAttribute("src", file_url);
			embed.setAttribute("type", "application/x-mplayer2");
			embed.setAttribute("loop", "false");
			embed.setAttribute("pluginspage", "http://microsoft.com/windows/mediaplayer/en/download/");
			embed.setAttribute("autostart", autoStart);
			embed.setAttribute("showcontrols", "true");		
			embed.setAttribute("width", videoWidth);
			embed.setAttribute("height", videoHeight+45);
			type = "video";		
 	   file_type_name = "Windows Media File";
  break
case 'mov':
case 'm4v':
case 'mp4':
case 'video/quicktime':
		  if (debug) { alert ("Running quicktime branch for:\n"+file_url); }	
			object.setAttribute("classid", "clsid:02BF25D5-8C17-4B23-BC80-D3488ABDDC6B");
			object.setAttribute("codebase", "http://www.apple.com/qtactivex/qtplugin.cab");
			object.setAttribute("width", videoWidth);
			object.setAttribute("height", videoHeight+15);

			var param1 = document.createElement("param");
			param1.setAttribute("name", "src");
			param1.setAttribute("value", file_url);
			var param2 = document.createElement("param");
			param2.setAttribute("name", "autoplay");
			param2.setAttribute("value", autoStart);
			var param3 = document.createElement("param");
			param3.setAttribute("name", "controller");
			param3.setAttribute("value", "true");			
			object.appendChild(param1);
			object.appendChild(param2);
			object.appendChild(param3);
			
			embed = document.createElement("embed");
			embed.setAttribute("src", file_url);
			embed.setAttribute("autoplay", autoStart);
			embed.setAttribute("controller", "true");
			embed.setAttribute("pluginspage", "http://www.apple.com/quicktime/download/");
			embed.setAttribute("width", videoWidth);
			embed.setAttribute("height", videoHeight+15);

			type = "video";
	    file_type_name = "QuickTime movie";
  break
default:
  file_type_name = "Unknown";
	type = "Unknown";
  error_message = "No embed code was found for this file type.  Please download the requested media file <a href=\"+file_url+\">here</a>";
  return true;
  break
} 
			
	smalldiv = document.createElement("div");
	b  = document.createElement("b");
	i  = document.createElement("i");
	br  = document.createElement("br");
	br_two  = document.createElement("br");
	
	manualdownloadlink = 	document.createElement("a");
  manualdownloadlink.setAttribute("href", file_url);
  file_type_text = document.createTextNode(file_type_name);
  manualdownloadlink.appendChild(file_type_text);
	
	autostart_text = document.createTextNode("The "+file_type_name+" should begin automatically.");
	nonautostart_text = document.createTextNode("Click the play button when you are ready.");
  trouble_text = document.createTextNode("If you are having problems playing the "+type+", you can download it here: ");

  smalldiv.appendChild(b);
	if (autoStart) {
   b.appendChild(autostart_text);
	}
	else {
   b.appendChild(nonautostart_text);
	}
  smalldiv.appendChild(br);
  smalldiv.appendChild(i);
  smalldiv.appendChild(br_two);
  i.appendChild(trouble_text);
	smalldiv.appendChild(manualdownloadlink);
  smalldiv.setAttribute("className", "small");
	smalldiv.setAttribute("class", "small");	
  manualdownloadlink.setAttribute("className", "medialink");
	manualdownloadlink.setAttribute("class", "medialink");	

 try
  {
      object.appendChild(embed);       
		  div.appendChild(object);
  }
  catch(e)
  {
  div.appendChild(embed);	
  }	
	
	if (displayHelpText) {
  div.appendChild(smalldiv);
  }
	
  linkobject.parentNode.replaceChild(div,linkobject);
	
  if (window.event) event.returnValue = false;
  return false;			

}

addEvent(window, 'load', mediaLink);