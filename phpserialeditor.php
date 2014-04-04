<?php
/**
 * A serialized PHP value editor.
 *
 * This file helps edit values from a 2be database, which are often
 * stored as serialized PHP.
 *
 * 2be - an Enterprise PHP Application Framework
 * Copyright (C) 2008-2011  Hunter Perrin.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * Hunter can be contacted at hperrin@gmail.com
 *
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hperrin@gmail.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 * @version 1.0
 */

// Set this to true if you don't trust your users. (And you most likely shouldn't!)
$secure_mode = true;

if (!empty($_REQUEST['type'])) {
	try {
		switch ($_REQUEST['type']) {
			case 'serialized':
			default:
				$value = unserialize($_REQUEST['value']);
				header("Content-Type: text/plain");
				switch ($_REQUEST['language']) {
					case 'yaml':
					default:
						$output = Spyc::YAMLDump($value, false, false, true);
						break;
					case 'json':
						$output = json_indent(json_encode($value));
						break;
					case 'php':
						$output = str_replace('stdClass::__set_state(', '(object) (', var_export($value, true));
						break;
				}
				break;
			case 'exported':
				switch ($_REQUEST['language']) {
					case 'yaml':
					default:
						$value = Spyc::YAMLLoadString($_REQUEST['value']);
						break;
					case 'json':
						$value = json_decode($_REQUEST['value'], true);
						break;
					case 'php':
						if ($secure_mode)
							$value = 'I told you, PHP mode is disabled!';
						else
							$value = eval('return '.$_REQUEST['value'].';');
						break;
				}
				header("Content-Type: text/plain");
				$output = serialize($value);
				break;
			case 'favicon':
				header("Content-Type: image/x-icon");
				$output = get_fav_icon();
				break;
			case 'header':
				header("Content-Type: image/png");
				$output = get_header();
				break;
		}
	} catch (Exception $e) {
		$ouput = 'Error: '.$e->getMessage();
	}
	echo $output;
	exit;
}

