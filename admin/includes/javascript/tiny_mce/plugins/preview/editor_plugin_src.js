/**
 * $RCSfile: editor_plugin_src.js,v $
 * $Revision: 1189 $
 * $Date: 2006/02/10 16:29:39 $
 *
 * @author Moxiecode
 * @copyright Copyright � 2004-2006, Moxiecode Systems AB, All rights reserved.
 */

/* Import plugin specific language pack */
tinyMCE.importPluginLanguagePack('preview', 'en,tr,cs,de,el,fr_ca,it,ko,pt,sv,zh_cn,fa,fr,pl,pt_br,nl,da,he,nb,hu,ru,ru_KOI8-R,ru_UTF-8,nn,es,cy,is,zh_tw,zh_tw_utf8,sk');

var TinyMCE_PreviewPlugin = {
	getInfo : function() {
		return {
			longname : 'Preview',
			author : 'Moxiecode Systems',
			authorurl : 'http://tinymce.moxiecode.com',
			infourl : 'http://tinymce.moxiecode.com/tinymce/docs/plugin_preview.html',
			version : tinyMCE.majorVersion + "." + tinyMCE.minorVersion
		};
	},

	/**
	 * Returns the HTML contents of the preview control.
	 */
	getControlHTML : function(cn) {
		switch (cn) {
			case "preview":
				return tinyMCE.getButtonHTML(cn, 'lang_preview_desc', '{$pluginurl}/images/preview.gif', 'mcePreview');
		}

		return "";
	},

	/**
	 * Executes the mcePreview command.
	 */
	execCommand : function(editor_id, element, command, user_interface, value) {
		// Handle commands
		switch (command) {
			case "mcePreview":
				var previewPage = tinyMCE.getParam("plugin_preview_pageurl", null);
				var previewWidth = tinyMCE.getParam("plugin_preview_width", "550");
				var previewHeight = tinyMCE.getParam("plugin_preview_height", "600");

				// Use a custom preview page
				if (previewPage) {
					var template = new Array();

					template['file'] = previewPage;
					template['width'] = previewWidth;
					template['height'] = previewHeight;

					tinyMCE.openWindow(template, {editor_id : editor_id, resizable : "yes", scrollbars : "yes", inline : "yes", content : tinyMCE.getContent(), content_css : tinyMCE.getParam("content_css")});
				} else {
					var win = window.open("", "mcePreview", "menubar=no,toolbar=no,scrollbars=yes,resizable=yes,left=20,top=20,width=" + previewWidth + ",height="  + previewHeight);
					var html = "";
					var c = tinyMCE.getContent();
					var pos = c.indexOf('<body'), pos2;

					if (pos != -1) {
						pos = c.indexOf('>', pos);
						pos2 = c.lastIndexOf('</body>');
						c = c.substring(pos + 1, pos2);
					}

					html += tinyMCE.getParam('doctype');
					html += '<html xmlns="http://www.w3.org/1999/xhtml">';
					html += '<head>';
					html += '<title>' + tinyMCE.getLang('lang_preview_desc') + '</title>';
					html += '<base href="' + tinyMCE.settings['base_href'] + '" />';
					html += '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />';
					html += '<link href="' + tinyMCE.getParam("content_css") + '" rel="stylesheet" type="text/css" />';
					html += '</head>';
					html += '<body>';
					html += c;
					html += '</body>';
					html += '</html>';

					win.document.write(html);
					win.document.close();
				}

				return true;
		}

		return false;
	}
};

tinyMCE.addPlugin("preview", TinyMCE_PreviewPlugin);