?>
<!DOCTYPE html>
<html>
	<head>
		<title>Serialized PHP Editor</title>
		<meta charset="UTF-8" />
		<link href="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>?type=favicon" type="image/vnd.microsoft.icon" rel="icon">
		<script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
		<link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap.min.css">
		<script src="//netdna.bootstrapcdn.com/bootstrap/3.1.1/js/bootstrap.min.js"></script>
		<?php if ($secure_mode) { ?>
		<link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/pnotify/1.3.1/jquery.pnotify.default.min.css">
		<script src="//cdnjs.cloudflare.com/ajax/libs/pnotify/1.3.1/jquery.pnotify.min.js"></script>
		<?php } ?>
		<style type="text/css">
			textarea {
				width: 100%;
				border: 1px solid #333;
			}
			#diff_container {
				width: 100%;
				overflow: auto;
				border: 1px solid #333;
			}
			#diff {
				padding: .5em;
				white-space: pre;
				font-size: .8em;
				font-family: monospace;
			}
		</style>
		<script type="text/javascript">
			$(function(){
				var updating = false, original = "";
				var diff = $("#diff"), output = $("#output");
				var serialized = $("#serialized").on("change keyup", function(){
					if (updating)
						return;
					original = serialized.val();
					$.post("", {type: "serialized", "value": original, "language": $("input[name=language]:checked").val()}, function(data){
						updating = true;
						editor.val(data);
						updating = false;
						editor.change();
					});
				});
				$("input[name=language]").on("change", function(){
					serialized.trigger("change");
				})
				var editor = $("#editor").on("change keyup", function(){
					if (updating)
						return;
					$.post("", {type: "exported", "value": editor.val(), "language": $("input[name=language]:checked").val()}, function(data){
						updating = true;
						output.val(data);
						diff.html(pretty_php_serialized(WDiffString(original, data)));
						updating = false;
					});
				});
				serialized.trigger("change");
			});

			function pretty_php_serialized(serialized) {
				serialized = serialized.replace(/<br\/?>/g, "").replace(/&([a-z0-9#]+);/gi, "**ent($1)ent**");
				while (serialized.match(/\{[^\n]/)!==null)
					serialized = serialized.replace(/\{([^\n])/g, "{\n$1");
				while (serialized.match(/\}[^\n]/)!==null)
					serialized = serialized.replace(/\}([^\n])/g, "}\n$1");
				while (serialized.match(/[^\n]\}/)!==null)
					serialized = serialized.replace(/([^\n])\}/g, "$1\n}");
				while (serialized.match(/;[^\n]/)!==null)
					serialized = serialized.replace(/;([^\n])/g, ";\n$1");
				while (serialized.match(/\{\n\}/)!==null)
					serialized = serialized.replace(/\{\n\}/g, "{}");
				var cur_indent = 1;
				var cur_entry_index = false;
				var lines = serialized.split("\n");
				serialized = "";
				for (var i=0; i<lines.length; i++) {
					var is_a_closer = lines[i].charAt(0) == "}";
					if (is_a_closer) {
						cur_indent--;
						serialized += Array(cur_indent).join("  ")+lines[i]+"\n";
					} else {
						if (cur_entry_index)
							serialized += Array(cur_indent).join("  ")+lines[i];
						else
							serialized += lines[i]+"\n";
						cur_entry_index = !cur_entry_index;
					}
					if (lines[i].charAt(lines[i].length-1) == "{")
						cur_indent++;
				}
				serialized = serialized.replace(/\*\*ent\(([a-z0-9#]+)\)ent\*\*/gi, "&$1;");
				return serialized;
			}

			function do_example() {
				var example = 'a:1:{s:13:"533616e6f0a3d";a:4:{s:4:"name";s:4:"Home";s:7:"buttons";a:34:{i:0;a:2:{s:9:"component";s:12:"com_calendar";s:6:"button";s:8:"calendar";}i:1;s:9:"separator";i:2;a:2:{s:9:"component";s:13:"com_configure";s:6:"button";s:6:"config";}i:3;s:9:"separator";i:4;a:2:{s:9:"component";s:11:"com_content";s:6:"button";s:10:"categories";}i:5;a:2:{s:9:"component";s:11:"com_content";s:6:"button";s:5:"pages";}i:6;a:2:{s:9:"component";s:11:"com_content";s:6:"button";s:8:"page_new";}i:7;s:9:"separator";i:8;a:2:{s:9:"component";s:12:"com_customer";s:6:"button";s:9:"customers";}i:9;a:2:{s:9:"component";s:12:"com_customer";s:6:"button";s:12:"customer_new";}i:10;s:9:"separator";i:11;a:2:{s:9:"component";s:12:"com_elfinder";s:6:"button";s:12:"file_manager";}i:12;s:9:"separator";i:13;a:2:{s:9:"component";s:14:"com_menueditor";s:6:"button";s:7:"entries";}i:14;a:2:{s:9:"component";s:14:"com_menueditor";s:6:"button";s:9:"entry_new";}i:15;s:9:"separator";i:16;a:2:{s:9:"component";s:11:"com_modules";s:6:"button";s:7:"modules";}i:17;a:2:{s:9:"component";s:11:"com_modules";s:6:"button";s:10:"module_new";}i:18;s:9:"separator";i:19;a:2:{s:9:"component";s:9:"com_plaza";s:6:"button";s:11:"getsoftware";}i:20;a:2:{s:9:"component";s:9:"com_plaza";s:6:"button";s:9:"installed";}i:21;s:9:"separator";i:22;a:2:{s:9:"component";s:11:"com_reports";s:6:"button";s:8:"rankings";}i:23;s:9:"separator";i:24;a:2:{s:9:"component";s:9:"com_sales";s:6:"button";s:5:"sales";}i:25;a:2:{s:9:"component";s:9:"com_sales";s:6:"button";s:8:"sale_new";}i:26;a:2:{s:9:"component";s:9:"com_sales";s:6:"button";s:11:"countsheets";}i:27;a:2:{s:9:"component";s:9:"com_sales";s:6:"button";s:14:"countsheet_new";}i:28;a:2:{s:9:"component";s:9:"com_sales";s:6:"button";s:7:"receive";}i:29;a:2:{s:9:"component";s:9:"com_sales";s:6:"button";s:7:"pending";}i:30;a:2:{s:9:"component";s:9:"com_sales";s:6:"button";s:9:"shipments";}i:31;s:9:"separator";i:32;a:2:{s:9:"component";s:8:"com_user";s:6:"button";s:10:"my_account";}i:33;a:2:{s:9:"component";s:8:"com_user";s:6:"button";s:6:"logout";}}s:12:"buttons_size";s:5:"large";s:7:"columns";a:3:{s:13:"533616e6f0a46";a:2:{s:4:"size";d:0.25;s:7:"widgets";a:2:{s:13:"533616e6f0975";a:3:{s:9:"component";s:9:"com_about";s:6:"widget";s:8:"newsfeed";s:7:"options";a:0:{}}s:13:"533616e6f0a08";a:3:{s:9:"component";s:11:"com_content";s:6:"widget";s:9:"quickpage";s:7:"options";a:0:{}}}}s:13:"533616e6f0a4e";a:2:{s:4:"size";d:0.333333333333329984160542380777769722044467926025390625;s:7:"widgets";a:2:{s:13:"533616e6f09b6";a:3:{s:9:"component";s:12:"com_calendar";s:6:"widget";s:6:"agenda";s:7:"options";a:0:{}}s:13:"533616e6f0a33";a:3:{s:9:"component";s:7:"com_hrm";s:6:"widget";s:7:"clockin";s:7:"options";a:0:{}}}}s:13:"533616e6f0a53";a:2:{s:4:"size";d:0.416666666666670015839457619222230277955532073974609375;s:7:"widgets";a:1:{s:13:"533616e6f09e0";a:3:{s:9:"component";s:13:"com_configure";s:6:"widget";s:7:"welcome";s:7:"options";a:0:{}}}}}}}';
				$("#serialized").val(example).trigger("change");
			}
			<?php if ($secure_mode) { ?>
			var stack = {"dir1": "up", "dir2": "left", "firstpos1": 25, "firstpos2": 25};
			$.pnotify.defaults.stack = stack;
			$.pnotify.defaults.addclass = 'stack-bottomright';
			$(function(){
				var n = $.pnotify({
					title: "PHP Mode Disabled",
					text: "PHP language mode has been disabled for security reasons.",
					type: "info",
					hide: false,
					history: false,
					styling: "bootstrap3",
					nonblock: true,
					nonblock_opacity: .2
				}).click(function(){n.pnotify_remove()});
			});
			<?php } ?>
		</script>
	</head>
	<body>
		<div class="container">
			<header class="page-header">
				<div style="float: right; position: relative; top: -20px;">
					<a href="http://2be.io" target="_blank">
						<img src="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>?type=header" alt="2be Logo" style="border: none;" />
					</a>
				</div>
				<h1>Serialized PHP Editor</h1>
			</header>
			<div class="form-group">
				<label>Choose a language to use for editing</label>
				<div>
					<div class="btn-group" data-toggle="buttons">
						<label class="btn btn-default active"><input type="radio" name="language" value="yaml" checked="checked" /> YAML</label>
						<label class="btn btn-default"><input type="radio" name="language" value="json" /> JSON</label>
						<label class="btn btn-default<?php if ($secure_mode) { ?> disabled<?php } ?>"><input type="radio" name="language" value="php" <?php if ($secure_mode) { ?>disabled="disabled"<?php } ?> /> PHP</label>
					</div>
				</div>
			</div>
			<div class="row">
				<div class="col-sm-6">
					1. Paste in serialized PHP here: <small>(<a href="javascript:void(0);" onclick="do_example();">example</a>)</small><br />
					<textarea rows="6" cols="30" id="serialized" style="height: 100px;"></textarea>
				</div>
				<div class="col-sm-6">
					3. The new serialized PHP will appear here:<br />
					<textarea rows="6" cols="30" id="output" style="height: 100px;"></textarea>
				</div>
			</div>
			<div class="row">
				<div class="col-sm-6">
					2. Then edit the value here:<br />
					<textarea rows="20" cols="30" id="editor" style="height: 500px;"></textarea>
				</div>
				<div class="col-sm-6">
					4. A colored diff will show here:<br />
					<div id="diff_container" style="height: 500px;">
						<div id="diff"></div>
					</div>
				</div>
			</div>
		</div>
	</body>
<script type="text/javascript">
/*

Name:    wDiff.js
Version: 0.9.9 (October 10, 2010)
Info:    http://en.wikipedia.org/wiki/User:Cacycle/diff
Code:    http://en.wikipedia.org/wiki/User:Cacycle/diff.js

JavaScript diff algorithm by [[en:User:Cacycle]] (http://en.wikipedia.org/wiki/User_talk:Cacycle).
Outputs html/css-formatted new text with highlighted deletions, inserts, and block moves.
For newline highlighting the following style rules have to be added to the document:
	.wDiffParagraph:before { content: "¶"; };

The program uses cross-browser code and should work with all modern browsers. It has been tested with:
* Mozilla Firefox 1.5.0.1
* Mozilla SeaMonkey 1.0
* Opera 8.53
* Internet Explorer 6.0.2900.2180
* Internet Explorer 7.0.5730.11
This program is also compatible with Greasemonkey

An implementation of the word-based algorithm from:

Communications of the ACM 21(4):264 (1978)
http://doi.acm.org/10.1145/359460.359467

With the following additional feature:

* Word types have been optimized for MediaWiki source texts
* Additional post-pass 5 code for resolving islands caused by adding
	two common words at the end of sequences of common words
* Additional detection of block borders and color coding of moved blocks and their original position
* Optional "intelligent" omission of unchanged parts from the output

This code is used by the MediaWiki in-browser text editors [[en:User:Cacycle/editor]] and [[en:User:Cacycle/wikEd]]
and the enhanced diff view tool wikEdDiff [[en:User:Cacycle/wikEd]].

Usage: var htmlText = WDiffString(oldText, newText);

This code has been released into the public domain.

Datastructures (abbreviations from publication):

text: an object that holds all text related datastructures
	.newWords: consecutive words of the new text (N)
	.oldWords: consecutive words of the old text (O)
	.newToOld: array pointing to corresponding word number in old text (NA)
	.oldToNew: array pointing to corresponding word number in new text (OA)
	.message:  output message for testing purposes

symbol table:
	symbols[word]: associative array (object) of detected words for passes 1 - 3, points to symbol[i]
	symbol[i]: array of objects that hold word counters and pointers:
		.newCtr:  new word occurences counter (NC)
		.oldCtr:  old word occurences counter (OC)
		.toNew:   first word occurrence in new text, points to text.newWords[i]
		.toOld:   last word occurrence in old text, points to text.oldWords[i]

block: an object that holds block move information
	blocks indexed after new text:
	.newStart:  new text word number of start of this block
	.newLength: element number of this block including non-words
	.newWords:  true word number of this block
	.newNumber: corresponding block index in old text
	.newBlock:  moved-block-number of a block that has been moved here
	.newLeft:   moved-block-number of a block that has been moved from this border leftwards
	.newRight:  moved-block-number of a block that has been moved from this border rightwards
	.newLeftIndex:  index number of a block that has been moved from this border leftwards
	.newRightIndex: index number of a block that has been moved from this border rightwards
	blocks indexed after old text:
	.oldStart:  word number of start of this block
	.oldToNew:  corresponding new text word number of start
	.oldLength: element number of this block including non-words
	.oldWords:  true word number of this block

*/


// css for change indicators
if (typeof(wDiffStyleDelete) == 'undefined') { window.wDiffStyleDelete = 'font-weight: normal; text-decoration: none; color: #fff; background-color: #990033;'; }
if (typeof(wDiffStyleInsert) == 'undefined') { window.wDiffStyleInsert = 'font-weight: normal; text-decoration: none; color: #fff; background-color: #009933;'; }
if (typeof(wDiffStyleMoved)  == 'undefined') { window.wDiffStyleMoved  = 'font-weight: bold;  color: #000; vertical-align: text-bottom; font-size: xx-small; padding: 0; border: solid 1px;'; }
if (typeof(wDiffStyleBlock)  == 'undefined') { window.wDiffStyleBlock  = [
	'color: #000; background-color: #ffff80;',
	'color: #000; background-color: #c0ffff;',
	'color: #000; background-color: #ffd0f0;',
	'color: #000; background-color: #ffe080;',
	'color: #000; background-color: #aaddff;',
	'color: #000; background-color: #ddaaff;',
	'color: #000; background-color: #ffbbbb;',
	'color: #000; background-color: #d8ffa0;',
	'color: #000; background-color: #d0d0d0;'
]; }

// html for change indicators, {number} is replaced by the block number
// {block} is replaced by the block style, class and html comments are important for shortening the output
if (typeof(wDiffHtmlMovedRight)  == 'undefined') { window.wDiffHtmlMovedRight  = '<input class="wDiffHtmlMovedRight" type="button" value="&gt;" style="' + wDiffStyleMoved + ' {block}"><!--wDiffHtmlMovedRight-->'; }
if (typeof(wDiffHtmlMovedLeft)   == 'undefined') { window.wDiffHtmlMovedLeft   = '<input class="wDiffHtmlMovedLeft" type="button" value="&lt;" style="' + wDiffStyleMoved + ' {block}"><!--wDiffHtmlMovedLeft-->'; }

if (typeof(wDiffHtmlBlockStart)  == 'undefined') { window.wDiffHtmlBlockStart  = '<span class="wDiffHtmlBlock" style="{block}">'; }
if (typeof(wDiffHtmlBlockEnd)    == 'undefined') { window.wDiffHtmlBlockEnd    = '</span><!--wDiffHtmlBlock-->'; }

if (typeof(wDiffHtmlDeleteStart) == 'undefined') { window.wDiffHtmlDeleteStart = '<span class="wDiffHtmlDelete" style="' + wDiffStyleDelete + '">'; }
if (typeof(wDiffHtmlDeleteEnd)   == 'undefined') { window.wDiffHtmlDeleteEnd   = '</span><!--wDiffHtmlDelete-->'; }

if (typeof(wDiffHtmlInsertStart) == 'undefined') { window.wDiffHtmlInsertStart = '<span class="wDiffHtmlInsert" style="' + wDiffStyleInsert + '">'; }
if (typeof(wDiffHtmlInsertEnd)   == 'undefined') { window.wDiffHtmlInsertEnd   = '</span><!--wDiffHtmlInsert-->'; }

// minimal number of real words for a moved block (0 for always displaying block move indicators)
if (typeof(wDiffBlockMinLength) == 'undefined') { window.wDiffBlockMinLength = 3; }

// exclude identical sequence starts and endings from change marking
if (typeof(wDiffWordDiff) == 'undefined') { window.wDiffWordDiff = true; }

// enable recursive diff to resolve problematic sequences
if (typeof(wDiffRecursiveDiff) == 'undefined') { window.wDiffRecursiveDiff = true; }

// enable block move display
if (typeof(wDiffShowBlockMoves) == 'undefined') { window.wDiffShowBlockMoves = true; }

// remove unchanged parts from final output

// characters before diff tag to search for previous heading, paragraph, line break, cut characters
if (typeof(wDiffHeadingBefore)   == 'undefined') { window.wDiffHeadingBefore   = 1500; }
if (typeof(wDiffParagraphBefore) == 'undefined') { window.wDiffParagraphBefore = 1500; }
if (typeof(wDiffLineBeforeMax)   == 'undefined') { window.wDiffLineBeforeMax   = 1000; }
if (typeof(wDiffLineBeforeMin)   == 'undefined') { window.wDiffLineBeforeMin   =  500; }
if (typeof(wDiffBlankBeforeMax)  == 'undefined') { window.wDiffBlankBeforeMax  = 1000; }
if (typeof(wDiffBlankBeforeMin)  == 'undefined') { window.wDiffBlankBeforeMin  =  500; }
if (typeof(wDiffCharsBefore)     == 'undefined') { window.wDiffCharsBefore     =  500; }

// characters after diff tag to search for next heading, paragraph, line break, or characters
if (typeof(wDiffHeadingAfter)   == 'undefined') { window.wDiffHeadingAfter   = 1500; }
if (typeof(wDiffParagraphAfter) == 'undefined') { window.wDiffParagraphAfter = 1500; }
if (typeof(wDiffLineAfterMax)   == 'undefined') { window.wDiffLineAfterMax   = 1000; }
if (typeof(wDiffLineAfterMin)   == 'undefined') { window.wDiffLineAfterMin   =  500; }
if (typeof(wDiffBlankAfterMax)  == 'undefined') { window.wDiffBlankAfterMax  = 1000; }
if (typeof(wDiffBlankAfterMin)  == 'undefined') { window.wDiffBlankAfterMin  =  500; }
if (typeof(wDiffCharsAfter)     == 'undefined') { window.wDiffCharsAfter     =  500; }

// maximal fragment distance to join close fragments
if (typeof(wDiffFragmentJoin)  == 'undefined') { window.wDiffFragmentJoin = 1000; }
if (typeof(wDiffOmittedChars)  == 'undefined') { window.wDiffOmittedChars = '…'; }
if (typeof(wDiffOmittedLines)  == 'undefined') { window.wDiffOmittedLines = '<hr style="height: 2px; margin: 1em 10%;">'; }
if (typeof(wDiffNoChange)      == 'undefined') { window.wDiffNoChange     = '<hr style="height: 2px; margin: 1em 20%;">'; }

// compatibility fix for old name of main function
window.StringDiff = window.WDiffString;


// WDiffString: main program
// input: oldText, newText, strings containing the texts
// returns: html diff

window.WDiffString = function(oldText, newText) {

// IE / Mac fix
	oldText = oldText.replace(/\r\n?/g, '\n');
	newText = newText.replace(/\r\n?/g, '\n');

	var text = {};
	text.newWords = [];
	text.oldWords = [];
	text.newToOld = [];
	text.oldToNew = [];
	text.message = '';
	var block = {};
	var outText = '';

// trap trivial changes: no change
	if (oldText == newText) {
		outText = newText;
		outText = WDiffEscape(outText);
		outText = WDiffHtmlFormat(outText);
		return(outText);
	}

// trap trivial changes: old text deleted
	if ( (oldText == null) || (oldText.length == 0) ) {
		outText = newText;
		outText = WDiffEscape(outText);
		outText = WDiffHtmlFormat(outText);
		outText = wDiffHtmlInsertStart + outText + wDiffHtmlInsertEnd;
		return(outText);
	}

// trap trivial changes: new text deleted
	if ( (newText == null) || (newText.length == 0) ) {
		outText = oldText;
		outText = WDiffEscape(outText);
		outText = WDiffHtmlFormat(outText);
		outText = wDiffHtmlDeleteStart + outText + wDiffHtmlDeleteEnd;
		return(outText);
	}

// split new and old text into words
	WDiffSplitText(oldText, newText, text);

// calculate diff information
	WDiffText(text);

//detect block borders and moved blocks
	WDiffDetectBlocks(text, block);

// process diff data into formatted html text
	outText = WDiffToHtml(text, block);

// IE fix
	outText = outText.replace(/> ( *)</g, '>&nbsp;$1<');

	return(outText);
};


// WDiffSplitText: split new and old text into words
// input: oldText, newText, strings containing the texts
// changes: text.newWords and text.oldWords, arrays containing the texts in arrays of words

window.WDiffSplitText = function(oldText, newText, text) {

// convert strange spaces
	oldText = oldText.replace(/[\t\u000b\u00a0\u2028\u2029]+/g, ' ');
	newText = newText.replace(/[\t\u000b\u00a0\u2028\u2029]+/g, ' ');

// split old text into words

//              /     |    |    |    |    |   |  |     |   |  |  |    |    |    | /
	var pattern = /[\w]+|\[\[|\]\]|\{\{|\}\}|\n+| +|&\w+;|'''|''|=+|\{\||\|\}|\|\-|./g;
	var result;
	do {
		result = pattern.exec(oldText);
		if (result != null) {
			text.oldWords.push(result[0]);
		}
	} while (result != null);

// split new text into words
	do {
		result = pattern.exec(newText);
		if (result != null) {
			text.newWords.push(result[0]);
		}
	} while (result != null);

	return;
};


// WDiffText: calculate diff information
// input: text.newWords and text.oldWords, arrays containing the texts as arrays of words
// optionally for recursive calls: newStart, newEnd, oldStart, oldEnd, recursionLevel
// changes: text.newToOld and text.oldToNew, arrays pointing to corresponding words

window.WDiffText = function(text, newStart, newEnd, oldStart, oldEnd, recursionLevel) {

	var symbol = [];
	var symbols = {};

// set defaults
	if (typeof(newStart) == 'undefined') { newStart = 0; }
	if (typeof(newEnd) == 'undefined') { newEnd = text.newWords.length; }
	if (typeof(oldStart) == 'undefined') { oldStart = 0; }
	if (typeof(oldEnd) == 'undefined') { oldEnd = text.oldWords.length; }
	if (typeof(recursionLevel) == 'undefined') { recursionLevel = 0; }

// limit recursion depth
	if (recursionLevel > 10) {
		return;
	}

//
// pass 1: Parse new text into symbol table
//
	for (var i = newStart; i < newEnd; i ++) {
		var word = text.newWords[i];

// preserve the native method
		if (word.indexOf('hasOwnProperty') == 0) {
			word = word.replace(/^(hasOwnProperty_*)$/, '$1_');
		}

// add new entry to symbol table
		if (symbols.hasOwnProperty(word) == false) {
			var last = symbol.length;
			symbols[word] = last;
			symbol[last] = { newCtr: 1, oldCtr: 0, toNew: i, toOld: null };
		}

// or update existing entry
		else {

// increment word counter for new text
			var hashToArray = symbols[word];
			symbol[hashToArray].newCtr ++;
		}
	}

//
// pass 2: parse old text into symbol table
//
	for (var i = oldStart; i < oldEnd; i ++) {
		var word = text.oldWords[i];

// preserve the native method
		if (word.indexOf('hasOwnProperty') == 0) {
			word = word.replace(/^(hasOwnProperty_*)$/, '$1_');
		}

// add new entry to symbol table
		if (symbols.hasOwnProperty(word) == false) {
			var last = symbol.length;
			symbols[word] = last;
			symbol[last] = { newCtr: 0, oldCtr: 1, toNew: null, toOld: i };
		}

// or update existing entry
		else {

// increment word counter for old text
			var hashToArray = symbols[word];
			symbol[hashToArray].oldCtr ++;

// add word number for old text
			symbol[hashToArray].toOld = i;
		}
	}

//
// pass 3: connect unique words
//
	for (var i = 0; i < symbol.length; i ++) {

// find words in the symbol table that occur only once in both versions
		if ( (symbol[i].newCtr == 1) && (symbol[i].oldCtr == 1) ) {
			var toNew = symbol[i].toNew;
			var toOld = symbol[i].toOld;

// do not use spaces as unique markers
			if (/^\s+$/.test(text.newWords[toNew]) == false) {

// connect from new to old and from old to new
				text.newToOld[toNew] = toOld;
				text.oldToNew[toOld] = toNew;
			}
		}
	}

//
// pass 4: connect adjacent identical words downwards
//
	for (var i = newStart; i < newEnd - 1; i ++) {

// find already connected pairs
		if (text.newToOld[i] != null) {
			var j = text.newToOld[i];

// check if the following words are not yet connected
			if ( (text.newToOld[i + 1] == null) && (text.oldToNew[j + 1] == null) ) {

// connect if the following words are the same
				if (text.newWords[i + 1] == text.oldWords[j + 1]) {
					text.newToOld[i + 1] = j + 1;
					text.oldToNew[j + 1] = i + 1;
				}
			}
		}
	}

//
// pass 5: connect adjacent identical words upwards
//
	for (var i = newEnd - 1; i > newStart; i --) {

// find already connected pairs
		if (text.newToOld[i] != null) {
			var j = text.newToOld[i];

// check if the preceeding words are not yet connected
			if ( (text.newToOld[i - 1] == null) && (text.oldToNew[j - 1] == null) ) {

// connect if the preceeding words are the same
				if ( text.newWords[i - 1] == text.oldWords[j - 1] ) {
					text.newToOld[i - 1] = j - 1;
					text.oldToNew[j - 1] = i - 1;
				}
			}
		}
	}

//
// "pass" 6: recursively diff still unresolved regions downwards
//
	if (wDiffRecursiveDiff == true) {
		var i = newStart;
		var j = oldStart;
		while (i < newEnd) {
			if (text.newToOld[i - 1] != null) {
				j = text.newToOld[i - 1] + 1;
			}

// check for the start of an unresolved sequence
			if ( (text.newToOld[i] == null) && (text.oldToNew[j] == null) ) {

// determine the ends of the sequences
				var iStart = i;
				var iEnd = i;
				while ( (text.newToOld[iEnd] == null) && (iEnd < newEnd) ) {
					iEnd ++;
				}
				var iLength = iEnd - iStart;

				var jStart = j;
				var jEnd = j;
				while ( (text.oldToNew[jEnd] == null) && (jEnd < oldEnd) ) {
					jEnd ++;
				}
				var jLength = jEnd - jStart;

// recursively diff the unresolved sequence
				if ( (iLength > 0) && (jLength > 0) ) {
					if ( (iLength > 1) || (jLength > 1) ) {
						if ( (iStart != newStart) || (iEnd != newEnd) || (jStart != oldStart) || (jEnd != oldEnd) ) {
							WDiffText(text, iStart, iEnd, jStart, jEnd, recursionLevel + 1);
						}
					}
				}
				i = iEnd;
			}
			else {
				i ++;
			}
		}
	}

//
// "pass" 7: recursively diff still unresolved regions upwards
//
	if (wDiffRecursiveDiff == true) {
		var i = newEnd - 1;
		var j = oldEnd - 1;
		while (i >= newStart) {
			if (text.newToOld[i + 1] != null) {
				j = text.newToOld[i + 1] - 1;
			}

// check for the start of an unresolved sequence
			if ( (text.newToOld[i] == null) && (text.oldToNew[j] == null) ) {

// determine the ends of the sequences
				var iStart = i;
				var iEnd = i + 1;
				while ( (text.newToOld[iStart - 1] == null) && (iStart >= newStart) ) {
					iStart --;
				}
				if (iStart < 0) {
					iStart = 0;
				}
				var iLength = iEnd - iStart;

				var jStart = j;
				var jEnd = j + 1;
				while ( (text.oldToNew[jStart - 1] == null) && (jStart >= oldStart) ) {
					jStart --;
				}
				if (jStart < 0) {
					jStart = 0;
				}
				var jLength = jEnd - jStart;

// recursively diff the unresolved sequence
				if ( (iLength > 0) && (jLength > 0) ) {
					if ( (iLength > 1) || (jLength > 1) ) {
						if ( (iStart != newStart) || (iEnd != newEnd) || (jStart != oldStart) || (jEnd != oldEnd) ) {
							WDiffText(text, iStart, iEnd, jStart, jEnd, recursionLevel + 1);
						}
					}
				}
				i = iStart - 1;
			}
			else {
				i --;
			}
		}
	}
	return;
};


// WDiffToHtml: process diff data into formatted html text
// input: text.newWords and text.oldWords, arrays containing the texts in arrays of words
//   text.newToOld and text.oldToNew, arrays pointing to corresponding words
//   block data structure
// returns: outText, a html string

window.WDiffToHtml = function(text, block) {

	var outText = text.message;

	var blockNumber = 0;
	var i = 0;
	var j = 0;
	var movedAsInsertion;

// cycle through the new text
	do {
		var movedIndex = [];
		var movedBlock = [];
		var movedLeft = [];
		var blockText = '';
		var identText = '';
		var delText = '';
		var insText = '';
		var identStart = '';

// check if a block ends here and finish previous block
		if (movedAsInsertion != null) {
			if (movedAsInsertion == false) {
				identStart += wDiffHtmlBlockEnd;
			}
			else {
				identStart += wDiffHtmlInsertEnd;
			}
			movedAsInsertion = null;
		}

// detect block boundary
		if ( (text.newToOld[i] != j) || (blockNumber == 0 ) ) {
			if ( ( (text.newToOld[i] != null) || (i >= text.newWords.length) ) && ( (text.oldToNew[j] != null) || (j >= text.oldWords.length) ) ) {

// block moved right
				var moved = block.newRight[blockNumber];
				if (moved > 0) {
					var index = block.newRightIndex[blockNumber];
					movedIndex.push(index);
					movedBlock.push(moved);
					movedLeft.push(false);
				}

// block moved left
				moved = block.newLeft[blockNumber];
				if (moved > 0) {
					var index = block.newLeftIndex[blockNumber];
					movedIndex.push(index);
					movedBlock.push(moved);
					movedLeft.push(true);
				}

// check if a block starts here
				moved = block.newBlock[blockNumber];
				if (moved > 0) {

// mark block as inserted text
					if (block.newWords[blockNumber] < wDiffBlockMinLength) {
						identStart += wDiffHtmlInsertStart;
						movedAsInsertion = true;
					}

// mark block by color
					else {
						if (moved > wDiffStyleBlock.length) {
							moved = wDiffStyleBlock.length;
						}
						identStart += WDiffHtmlCustomize(wDiffHtmlBlockStart, moved - 1);
						movedAsInsertion = false;
					}
				}

				if (i >= text.newWords.length) {
					i ++;
				}
				else {
					j = text.newToOld[i];
					blockNumber ++;
				}
			}
		}

// get the correct order if moved to the left as well as to the right from here
		if (movedIndex.length == 2) {
			if (movedIndex[0] > movedIndex[1]) {
				movedIndex.reverse();
				movedBlock.reverse();
				movedLeft.reverse();
			}
		}

// handle left and right block moves from this position
		for (var m = 0; m < movedIndex.length; m ++) {

// insert the block as deleted text
			if (block.newWords[ movedIndex[m] ] < wDiffBlockMinLength) {
				var movedStart = block.newStart[ movedIndex[m] ];
				var movedLength = block.newLength[ movedIndex[m] ];
				var str = '';
				for (var n = movedStart; n < movedStart + movedLength; n ++) {
					str += text.newWords[n];
				}
				str = WDiffEscape(str);
				str = str.replace(/\n/g, '<span class="wDiffParagraph"></span><br>');
				blockText += wDiffHtmlDeleteStart + str + wDiffHtmlDeleteEnd;
			}

// add a placeholder / move direction indicator
			else {
				if (movedBlock[m] > wDiffStyleBlock.length) {
					movedBlock[m] = wDiffStyleBlock.length;
				}
				if (movedLeft[m]) {
					blockText += WDiffHtmlCustomize(wDiffHtmlMovedLeft, movedBlock[m] - 1);
				}
				else {
					blockText += WDiffHtmlCustomize(wDiffHtmlMovedRight, movedBlock[m] - 1);
				}
			}
		}

// collect consecutive identical text
		while ( (i < text.newWords.length) && (j < text.oldWords.length) ) {
			if ( (text.newToOld[i] == null) || (text.oldToNew[j] == null) ) {
				break;
			}
			if (text.newToOld[i] != j) {
				break;
			}
			identText += text.newWords[i];
			i ++;
			j ++;
		}

// collect consecutive deletions
		while ( (text.oldToNew[j] == null) && (j < text.oldWords.length) ) {
			delText += text.oldWords[j];
			j ++;
		}

// collect consecutive inserts
		while ( (text.newToOld[i] == null) && (i < text.newWords.length) ) {
			insText += text.newWords[i];
			i ++;
		}

// remove leading and trailing similarities between delText and ins from highlighting
		var preText = '';
		var postText = '';
		if (wDiffWordDiff) {
			if ( (delText != '') && (insText != '') ) {

// remove leading similarities
				while ( delText.charAt(0) == insText.charAt(0) && (delText != '') && (insText != '') ) {
					preText = preText + delText.charAt(0);
					delText = delText.substr(1);
					insText = insText.substr(1);
				}

// remove trailing similarities
				while ( delText.charAt(delText.length - 1) == insText.charAt(insText.length - 1) && (delText != '') && (insText != '') ) {
					postText = delText.charAt(delText.length - 1) + postText;
					delText = delText.substr(0, delText.length - 1);
					insText = insText.substr(0, insText.length - 1);
				}
			}
		}

// output the identical text, deletions and inserts

// moved from here indicator
		if (blockText != '') {
			outText += blockText;
		}

// identical text
		if (identText != '') {
			outText += identStart + WDiffEscape(identText);
		}
		outText += preText;

// deleted text
		if (delText != '') {
			delText = wDiffHtmlDeleteStart + WDiffEscape(delText) + wDiffHtmlDeleteEnd;
			delText = delText.replace(/\n/g, '<span class="wDiffParagraph"></span><br>');
			outText += delText;
		}

// inserted text
		if (insText != '') {
			insText = wDiffHtmlInsertStart + WDiffEscape(insText) + wDiffHtmlInsertEnd;
			insText = insText.replace(/\n/g, '<span class="wDiffParagraph"></span><br>');
			outText += insText;
		}
		outText += postText;
	} while (i <= text.newWords.length);

	outText += '\n';
	outText = WDiffHtmlFormat(outText);

	return(outText);
};


// WDiffEscape: replaces html-sensitive characters in output text with character entities

window.WDiffEscape = function(text) {

	text = text.replace(/&/g, '&amp;');
	text = text.replace(/</g, '&lt;');
	text = text.replace(/>/g, '&gt;');
	text = text.replace(/"/g, '&quot;');

	return(text);
};


// HtmlCustomize: customize indicator html: replace {number} with the block number, {block} with the block style

window.WDiffHtmlCustomize = function(text, block) {

	text = text.replace(/\{number\}/, block);
	text = text.replace(/\{block\}/, wDiffStyleBlock[block]);

	return(text);
};


// HtmlFormat: replaces newlines and multiple spaces in text with html code

window.WDiffHtmlFormat = function(text) {

	text = text.replace(/ {2}/g, ' &nbsp;');
	text = text.replace(/\n/g, '<br>');

	return(text);
};


// WDiffDetectBlocks: detect block borders and moved blocks
// input: text object, block object

window.WDiffDetectBlocks = function(text, block) {

	block.oldStart  = [];
	block.oldToNew  = [];
	block.oldLength = [];
	block.oldWords  = [];
	block.newStart  = [];
	block.newLength = [];
	block.newWords  = [];
	block.newNumber = [];
	block.newBlock  = [];
	block.newLeft   = [];
	block.newRight  = [];
	block.newLeftIndex  = [];
	block.newRightIndex = [];

	var blockNumber = 0;
	var wordCounter = 0;
	var realWordCounter = 0;

// get old text block order
	if (wDiffShowBlockMoves) {
		var j = 0;
		var i = 0;
		do {

// detect block boundaries on old text
			if ( (text.oldToNew[j] != i) || (blockNumber == 0 ) ) {
				if ( ( (text.oldToNew[j] != null) || (j >= text.oldWords.length) ) && ( (text.newToOld[i] != null) || (i >= text.newWords.length) ) ) {
					if (blockNumber > 0) {
						block.oldLength[blockNumber - 1] = wordCounter;
						block.oldWords[blockNumber - 1] = realWordCounter;
						wordCounter = 0;
						realWordCounter = 0;
					}

					if (j >= text.oldWords.length) {
						j ++;
					}
					else {
						i = text.oldToNew[j];
						block.oldStart[blockNumber] = j;
						block.oldToNew[blockNumber] = text.oldToNew[j];
						blockNumber ++;
					}
				}
			}

// jump over identical pairs
			while ( (i < text.newWords.length) && (j < text.oldWords.length) ) {
				if ( (text.newToOld[i] == null) || (text.oldToNew[j] == null) ) {
					break;
				}
				if (text.oldToNew[j] != i) {
					break;
				}
				i ++;
				j ++;
				wordCounter ++;
				if ( /\w/.test( text.newWords[i] ) ) {
					realWordCounter ++;
				}
			}

// jump over consecutive deletions
			while ( (text.oldToNew[j] == null) && (j < text.oldWords.length) ) {
				j ++;
			}

// jump over consecutive inserts
			while ( (text.newToOld[i] == null) && (i < text.newWords.length) ) {
				i ++;
			}
		} while (j <= text.oldWords.length);

// get the block order in the new text
		var lastMin;
		var currMinIndex;
		lastMin = null;

// sort the data by increasing start numbers into new text block info
		for (var i = 0; i < blockNumber; i ++) {
			currMin = null;
			for (var j = 0; j < blockNumber; j ++) {
				curr = block.oldToNew[j];
				if ( (curr > lastMin) || (lastMin == null) ) {
					if ( (curr < currMin) || (currMin == null) ) {
						currMin = curr;
						currMinIndex = j;
					}
				}
			}
			block.newStart[i] = block.oldToNew[currMinIndex];
			block.newLength[i] = block.oldLength[currMinIndex];
			block.newWords[i] = block.oldWords[currMinIndex];
			block.newNumber[i] = currMinIndex;
			lastMin = currMin;
		}

// detect not moved blocks
		for (var i = 0; i < blockNumber; i ++) {
			if (block.newBlock[i] == null) {
				if (block.newNumber[i] == i) {
					block.newBlock[i] = 0;
				}
			}
		}

// detect switches of neighbouring blocks
		for (var i = 0; i < blockNumber - 1; i ++) {
			if ( (block.newBlock[i] == null) && (block.newBlock[i + 1] == null) ) {
				if (block.newNumber[i] - block.newNumber[i + 1] == 1) {
					if ( (block.newNumber[i + 1] - block.newNumber[i + 2] != 1) || (i + 2 >= blockNumber) ) {

// the shorter one is declared the moved one
						if (block.newLength[i] < block.newLength[i + 1]) {
							block.newBlock[i] = 1;
							block.newBlock[i + 1] = 0;
						}
						else {
							block.newBlock[i] = 0;
							block.newBlock[i + 1] = 1;
						}
					}
				}
			}
		}

// mark all others as moved and number the moved blocks
		j = 1;
		for (var i = 0; i < blockNumber; i ++) {
			if ( (block.newBlock[i] == null) || (block.newBlock[i] == 1) ) {
				block.newBlock[i] = j++;
			}
		}

// check if a block has been moved from this block border
		for (var i = 0; i < blockNumber; i ++) {
			for (var j = 0; j < blockNumber; j ++) {

				if (block.newNumber[j] == i) {
					if (block.newBlock[j] > 0) {

// block moved right
						if (block.newNumber[j] < j) {
							block.newRight[i] = block.newBlock[j];
							block.newRightIndex[i] = j;
						}

// block moved left
						else {
							block.newLeft[i + 1] = block.newBlock[j];
							block.newLeftIndex[i + 1] = j;
						}
					}
				}
			}
		}
	}
	return;
};


// WDiffShortenOutput: remove unchanged parts from final output
// input: the output of WDiffString
// returns: the text with removed unchanged passages indicated by (...)

window.WDiffShortenOutput = function(diffText) {

// html <br/> to newlines
	diffText = diffText.replace(/<br[^>]*>/g, '\n');

// scan for diff html tags
	var regExpDiff = /<\w+ class="(\w+)"[^>]*>(.|\n)*?<!--\1-->/g;
	var tagStart = [];
	var tagEnd = [];
	var i = 0;
	var found;
	while ( (found = regExpDiff.exec(diffText)) != null ) {

// combine consecutive diff tags
		if ( (i > 0) && (tagEnd[i - 1] == found.index) ) {
			tagEnd[i - 1] = found.index + found[0].length;
		}
		else {
			tagStart[i] = found.index;
			tagEnd[i] = found.index + found[0].length;
			i ++;
		}
	}

// no diff tags detected
	if (tagStart.length == 0) {
		return(wDiffNoChange);
	}

// define regexps
	var regExpHeading = /\n=+.+?=+ *\n|\n\{\||\n\|\}/g;
	var regExpParagraph = /\n\n+/g;
	var regExpLine = /\n+/g;
	var regExpBlank = /(<[^>]+>)*\s+/g;

// determine fragment border positions around diff tags
	var rangeStart = [];
	var rangeEnd = [];
	var rangeStartType = [];
	var rangeEndType = [];
	for (var i = 0; i < tagStart.length; i ++) {
		var found;

// find last heading before diff tag
		var lastPos = tagStart[i] - wDiffHeadingBefore;
		if (lastPos < 0) {
			lastPos = 0;
		}
		regExpHeading.lastIndex = lastPos;
		while ( (found = regExpHeading.exec(diffText)) != null ) {
			if (found.index > tagStart[i]) {
				break;
			}
			rangeStart[i] = found.index;
			rangeStartType[i] = 'heading';
		}

// find last paragraph before diff tag
		if (rangeStart[i] == null) {
			lastPos = tagStart[i] - wDiffParagraphBefore;
			if (lastPos < 0) {
				lastPos = 0;
			}
			regExpParagraph.lastIndex = lastPos;
			while ( (found = regExpParagraph.exec(diffText)) != null ) {
				if (found.index > tagStart[i]) {
					break;
				}
				rangeStart[i] = found.index;
				rangeStartType[i] = 'paragraph';
			}
		}

// find line break before diff tag
		if (rangeStart[i] == null) {
			lastPos = tagStart[i] - wDiffLineBeforeMax;
			if (lastPos < 0) {
				lastPos = 0;
			}
			regExpLine.lastIndex = lastPos;
			while ( (found = regExpLine.exec(diffText)) != null ) {
				if (found.index > tagStart[i] - wDiffLineBeforeMin) {
					break;
				}
				rangeStart[i] = found.index;
				rangeStartType[i] = 'line';
			}
		}

// find blank before diff tag
		if (rangeStart[i] == null) {
			lastPos = tagStart[i] - wDiffBlankBeforeMax;
			if (lastPos < 0) {
				lastPos = 0;
			}
			regExpBlank.lastIndex = lastPos;
			while ( (found = regExpBlank.exec(diffText)) != null ) {
				if (found.index > tagStart[i] - wDiffBlankBeforeMin) {
					break;
				}
				rangeStart[i] = found.index;
				rangeStartType[i] = 'blank';
			}
		}

// fixed number of chars before diff tag
		if (rangeStart[i] == null) {
			rangeStart[i] = tagStart[i] - wDiffCharsBefore;
			rangeStartType[i] = 'chars';
			if (rangeStart[i] < 0) {
				rangeStart[i] = 0;
			}
		}

// find first heading after diff tag
		regExpHeading.lastIndex = tagEnd[i];
		if ( (found = regExpHeading.exec(diffText)) != null ) {
			if (found.index < tagEnd[i] + wDiffHeadingAfter) {
				rangeEnd[i] = found.index + found[0].length;
				rangeEndType[i] = 'heading';
			}
		}

// find first paragraph after diff tag
		if (rangeEnd[i] == null) {
			regExpParagraph.lastIndex = tagEnd[i];
			if ( (found = regExpParagraph.exec(diffText)) != null ) {
				if (found.index < tagEnd[i] + wDiffParagraphAfter) {
					rangeEnd[i] = found.index;
					rangeEndType[i] = 'paragraph';
				}
			}
		}

// find first line break after diff tag
		if (rangeEnd[i] == null) {
			regExpLine.lastIndex = tagEnd[i] + wDiffLineAfterMin;
			if ( (found = regExpLine.exec(diffText)) != null ) {
				if (found.index < tagEnd[i] + wDiffLineAfterMax) {
					rangeEnd[i] = found.index;
					rangeEndType[i] = 'break';
				}
			}
		}

// find blank after diff tag
		if (rangeEnd[i] == null) {
			regExpBlank.lastIndex = tagEnd[i] + wDiffBlankAfterMin;
			if ( (found = regExpBlank.exec(diffText)) != null ) {
				if (found.index < tagEnd[i] + wDiffBlankAfterMax) {
					rangeEnd[i] = found.index;
					rangeEndType[i] = 'blank';
				}
			}
		}

// fixed number of chars after diff tag
		if (rangeEnd[i] == null) {
			rangeEnd[i] = tagEnd[i] + wDiffCharsAfter;
			if (rangeEnd[i] > diffText.length) {
				rangeEnd[i] = diffText.length;
				rangeEndType[i] = 'chars';
			}
		}
	}

// remove overlaps, join close fragments
	var fragmentStart = [];
	var fragmentEnd = [];
	var fragmentStartType = [];
	var fragmentEndType = [];
	fragmentStart[0] = rangeStart[0];
	fragmentEnd[0] = rangeEnd[0];
	fragmentStartType[0] = rangeStartType[0];
	fragmentEndType[0] = rangeEndType[0];
	var j = 1;
	for (var i = 1; i < rangeStart.length; i ++) {
		if (rangeStart[i] > fragmentEnd[j - 1] + wDiffFragmentJoin) {
			fragmentStart[j] = rangeStart[i];
			fragmentEnd[j] = rangeEnd[i];
			fragmentStartType[j] = rangeStartType[i];
			fragmentEndType[j] = rangeEndType[i];
			j ++;
		}
		else {
			fragmentEnd[j - 1] = rangeEnd[i];
			fragmentEndType[j - 1] = rangeEndType[i];
		}
	}

// assemble the fragments
	var outText = '';
	for (var i = 0; i < fragmentStart.length; i ++) {

// get text fragment
		var fragment = diffText.substring(fragmentStart[i], fragmentEnd[i]);
		var fragment = fragment.replace(/^\n+|\n+$/g, '');

// add inline marks for omitted chars and words
		if (fragmentStart[i] > 0) {
			if (fragmentStartType[i] == 'chars') {
				fragment = wDiffOmittedChars + fragment;
			}
			else if (fragmentStartType[i] == 'blank') {
				fragment = wDiffOmittedChars + ' ' + fragment;
			}
		}
		if (fragmentEnd[i] < diffText.length) {
			if (fragmentStartType[i] == 'chars') {
				fragment = fragment + wDiffOmittedChars;
			}
			else if (fragmentStartType[i] == 'blank') {
				fragment = fragment + ' ' + wDiffOmittedChars;
			}
		}

// add omitted line separator
		if (fragmentStart[i] > 0) {
			outText += wDiffOmittedLines;
		}

// encapsulate span errors
		outText += '<div>' + fragment + '</div>';
	}

// add trailing omitted line separator
	if (fragmentEnd[i - 1] < diffText.length) {
		outText = outText + wDiffOmittedLines;
	}

// remove leading and trailing empty lines
	outText = outText.replace(/^(<div>)\n+|\n+(<\/div>)$/g, '$1$2');

// convert to html linebreaks
	outText = outText.replace(/\n/g, '<br />');

	return(outText);
};

</script>
</html>
<?php


/**
 * Indents a flat JSON string to make it more human-readable.
 *
 * Copied from: http://recursive-design.com/blog/2008/03/11/format-json-with-php/
 *
 * @param string $json The original JSON string to process.
 *
 * @return string Indented version of the original JSON string.
 */
function json_indent($json) {

    $result      = '';
    $pos         = 0;
    $strLen      = strlen($json);
    $indentStr   = '  ';
    $newLine     = "\n";
    $prevChar    = '';
    $outOfQuotes = true;

    for ($i=0; $i<=$strLen; $i++) {

        // Grab the next character in the string.
        $char = substr($json, $i, 1);

        // Are we inside a quoted string?
        if ($char == '"' && $prevChar != '\\') {
            $outOfQuotes = !$outOfQuotes;

        // If this character is the end of an element,
        // output a new line and indent the next line.
        } else if(($char == '}' || $char == ']') && $outOfQuotes) {
            $result .= $newLine;
            $pos --;
            for ($j=0; $j<$pos; $j++) {
                $result .= $indentStr;
            }
        }

        // Add the character to the result string.
        $result .= $char;

        // If the last character was the beginning of an element,
        // output a new line and indent the next line.
        if (($char == ',' || $char == '{' || $char == '[') && $outOfQuotes) {
            $result .= $newLine;
            if ($char == '{' || $char == '[') {
                $pos ++;
            }

            for ($j = 0; $j < $pos; $j++) {
                $result .= $indentStr;
            }
        }

        $prevChar = $char;
    }

    return $result;
}

/*
 * This is the license for Spyc.
The MIT License

Copyright (c) 2011 Vladimir Andersen

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.
 */
/**
   * Spyc -- A Simple PHP YAML Class
   * @version 0.5.1
   * @author Vlad Andersen <vlad.andersen@gmail.com>
   * @author Chris Wanstrath <chris@ozmm.org>
   * @link http://code.google.com/p/spyc/
   * @copyright Copyright 2005-2006 Chris Wanstrath, 2006-2011 Vlad Andersen
   * @license http://www.opensource.org/licenses/mit-license.php MIT License
   * @package Spyc
   */

/**
   * The Simple PHP YAML Class.
   *
   * This class can be used to read a YAML file and convert its contents
   * into a PHP array.  It currently supports a very limited subsection of
   * the YAML spec.
   *
   * Usage:
   * <code>
   *   $Spyc  = new Spyc;
   *   $array = $Spyc->load($file);
   * </code>
   * or:
   * <code>
   *   $array = Spyc::YAMLLoad($file);
   * </code>
   * or:
   * <code>
   *   $array = spyc_load_file($file);
   * </code>
   * @package Spyc
   */
class Spyc {

  // SETTINGS

  const REMPTY = "\0\0\0\0\0";

  /**
   * Setting this to true will force YAMLDump to enclose any string value in
   * quotes.  False by default.
   *
   * @var bool
   */
  public $setting_dump_force_quotes = false;

  /**
   * Setting this to true will forse YAMLLoad to use syck_load function when
   * possible. False by default.
   * @var bool
   */
  public $setting_use_syck_is_possible = false;



  /**#@+
  * @access private
  * @var mixed
  */
  private $_dumpIndent;
  private $_dumpWordWrap;
  private $_containsGroupAnchor = false;
  private $_containsGroupAlias = false;
  private $path;
  private $result;
  private $LiteralPlaceHolder = '___YAML_Literal_Block___';
  private $SavedGroups = array();
  private $indent;
  /**
   * Path modifier that should be applied after adding current element.
   * @var array
   */
  private $delayedPath = array();

  /**#@+
  * @access public
  * @var mixed
  */
  public $_nodeId;

/**
 * Load a valid YAML string to Spyc.
 * @param string $input
 * @return array
 */
  public function load ($input) {
    return $this->__loadString($input);
  }

 /**
 * Load a valid YAML file to Spyc.
 * @param string $file
 * @return array
 */
  public function loadFile ($file) {
    return $this->__load($file);
  }

  /**
     * Load YAML into a PHP array statically
     *
     * The load method, when supplied with a YAML stream (string or file),
     * will do its best to convert YAML in a file into a PHP array.  Pretty
     * simple.
     *  Usage:
     *  <code>
     *   $array = Spyc::YAMLLoad('lucky.yaml');
     *   print_r($array);
     *  </code>
     * @access public
     * @return array
     * @param string $input Path of YAML file or string containing YAML
     */
  public static function YAMLLoad($input) {
    $Spyc = new Spyc;
    return $Spyc->__load($input);
  }

  /**
     * Load a string of YAML into a PHP array statically
     *
     * The load method, when supplied with a YAML string, will do its best
     * to convert YAML in a string into a PHP array.  Pretty simple.
     *
     * Note: use this function if you don't want files from the file system
     * loaded and processed as YAML.  This is of interest to people concerned
     * about security whose input is from a string.
     *
     *  Usage:
     *  <code>
     *   $array = Spyc::YAMLLoadString("---\n0: hello world\n");
     *   print_r($array);
     *  </code>
     * @access public
     * @return array
     * @param string $input String containing YAML
     */
  public static function YAMLLoadString($input) {
    $Spyc = new Spyc;
    return $Spyc->__loadString($input);
  }

  /**
     * Dump YAML from PHP array statically
     *
     * The dump method, when supplied with an array, will do its best
     * to convert the array into friendly YAML.  Pretty simple.  Feel free to
     * save the returned string as nothing.yaml and pass it around.
     *
     * Oh, and you can decide how big the indent is and what the wordwrap
     * for folding is.  Pretty cool -- just pass in 'false' for either if
     * you want to use the default.
     *
     * Indent's default is 2 spaces, wordwrap's default is 40 characters.  And
     * you can turn off wordwrap by passing in 0.
     *
     * @access public
     * @return string
     * @param array $array PHP array
     * @param int $indent Pass in false to use the default, which is 2
     * @param int $wordwrap Pass in 0 for no wordwrap, false for default (40)
     * @param int $no_opening_dashes Do not start YAML file with "---\n"
     */
  public static function YAMLDump($array, $indent = false, $wordwrap = false, $no_opening_dashes = false) {
    $spyc = new Spyc;
    return $spyc->dump($array, $indent, $wordwrap, $no_opening_dashes);
  }


  /**
     * Dump PHP array to YAML
     *
     * The dump method, when supplied with an array, will do its best
     * to convert the array into friendly YAML.  Pretty simple.  Feel free to
     * save the returned string as tasteful.yaml and pass it around.
     *
     * Oh, and you can decide how big the indent is and what the wordwrap
     * for folding is.  Pretty cool -- just pass in 'false' for either if
     * you want to use the default.
     *
     * Indent's default is 2 spaces, wordwrap's default is 40 characters.  And
     * you can turn off wordwrap by passing in 0.
     *
     * @access public
     * @return string
     * @param array $array PHP array
     * @param int $indent Pass in false to use the default, which is 2
     * @param int $wordwrap Pass in 0 for no wordwrap, false for default (40)
     */
  public function dump($array,$indent = false,$wordwrap = false, $no_opening_dashes = false) {
    // Dumps to some very clean YAML.  We'll have to add some more features
    // and options soon.  And better support for folding.

    // New features and options.
    if ($indent === false or !is_numeric($indent)) {
      $this->_dumpIndent = 2;
    } else {
      $this->_dumpIndent = $indent;
    }

    if ($wordwrap === false or !is_numeric($wordwrap)) {
      $this->_dumpWordWrap = 40;
    } else {
      $this->_dumpWordWrap = $wordwrap;
    }

    // New YAML document
    $string = "";
    if (!$no_opening_dashes) $string = "---\n";

    // Start at the base of the array and move through it.
    if ($array) {
      $array = (array)$array;
      $previous_key = -1;
      foreach ($array as $key => $value) {
        if (!isset($first_key)) $first_key = $key;
        $string .= $this->_yamlize($key,$value,0,$previous_key, $first_key, $array);
        $previous_key = $key;
      }
    }
    return $string;
  }

  /**
     * Attempts to convert a key / value array item to YAML
     * @access private
     * @return string
     * @param $key The name of the key
     * @param $value The value of the item
     * @param $indent The indent of the current node
     */
  private function _yamlize($key,$value,$indent, $previous_key = -1, $first_key = 0, $source_array = null) {
    if (is_array($value)) {
      if (empty ($value))
        return $this->_dumpNode($key, array(), $indent, $previous_key, $first_key, $source_array);
      // It has children.  What to do?
      // Make it the right kind of item
      $string = $this->_dumpNode($key, self::REMPTY, $indent, $previous_key, $first_key, $source_array);
      // Add the indent
      $indent += $this->_dumpIndent;
      // Yamlize the array
      $string .= $this->_yamlizeArray($value,$indent);
    } elseif (!is_array($value)) {
      // It doesn't have children.  Yip.
      $string = $this->_dumpNode($key, $value, $indent, $previous_key, $first_key, $source_array);
    }
    return $string;
  }

  /**
     * Attempts to convert an array to YAML
     * @access private
     * @return string
     * @param $array The array you want to convert
     * @param $indent The indent of the current level
     */
  private function _yamlizeArray($array,$indent) {
    if (is_array($array)) {
      $string = '';
      $previous_key = -1;
      foreach ($array as $key => $value) {
        if (!isset($first_key)) $first_key = $key;
        $string .= $this->_yamlize($key, $value, $indent, $previous_key, $first_key, $array);
        $previous_key = $key;
      }
      return $string;
    } else {
      return false;
    }
  }

  /**
     * Returns YAML from a key and a value
     * @access private
     * @return string
     * @param $key The name of the key
     * @param $value The value of the item
     * @param $indent The indent of the current node
     */
  private function _dumpNode($key, $value, $indent, $previous_key = -1, $first_key = 0, $source_array = null) {
    // do some folding here, for blocks
    if (is_string ($value) && ((strpos($value,"\n") !== false || strpos($value,": ") !== false || strpos($value,"- ") !== false ||
      strpos($value,"*") !== false || strpos($value,"#") !== false || strpos($value,"<") !== false || strpos($value,">") !== false || strpos ($value, '  ') !== false ||
      strpos($value,"[") !== false || strpos($value,"]") !== false || strpos($value,"{") !== false || strpos($value,"}") !== false) || strpos($value,"&") !== false || strpos($value, "'") !== false || strpos($value, "!") === 0 ||
      substr ($value, -1, 1) == ':')
    ) {
      $value = $this->_doLiteralBlock($value,$indent);
    } else {
      $value  = $this->_doFolding($value,$indent);
    }

    if ($value === array()) $value = '[ ]';
    if ($value === "") $value = '""';
    if (self::isTranslationWord($value)) {
      $value = $this->_doLiteralBlock($value, $indent);
    }
    if (trim ($value) != $value)
       $value = $this->_doLiteralBlock($value,$indent);

    if (is_bool($value)) {
       $value = $value ? "true" : "false";
    }

    if ($value === null) $value = 'null';
    if ($value === "'" . self::REMPTY . "'") $value = null;

    $spaces = str_repeat(' ',$indent);

    //if (is_int($key) && $key - 1 == $previous_key && $first_key===0) {
    if (is_array ($source_array) && array_keys($source_array) === range(0, count($source_array) - 1)) {
      // It's a sequence
      $string = $spaces.'- '.$value."\n";
    } else {
      // if ($first_key===0)  throw new Exception('Keys are all screwy.  The first one was zero, now it\'s "'. $key .'"');
      // It's mapped
      if (strpos($key, ":") !== false || strpos($key, "#") !== false) { $key = '"' . $key . '"'; }
      $string = rtrim ($spaces.$key.': '.$value)."\n";
    }
    return $string;
  }

  /**
     * Creates a literal block for dumping
     * @access private
     * @return string
     * @param $value
     * @param $indent int The value of the indent
     */
  private function _doLiteralBlock($value,$indent) {
    if ($value === "\n") return '\n';
    if (strpos($value, "\n") === false && strpos($value, "'") === false) {
      return sprintf ("'%s'", $value);
    }
    if (strpos($value, "\n") === false && strpos($value, '"') === false) {
      return sprintf ('"%s"', $value);
    }
    $exploded = explode("\n",$value);
    $newValue = '|';
    $indent  += $this->_dumpIndent;
    $spaces   = str_repeat(' ',$indent);
    foreach ($exploded as $line) {
      $newValue .= "\n" . $spaces . ($line);
    }
    return $newValue;
  }

  /**
     * Folds a string of text, if necessary
     * @access private
     * @return string
     * @param $value The string you wish to fold
     */
  private function _doFolding($value,$indent) {
    // Don't do anything if wordwrap is set to 0

    if ($this->_dumpWordWrap !== 0 && is_string ($value) && strlen($value) > $this->_dumpWordWrap) {
      $indent += $this->_dumpIndent;
      $indent = str_repeat(' ',$indent);
      $wrapped = wordwrap($value,$this->_dumpWordWrap,"\n$indent");
      $value   = ">\n".$indent.$wrapped;
    } else {
      if ($this->setting_dump_force_quotes && is_string ($value) && $value !== self::REMPTY)
        $value = '"' . $value . '"';
      if (is_numeric($value) && is_string($value))
        $value = '"' . $value . '"';
    }


    return $value;
  }

  private function isTrueWord($value) {
    $words = self::getTranslations(array('true', 'on', 'yes', 'y'));
    return in_array($value, $words, true);
  }

  private function isFalseWord($value) {
    $words = self::getTranslations(array('false', 'off', 'no', 'n'));
    return in_array($value, $words, true);
  }

  private function isNullWord($value) {
    $words = self::getTranslations(array('null', '~'));
    return in_array($value, $words, true);
  }

  private function isTranslationWord($value) {
    return (
      self::isTrueWord($value)  ||
      self::isFalseWord($value) ||
      self::isNullWord($value)
    );
  }

  /**
     * Coerce a string into a native type
     * Reference: http://yaml.org/type/bool.html
     * TODO: Use only words from the YAML spec.
     * @access private
     * @param $value The value to coerce
     */
  private function coerceValue(&$value) {
    if (self::isTrueWord($value)) {
      $value = true;
    } else if (self::isFalseWord($value)) {
      $value = false;
    } else if (self::isNullWord($value)) {
      $value = null;
    }
  }

  /**
     * Given a set of words, perform the appropriate translations on them to
     * match the YAML 1.1 specification for type coercing.
     * @param $words The words to translate
     * @access private
     */
  private static function getTranslations(array $words) {
    $result = array();
    foreach ($words as $i) {
      $result = array_merge($result, array(ucfirst($i), strtoupper($i), strtolower($i)));
    }
    return $result;
  }

// LOADING FUNCTIONS

  private function __load($input) {
    $Source = $this->loadFromSource($input);
    return $this->loadWithSource($Source);
  }

  private function __loadString($input) {
    $Source = $this->loadFromString($input);
    return $this->loadWithSource($Source);
  }

  private function loadWithSource($Source) {
    if (empty ($Source)) return array();
    if ($this->setting_use_syck_is_possible && function_exists ('syck_load')) {
      $array = syck_load (implode ("\n", $Source));
      return is_array($array) ? $array : array();
    }

    $this->path = array();
    $this->result = array();

    $cnt = count($Source);
    for ($i = 0; $i < $cnt; $i++) {
      $line = $Source[$i];

      $this->indent = strlen($line) - strlen(ltrim($line));
      $tempPath = $this->getParentPathByIndent($this->indent);
      $line = self::stripIndent($line, $this->indent);
      if (self::isComment($line)) continue;
      if (self::isEmpty($line)) continue;
      $this->path = $tempPath;

      $literalBlockStyle = self::startsLiteralBlock($line);
      if ($literalBlockStyle) {
        $line = rtrim ($line, $literalBlockStyle . " \n");
        $literalBlock = '';
        $line .= ' '.$this->LiteralPlaceHolder;
        $literal_block_indent = strlen($Source[$i+1]) - strlen(ltrim($Source[$i+1]));
        while (++$i < $cnt && $this->literalBlockContinues($Source[$i], $this->indent)) {
          $literalBlock = $this->addLiteralLine($literalBlock, $Source[$i], $literalBlockStyle, $literal_block_indent);
        }
        $i--;
      }

      // Strip out comments
      if (strpos ($line, '#')) {
          $line = preg_replace('/\s*#([^"\']+)$/','',$line);
      }

      while (++$i < $cnt && self::greedilyNeedNextLine($line)) {
        $line = rtrim ($line, " \n\t\r") . ' ' . ltrim ($Source[$i], " \t");
      }
      $i--;

      $lineArray = $this->_parseLine($line);

      if ($literalBlockStyle)
        $lineArray = $this->revertLiteralPlaceHolder ($lineArray, $literalBlock);

      $this->addArray($lineArray, $this->indent);

      foreach ($this->delayedPath as $indent => $delayedPath)
        $this->path[$indent] = $delayedPath;

      $this->delayedPath = array();

    }
    return $this->result;
  }

  private function loadFromSource ($input) {
    if (!empty($input) && strpos($input, "\n") === false && file_exists($input))
      $input = file_get_contents($input);

    return $this->loadFromString($input);
  }

  private function loadFromString ($input) {
    $lines = explode("\n",$input);
    foreach ($lines as $k => $_) {
      $lines[$k] = rtrim ($_, "\r");
    }
    return $lines;
  }

  /**
     * Parses YAML code and returns an array for a node
     * @access private
     * @return array
     * @param string $line A line from the YAML file
     */
  private function _parseLine($line) {
    if (!$line) return array();
    $line = trim($line);
    if (!$line) return array();

    $array = array();

    $group = $this->nodeContainsGroup($line);
    if ($group) {
      $this->addGroup($line, $group);
      $line = $this->stripGroup ($line, $group);
    }

    if ($this->startsMappedSequence($line))
      return $this->returnMappedSequence($line);

    if ($this->startsMappedValue($line))
      return $this->returnMappedValue($line);

    if ($this->isArrayElement($line))
     return $this->returnArrayElement($line);

    if ($this->isPlainArray($line))
     return $this->returnPlainArray($line);


    return $this->returnKeyValuePair($line);

  }

  /**
     * Finds the type of the passed value, returns the value as the new type.
     * @access private
     * @param string $value
     * @return mixed
     */
  private function _toType($value) {
    if ($value === '') return "";
    $first_character = $value[0];
    $last_character = substr($value, -1, 1);

    $is_quoted = false;
    do {
      if (!$value) break;
      if ($first_character != '"' && $first_character != "'") break;
      if ($last_character != '"' && $last_character != "'") break;
      $is_quoted = true;
    } while (0);

    if ($is_quoted) {
      $value = str_replace('\n', "\n", $value);
      return strtr(substr ($value, 1, -1), array ('\\"' => '"', '\'\'' => '\'', '\\\'' => '\''));
    }

    if (strpos($value, ' #') !== false && !$is_quoted)
      $value = preg_replace('/\s+#(.+)$/','',$value);

    if ($first_character == '[' && $last_character == ']') {
      // Take out strings sequences and mappings
      $innerValue = trim(substr ($value, 1, -1));
      if ($innerValue === '') return array();
      $explode = $this->_inlineEscape($innerValue);
      // Propagate value array
      $value  = array();
      foreach ($explode as $v) {
        $value[] = $this->_toType($v);
      }
      return $value;
    }

    if (strpos($value,': ')!==false && $first_character != '{') {
      $array = explode(': ',$value);
      $key   = trim($array[0]);
      array_shift($array);
      $value = trim(implode(': ',$array));
      $value = $this->_toType($value);
      return array($key => $value);
    }

    if ($first_character == '{' && $last_character == '}') {
      $innerValue = trim(substr ($value, 1, -1));
      if ($innerValue === '') return array();
      // Inline Mapping
      // Take out strings sequences and mappings
      $explode = $this->_inlineEscape($innerValue);
      // Propagate value array
      $array = array();
      foreach ($explode as $v) {
        $SubArr = $this->_toType($v);
        if (empty($SubArr)) continue;
        if (is_array ($SubArr)) {
          $array[key($SubArr)] = $SubArr[key($SubArr)]; continue;
        }
        $array[] = $SubArr;
      }
      return $array;
    }

    if ($value == 'null' || $value == 'NULL' || $value == 'Null' || $value == '' || $value == '~') {
      return null;
    }

    if ( is_numeric($value) && preg_match ('/^(-|)[1-9]+[0-9]*$/', $value) ){
      $intvalue = (int)$value;
      if ($intvalue != PHP_INT_MAX)
        $value = $intvalue;
      return $value;
    }

    if (is_numeric($value) && preg_match('/^0[xX][0-9a-fA-F]+$/', $value)) {
      // Hexadecimal value.
      return hexdec($value);
    }

    $this->coerceValue($value);

    if (is_numeric($value)) {
      if ($value === '0') return 0;
      if (rtrim ($value, 0) === $value)
        $value = (float)$value;
      return $value;
    }

    return $value;
  }

  /**
     * Used in inlines to check for more inlines or quoted strings
     * @access private
     * @return array
     */
  private function _inlineEscape($inline) {
    // There's gotta be a cleaner way to do this...
    // While pure sequences seem to be nesting just fine,
    // pure mappings and mappings with sequences inside can't go very
    // deep.  This needs to be fixed.

    $seqs = array();
    $maps = array();
    $saved_strings = array();
    $saved_empties = array();

    // Check for empty strings
    $regex = '/("")|(\'\')/';
    if (preg_match_all($regex,$inline,$strings)) {
      $saved_empties = $strings[0];
      $inline  = preg_replace($regex,'YAMLEmpty',$inline);
    }
    unset($regex);

    // Check for strings
    $regex = '/(?:(")|(?:\'))((?(1)[^"]+|[^\']+))(?(1)"|\')/';
    if (preg_match_all($regex,$inline,$strings)) {
      $saved_strings = $strings[0];
      $inline  = preg_replace($regex,'YAMLString',$inline);
    }
    unset($regex);

    // echo $inline;

    $i = 0;
    do {

    // Check for sequences
    while (preg_match('/\[([^{}\[\]]+)\]/U',$inline,$matchseqs)) {
      $seqs[] = $matchseqs[0];
      $inline = preg_replace('/\[([^{}\[\]]+)\]/U', ('YAMLSeq' . (count($seqs) - 1) . 's'), $inline, 1);
    }

    // Check for mappings
    while (preg_match('/{([^\[\]{}]+)}/U',$inline,$matchmaps)) {
      $maps[] = $matchmaps[0];
      $inline = preg_replace('/{([^\[\]{}]+)}/U', ('YAMLMap' . (count($maps) - 1) . 's'), $inline, 1);
    }

    if ($i++ >= 10) break;

    } while (strpos ($inline, '[') !== false || strpos ($inline, '{') !== false);

    $explode = explode(',',$inline);
    $explode = array_map('trim', $explode);
    $stringi = 0; $i = 0;

    while (1) {

    // Re-add the sequences
    if (!empty($seqs)) {
      foreach ($explode as $key => $value) {
        if (strpos($value,'YAMLSeq') !== false) {
          foreach ($seqs as $seqk => $seq) {
            $explode[$key] = str_replace(('YAMLSeq'.$seqk.'s'),$seq,$value);
            $value = $explode[$key];
          }
        }
      }
    }

    // Re-add the mappings
    if (!empty($maps)) {
      foreach ($explode as $key => $value) {
        if (strpos($value,'YAMLMap') !== false) {
          foreach ($maps as $mapk => $map) {
            $explode[$key] = str_replace(('YAMLMap'.$mapk.'s'), $map, $value);
            $value = $explode[$key];
          }
        }
      }
    }


    // Re-add the strings
    if (!empty($saved_strings)) {
      foreach ($explode as $key => $value) {
        while (strpos($value,'YAMLString') !== false) {
          $explode[$key] = preg_replace('/YAMLString/',$saved_strings[$stringi],$value, 1);
          unset($saved_strings[$stringi]);
          ++$stringi;
          $value = $explode[$key];
        }
      }
    }


    // Re-add the empties
    if (!empty($saved_empties)) {
      foreach ($explode as $key => $value) {
        while (strpos($value,'YAMLEmpty') !== false) {
          $explode[$key] = preg_replace('/YAMLEmpty/', '', $value, 1);
          $value = $explode[$key];
        }
      }
    }

    $finished = true;
    foreach ($explode as $key => $value) {
      if (strpos($value,'YAMLSeq') !== false) {
        $finished = false; break;
      }
      if (strpos($value,'YAMLMap') !== false) {
        $finished = false; break;
      }
      if (strpos($value,'YAMLString') !== false) {
        $finished = false; break;
      }
      if (strpos($value,'YAMLEmpty') !== false) {
        $finished = false; break;
      }
    }
    if ($finished) break;

    $i++;
    if ($i > 10)
      break; // Prevent infinite loops.
    }


    return $explode;
  }

  private function literalBlockContinues ($line, $lineIndent) {
    if (!trim($line)) return true;
    if (strlen($line) - strlen(ltrim($line)) > $lineIndent) return true;
    return false;
  }

  private function referenceContentsByAlias ($alias) {
    do {
      if (!isset($this->SavedGroups[$alias])) { echo "Bad group name: $alias."; break; }
      $groupPath = $this->SavedGroups[$alias];
      $value = $this->result;
      foreach ($groupPath as $k) {
        $value = $value[$k];
      }
    } while (false);
    return $value;
  }

  private function addArrayInline ($array, $indent) {
      $CommonGroupPath = $this->path;
      if (empty ($array)) return false;

      foreach ($array as $k => $_) {
        $this->addArray(array($k => $_), $indent);
        $this->path = $CommonGroupPath;
      }
      return true;
  }

  private function addArray ($incoming_data, $incoming_indent) {

   // print_r ($incoming_data);

    if (count ($incoming_data) > 1)
      return $this->addArrayInline ($incoming_data, $incoming_indent);

    $key = key ($incoming_data);
    $value = isset($incoming_data[$key]) ? $incoming_data[$key] : null;
    if ($key === '__!YAMLZero') $key = '0';

    if ($incoming_indent == 0 && !$this->_containsGroupAlias && !$this->_containsGroupAnchor) { // Shortcut for root-level values.
      if ($key || $key === '' || $key === '0') {
        $this->result[$key] = $value;
      } else {
        $this->result[] = $value; end ($this->result); $key = key ($this->result);
      }
      $this->path[$incoming_indent] = $key;
      return;
    }



    $history = array();
    // Unfolding inner array tree.
    $history[] = $_arr = $this->result;
    foreach ($this->path as $k) {
      $history[] = $_arr = $_arr[$k];
    }

    if ($this->_containsGroupAlias) {
      $value = $this->referenceContentsByAlias($this->_containsGroupAlias);
      $this->_containsGroupAlias = false;
    }


    // Adding string or numeric key to the innermost level or $this->arr.
    if (is_string($key) && $key == '<<') {
      if (!is_array ($_arr)) { $_arr = array (); }

      $_arr = array_merge ($_arr, $value);
    } else if ($key || $key === '' || $key === '0') {
      if (!is_array ($_arr))
        $_arr = array ($key=>$value);
      else
        $_arr[$key] = $value;
    } else {
      if (!is_array ($_arr)) { $_arr = array ($value); $key = 0; }
      else { $_arr[] = $value; end ($_arr); $key = key ($_arr); }
    }

    $reverse_path = array_reverse($this->path);
    $reverse_history = array_reverse ($history);
    $reverse_history[0] = $_arr;
    $cnt = count($reverse_history) - 1;
    for ($i = 0; $i < $cnt; $i++) {
      $reverse_history[$i+1][$reverse_path[$i]] = $reverse_history[$i];
    }
    $this->result = $reverse_history[$cnt];

    $this->path[$incoming_indent] = $key;

    if ($this->_containsGroupAnchor) {
      $this->SavedGroups[$this->_containsGroupAnchor] = $this->path;
      if (is_array ($value)) {
        $k = key ($value);
        if (!is_int ($k)) {
          $this->SavedGroups[$this->_containsGroupAnchor][$incoming_indent + 2] = $k;
        }
      }
      $this->_containsGroupAnchor = false;
    }

  }

  private static function startsLiteralBlock ($line) {
    $lastChar = substr (trim($line), -1);
    if ($lastChar != '>' && $lastChar != '|') return false;
    if ($lastChar == '|') return $lastChar;
    // HTML tags should not be counted as literal blocks.
    if (preg_match ('#<.*?>$#', $line)) return false;
    return $lastChar;
  }

  private static function greedilyNeedNextLine($line) {
    $line = trim ($line);
    if (!strlen($line)) return false;
    if (substr ($line, -1, 1) == ']') return false;
    if ($line[0] == '[') return true;
    if (preg_match ('#^[^:]+?:\s*\[#', $line)) return true;
    return false;
  }

  private function addLiteralLine ($literalBlock, $line, $literalBlockStyle, $indent = -1) {
    $line = self::stripIndent($line, $indent);
    if ($literalBlockStyle !== '|') {
        $line = self::stripIndent($line);
    }
    $line = rtrim ($line, "\r\n\t ") . "\n";
    if ($literalBlockStyle == '|') {
      return $literalBlock . $line;
    }
    if (strlen($line) == 0)
      return rtrim($literalBlock, ' ') . "\n";
    if ($line == "\n" && $literalBlockStyle == '>') {
      return rtrim ($literalBlock, " \t") . "\n";
    }
    if ($line != "\n")
      $line = trim ($line, "\r\n ") . " ";
    return $literalBlock . $line;
  }

   function revertLiteralPlaceHolder ($lineArray, $literalBlock) {
     foreach ($lineArray as $k => $_) {
      if (is_array($_))
        $lineArray[$k] = $this->revertLiteralPlaceHolder ($_, $literalBlock);
      else if (substr($_, -1 * strlen ($this->LiteralPlaceHolder)) == $this->LiteralPlaceHolder)
	       $lineArray[$k] = rtrim ($literalBlock, " \r\n");
     }
     return $lineArray;
   }

  private static function stripIndent ($line, $indent = -1) {
    if ($indent == -1) $indent = strlen($line) - strlen(ltrim($line));
    return substr ($line, $indent);
  }

  private function getParentPathByIndent ($indent) {
    if ($indent == 0) return array();
    $linePath = $this->path;
    do {
      end($linePath); $lastIndentInParentPath = key($linePath);
      if ($indent <= $lastIndentInParentPath) array_pop ($linePath);
    } while ($indent <= $lastIndentInParentPath);
    return $linePath;
  }


  private function clearBiggerPathValues ($indent) {


    if ($indent == 0) $this->path = array();
    if (empty ($this->path)) return true;

    foreach ($this->path as $k => $_) {
      if ($k > $indent) unset ($this->path[$k]);
    }

    return true;
  }


  private static function isComment ($line) {
    if (!$line) return false;
    if ($line[0] == '#') return true;
    if (trim($line, " \r\n\t") == '---') return true;
    return false;
  }

  private static function isEmpty ($line) {
    return (trim ($line) === '');
  }


  private function isArrayElement ($line) {
    if (!$line || !is_scalar($line)) return false;
    if (substr($line, 0, 2) != '- ') return false;
    if (strlen ($line) > 3)
      if (substr($line,0,3) == '---') return false;

    return true;
  }

  private function isHashElement ($line) {
    return strpos($line, ':');
  }

  private function isLiteral ($line) {
    if ($this->isArrayElement($line)) return false;
    if ($this->isHashElement($line)) return false;
    return true;
  }


  private static function unquote ($value) {
    if (!$value) return $value;
    if (!is_string($value)) return $value;
    if ($value[0] == '\'') return trim ($value, '\'');
    if ($value[0] == '"') return trim ($value, '"');
    return $value;
  }

  private function startsMappedSequence ($line) {
    return (substr($line, 0, 2) == '- ' && substr ($line, -1, 1) == ':');
  }

  private function returnMappedSequence ($line) {
    $array = array();
    $key         = self::unquote(trim(substr($line,1,-1)));
    $array[$key] = array();
    $this->delayedPath = array(strpos ($line, $key) + $this->indent => $key);
    return array($array);
  }

  private function checkKeysInValue($value) {
    if (strchr('[{"\'', $value[0]) === false) {
      if (strchr($value, ': ') !== false) {
          throw new Exception('Too many keys: '.$value);
      }
    }
  }

  private function returnMappedValue ($line) {
    $this->checkKeysInValue($line);
    $array = array();
    $key         = self::unquote (trim(substr($line,0,-1)));
    $array[$key] = '';
    return $array;
  }

  private function startsMappedValue ($line) {
    return (substr ($line, -1, 1) == ':');
  }

  private function isPlainArray ($line) {
    return ($line[0] == '[' && substr ($line, -1, 1) == ']');
  }

  private function returnPlainArray ($line) {
    return $this->_toType($line);
  }

  private function returnKeyValuePair ($line) {
    $array = array();
    $key = '';
    if (strpos ($line, ': ')) {
      // It's a key/value pair most likely
      // If the key is in double quotes pull it out
      if (($line[0] == '"' || $line[0] == "'") && preg_match('/^(["\'](.*)["\'](\s)*:)/',$line,$matches)) {
        $value = trim(str_replace($matches[1],'',$line));
        $key   = $matches[2];
      } else {
        // Do some guesswork as to the key and the value
        $explode = explode(': ', $line);
        $key     = trim(array_shift($explode));
        $value   = trim(implode(': ', $explode));
        $this->checkKeysInValue($value);
      }
      // Set the type of the value.  Int, string, etc
      $value = $this->_toType($value);
      if ($key === '0') $key = '__!YAMLZero';
      $array[$key] = $value;
    } else {
      $array = array ($line);
    }
    return $array;

  }


  private function returnArrayElement ($line) {
     if (strlen($line) <= 1) return array(array()); // Weird %)
     $array = array();
     $value   = trim(substr($line,1));
     $value   = $this->_toType($value);
     if ($this->isArrayElement($value)) {
       $value = $this->returnArrayElement($value);
     }
     $array[] = $value;
     return $array;
  }


  private function nodeContainsGroup ($line) {
    $symbolsForReference = 'A-z0-9_\-';
    if (strpos($line, '&') === false && strpos($line, '*') === false) return false; // Please die fast ;-)
    if ($line[0] == '&' && preg_match('/^(&['.$symbolsForReference.']+)/', $line, $matches)) return $matches[1];
    if ($line[0] == '*' && preg_match('/^(\*['.$symbolsForReference.']+)/', $line, $matches)) return $matches[1];
    if (preg_match('/(&['.$symbolsForReference.']+)$/', $line, $matches)) return $matches[1];
    if (preg_match('/(\*['.$symbolsForReference.']+$)/', $line, $matches)) return $matches[1];
    if (preg_match ('#^\s*<<\s*:\s*(\*[^\s]+).*$#', $line, $matches)) return $matches[1];
    return false;

  }

  private function addGroup ($line, $group) {
    if ($group[0] == '&') $this->_containsGroupAnchor = substr ($group, 1);
    if ($group[0] == '*') $this->_containsGroupAlias = substr ($group, 1);
    //print_r ($this->path);
  }

  private function stripGroup ($line, $group) {
    $line = trim(str_replace($group, '', $line));
    return $line;
  }
}

// This is for loading the graphics.

function get_fav_icon() {
	$output = <<<'EOF'
AAABAAEAEBAAAAEAIABoBAAAFgAAACgAAAAQAAAAIAAAAAEAIAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA
AAAAAAAAqWVHAAAAAAAAAAAAp2FDM6ZfQaqvb1HzvYlr/72Jav+ub1HzpV5Ap6dhQzMAAAAAAAAAAKhl
RwAAAAAAAAAAAAAAAACfVjgRrGpMnMWWeP/w5MT////p////5P///t7////j//DjxP/Flnj/q2lLk6Nc
PhIAAAAAqGVHAAAAAACcTjEWs3hatuHJqv///+n//v7e/+rZuf/UspP/wZFy/8mfgP/v4sL////m/93D
pP+ydVetpF5AEAAAAACPNhkDsHJUkuHKq////+L/4cqq/8COb/+1elz/wI9x/86piv+2fF7/sXRV//v5
2f///+X/3cOk/6pnSZMAAAAArGlLLMaZev/068v/0quL/8ykhf/fxqf/8unJ//r21v///+H/5tOz/6dh
Q//9+9v////h////4//ElXf/pl9BMq1sTZTs27v/0NG5/9DPtv/8+9z///7e////3////9/////l/9i4
mf/ElXf////m////3////+L/7+LD/6RcPqWucFLd///k//7/3/+Pj4L/6+vO////4P///9/////f////
6f+4gGH/5M+w//793f/489P/+fTU////5/+tbU/vuIBi/v//6f///+H/7ezP/4qXkv/T59j/7fvk////
3////+n/tXtd/8+piv/VtJb/wpN1/76Mbf/9+9v/vIhp/biAYf3//+n////f////4v+EvtL/SYmp/3nE
4/+o4vD/5vjl/+/fv//gyKn/59S1//Hmxv/Sro///fzc/7yHaf2vcFLd///k////3////97/1/Po/0Wt
4/9Hi67/TrPm/2/N9//i9uX////h////4v/699f/9e3N////5v+tbU/trWxOk+nYuf///+P////f////
3v+N1vD/SbLo/0mOsf9ZuOX/i9bx/7/o6P///97///7f////4f/v4sL/pV0/pKtpSynFl3j////j////
3////9//+f3g/3LN9f9PufD/SY6x/02z5/9Ovvf/ndzu//3/4P///+X/w5N1/6VeQC+WRCYDsXRVk+DH
qP///+H////f////3//s+OL/jNfx/2PK+v9KmcH/Tqzb/0668f+k3+7/4MSk/6tnSJYAAAAAAAAAAJZE
Jha1e1244Meo////4////+P////f//3+4P/N7eb/c9D5/0iw5v9LsOP/Zbzl/4KbqOmjXD8OAAAAAAAA
AAAAAAAAlkQmFrF0VZTFl3j/6ti5////5P///+n////p/+z85/+pwsT/Zp+//0658P9Pv/j/UMH5qVK/
9gKpZUcAAAAAAAAAAACWRCcDq2lLKa1sTpSvcFLeuIBh/biAYf6vb1Hdq2tOkpZ2ais+0v8OUcD3MlHA
+EtRwPgE+B8AAOAHAADAAwAAgAEAAIABAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAACAAQAAgAEAAMAD
AADgAQAA+B8AAA==
EOF;
	return base64_decode($output);
}
function get_header() {
	$output = <<<'EOF'
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
YII=
EOF;
	return base64_decode($output);
}
