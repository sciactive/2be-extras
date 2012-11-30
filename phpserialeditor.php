<?php
/**
 * A serialized PHP value editor.
 * 
 * This file helps edit values from a Pines database, which are often
 * stored as serialized PHP.
 *
 * Pines - an Enterprise PHP Application Framework
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
 * Hunter can be contacted at hunter@sciactive.com
 *
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 * @version 1.0
 */

// Set this to true if you don't trust your users. (And you most likely shouldn't!)
$secure_mode = true;
// This determined if you have the PECL YAML plugin installed.
// For instructions, see http://code.google.com/p/php-yaml/wiki/InstallingWithPecl
$yaml_works = function_exists('yaml_emit');

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
						$output = yaml_emit($value);
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
						$value = yaml_parse($_REQUEST['value']);
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
		<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.6.4/jquery.min.js"></script>
		<style type="text/css">
			body {
				font: 12pt Arial;
				color: #000;
			}
			textarea {
				width: 100%;
			}
			#diff_container {
				width: 100%;
				overflow: auto;
				border: 1px solid black;
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
				var serialized = $("#serialized").bind("change keyup", function(){
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
				var editor = $("#editor").bind("change keyup", function(){
					if (updating)
						return;
					$.post("", {type: "exported", "value": editor.val(), "language": $("input[name=language]:checked").val()}, function(data){
						updating = true;
						output.val(data);
						diff.html(WDiffString(pretty_php_serialized(original), pretty_php_serialized(data)));
						updating = false;
					});
				});
				serialized.change();
			});

			function pretty_php_serialized(serialized) {
				while (serialized.match(/\{[^\n]/))
					serialized = serialized.replace(/\{([^\n])/g, "{\n$1");
				while (serialized.match(/\}[^\n]/))
					serialized = serialized.replace(/\}([^\n])/g, "}\n$1");
				while (serialized.match(/[^\n]\}/))
					serialized = serialized.replace(/([^\n])\}/g, "$1\n}");
				while (serialized.match(/\;[^\n]/))
					serialized = serialized.replace(/\;([^\n])/g, ";\n$1");
				while (serialized.match(/\{\n\}/))
					serialized = serialized.replace(/\{\n\}/g, "{}");
				var cur_indent = 1;
				var cur_entry_index = false;
				var lines = serialized.split("\n");
				serialized = "";
				for (var i=0; i<lines.length; i++) {
					var is_a_closer = lines[i].charAt(0) == "}";
					if (is_a_closer) {
						cur_indent--;
						serialized += Array(cur_indent).join("    ")+lines[i]+"\n";
					} else {
						if (cur_entry_index)
							serialized += Array(cur_indent).join("    ")+lines[i];
						else
							serialized += lines[i]+"\n";
						cur_entry_index = !cur_entry_index;
					}
					if (lines[i].charAt(lines[i].length-1) == "{")
						cur_indent++;
				}
				return serialized;
			}
			
			function do_example() {
				var example = 'a:4:{i:0;a:15:{s:6:"entity";a:3:{i:0;s:22:"pines_entity_reference";i:1;i:18067;i:2;s:17:"com_sales_'
				+'product";}s:3:"sku";s:10:"GZS1100001";s:6:"serial";s:9:"GZA103306";s:8:"delivery";s:8:"in-store";s:8:"quantity";'
				+'i:1;s:5:"price";d:119.98999999999999;s:8:"discount";s:0:"";s:11:"salesperson";a:3:{i:0;s:22:"pines_entity_refere'
				+'nce";i:1;i:105662;i:2;s:4:"user";}s:3:"esp";s:13:"439007ac2dfae";s:10:"line_total";d:119.98999999999999;s:4:"fee'
				+'s";d:0;s:14:"stock_entities";a:1:{i:0;a:3:{i:0;s:22:"pines_entity_reference";i:1;i:173989;i:2;s:15:"com_sales_st'
				+'ock";}}s:10:"commission";d:9.5991999999999997;s:23:"returned_stock_entities";a:1:{i:0;a:3:{i:0;s:22:"pines_entit'
				+'y_reference";i:1;i:173989;i:2;s:15:"com_sales_stock";}}s:17:"returned_quantity";i:1;}i:1;a:15:{s:6:"entity";a:3:'
				+'{i:0;s:22:"pines_entity_reference";i:1;i:18067;i:2;s:17:"com_sales_product";}s:3:"sku";s:10:"GZS1100001";s:6:"se'
				+'rial";s:9:"GZA103307";s:8:"delivery";s:8:"in-store";s:8:"quantity";i:1;s:5:"price";d:49.990000000000002;s:8:"dis'
				+'count";s:0:"";s:11:"salesperson";a:3:{i:0;s:22:"pines_entity_reference";i:1;i:105662;i:2;s:4:"user";}s:3:"esp";s'
				+':13:"8b8dbdc6f973d";s:10:"line_total";d:49.990000000000002;s:4:"fees";d:0;s:14:"stock_entities";a:1:{i:0;a:3:{i:'
				+'0;s:22:"pines_entity_reference";i:1;i:173987;i:2;s:15:"com_sales_stock";}}s:10:"commission";d:3.9992000000000001'
				+';s:23:"returned_stock_entities";a:1:{i:0;a:3:{i:0;s:22:"pines_entity_reference";i:1;i:173987;i:2;s:15:"com_sales'
				+'_stock";}}s:17:"returned_quantity";i:1;}i:2;a:15:{s:6:"entity";a:3:{i:0;s:22:"pines_entity_reference";i:1;i:1266'
				+'29;i:2;s:17:"com_sales_product";}s:3:"sku";s:14:"TSASUSDOCK0001";s:6:"serial";s:12:"B8OKAS062523";s:8:"delivery"'
				+';s:9:"warehouse";s:8:"quantity";i:1;s:5:"price";d:298;s:8:"discount";s:0:"";s:11:"salesperson";a:3:{i:0;s:22:"pi'
				+'nes_entity_reference";i:1;i:105662;i:2;s:4:"user";}s:3:"esp";s:13:"8b8dbdc6f973d";s:10:"line_total";d:298;s:4:"f'
				+'ees";d:0;s:14:"stock_entities";a:1:{i:0;a:3:{i:0;s:22:"pines_entity_reference";i:1;i:152182;i:2;s:15:"com_sales_'
				+'stock";}}s:16:"shipped_entities";a:1:{i:0;a:3:{i:0;s:22:"pines_entity_reference";i:1;i:152182;i:2;s:15:"com_sale'
				+'s_stock";}}s:23:"returned_stock_entities";a:1:{i:0;a:3:{i:0;s:22:"pines_entity_reference";i:1;i:152182;i:2;s:15:'
				+'"com_sales_stock";}}s:17:"returned_quantity";i:1;}i:3;a:15:{s:6:"entity";a:3:{i:0;s:22:"pines_entity_reference";'
				+'i:1;i:126628;i:2;s:17:"com_sales_product";}s:3:"sku";s:13:"TSASUSPAD0001";s:6:"serial";s:12:"B7OKAS370654";s:8:"'
				+'delivery";s:9:"warehouse";s:8:"quantity";i:1;s:5:"price";d:799;s:8:"discount";s:0:"";s:11:"salesperson";a:3:{i:0'
				+';s:22:"pines_entity_reference";i:1;i:105662;i:2;s:4:"user";}s:3:"esp";s:13:"439007ac2dfae";s:10:"line_total";d:7'
				+'99;s:4:"fees";d:0;s:14:"stock_entities";a:1:{i:0;a:3:{i:0;s:22:"pines_entity_reference";i:1;i:170789;i:2;s:15:"c'
				+'om_sales_stock";}}s:16:"shipped_entities";a:1:{i:0;a:3:{i:0;s:22:"pines_entity_reference";i:1;i:170789;i:2;s:15:'
				+'"com_sales_stock";}}s:23:"returned_stock_entities";a:1:{i:0;a:3:{i:0;s:22:"pines_entity_reference";i:1;i:170789;'
				+'i:2;s:15:"com_sales_stock";}}s:17:"returned_quantity";i:1;}}';
				$("#serialized").val(example).change();
			}
		</script>
	</head>
	<body>
		<div style="float: right;">
			<a href="http://sourceforge.net/projects/pines" target="_blank">
				<img src="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>?type=header" alt="Pines Logo" style="border: none;" />
			</a>
		</div>
		<h1 style="margin: 0; font-size: 1.2em; font-weight: bold;">Serialized PHP Editor</h1>
		<div style="margin: .4em 0">
			Choose a language to use for editing:
			<label><input type="radio" name="language" value="yaml" <?php if ($yaml_works) { ?>checked="checked"<?php } else { ?>disabled="disabled"<? } ?> /> YAML</label>
			<label><input type="radio" name="language" value="json" <?php if (!$yaml_works) { ?>checked="checked"<?php } ?> /> JSON</label>
			<label><input type="radio" name="language" value="php" <?php if ($secure_mode) { ?>disabled="disabled"<?php } ?> /> PHP</label>
			<?php if (!$yaml_works) { ?>
			<div><small>It appears YAML is not installed on your server. You can find instructions <a href="http://code.google.com/p/php-yaml/wiki/InstallingWithPecl" target="_blank">here</a>.</small></div>
			<?php } if ($secure_mode) { ?>
			<div><small>PHP language mode has been disabled for security reasons.</small></div>
			<?php } ?>
		</div>
		<div style="margin: .4em 0">
			<div style="width: 48%; float: left;">
				Paste in a PHP serialized value here: <small>(<a href="javascript:void(0);" onclick="do_example();">example</a>)</small><br />
				<textarea rows="4" cols="30" id="serialized" style="height: 50px;"></textarea>
			</div>
			<div style="width: 48%; float: right;">
				The new serialized value will appear here after you edit it:<br />
				<textarea rows="4" cols="30" id="output" style="height: 50px;"></textarea>
			</div>
			<div style="clear: both; height: 0; line-height: 0;">&nbsp;</div>
		</div>
		<div style="width: 48%; float: left;">
			Then edit the value here:<br />
			<textarea rows="20" cols="30" id="editor" style="height: 475px;"></textarea>
		</div>
		<div style="width: 48%; float: right;">
			A colored diff will show here:<br />
			<div id="diff_container" style="height: 475px;">
				<div id="diff"></div>
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

function get_fav_icon() {
	$output = <<<'EOF'
AAABAAEAEBAAAAEAIABoBAAAFgAAACgAAAAQAAAAIAAAAAEAIAAAAAAAAAAAAAAAAAAAAAAAAAAA
AAAAAABubm4AAAAAAAAAAABnTmUCVVhWM1JWUqlaZVv1Z35o/2mDa/9cbV3/RUtG0iwwLGktKS4K
AAAAAAAAAAAhISEAAAAAAAAAAABlUWMQXmZfkGWBZv95t33/hs2L/5XMmP6QxZT+jMaQ/nu8f/9w
nHP/R1ZI1CopKjsAAAAAAAAAAAAAAABxWW8NZHBlpGWbaf9nwG7/lMiX/trk2v/t7e7/6+nr/9zc
3P+tv6//X59j/2u2cP9PalHkKygqOwAAAACPhI4Cb3dvbVqQXv9Ptlb/m8ye/vX29v/a7dv/pdeo
/4rLj/+047f/8Pbw/97i3v5Ul1j+W7Fh/0FUQtQwKzAKcGxwDWN9Ze8+rkX/hceK/ufw6P9ruXH/
TKNS/42ykP+ZsZr/VYFX/0ijTv/H58j/4ufi/kOfSv9QkFP/KS8paYSMhVNDhUf/SrZR/s/l0P9J
rE//Wa9f//L08v/z+vP/7fbu//////9+kX//MZg3/53QoP9komf/Mag5/z9JQNKFj4aVHYgk/zuv
Qv8oojD/arxv/+/17/+d0KD/MaE5/yKUKf9ovm7/4/Xk/4OihP8bnST/NaY9/x+nJ/4+YkD/fIZ9
sAiMEP8GnA//C5wV/9Tp1f9mtmv/FJ0d/8LhxP+/yb//F5Eg/xihIf+/3cP/K4gx/wWcDv8FoA/+
NW45/4WPhqgAgwj/AJkI/xugI/9HrE7/B5YJ/9Po1f/Q5dH/3urf/67SsP8Algf/DZwW/yecL/8A
lQf/AJsG/jVqOf+Zo5p8EXoX/wCaCP4DmQ3/AJYH/x2jJf+p06z/Mpo5/4HMhv/e6d7/Lqg1/wCX
CP8BmAv/AJcJ/wCcBf9DXET1lZ2WKkZ/Sv8AmgT/BpoQ/wydFv8ToB3/QKtG/xKaG/9ZvmD/0eLT
/23Cdf8Pnhn/CZwT/wCaCv4RjRn/S1RLqbSjswZ4kXm5CYMQ/xijIf8rqjP+Mqw6/zevQP85sED/
S7hS/6jJqv9rwXL/L6s3/yOnLP4GnRD/OG47/1JYUzMAAAAAn5eeNk6DUe0YlyD/RLVL/1G5WP9Y
u17/XL5i/1m9X/+CwIb/Vblb/0y4U/4qqjP/I38p/1RjVZCRYo4CAAAAAP///wGal5pWT4RS7TGU
N/9kxmv/e82A/oDOhf9/zoX/hM6J/3TLev5GsU7/NIA5/1ZqV6R/XX0QAAAAALq6ugAAAAAA////
AZ2WnDZ6k3u5YIli/2GbZf9+t4L/icCN/3CqdP9Zjl3/Xntg72V0Zm2PZowNAAAAAAAAAAAAAAAA
urq6AAAAAAAAAAAAs6SyBpWdliqhp6J8kJSQqIeLh7COk46Vho6HU29sbw2di5sCAAAAAAAAAABx
cXEA+B8AAOAHAADAAwAAwAEAAIABAACAAAAAAAAAAAAAAAAAAAAAgAAAAIAAAACAAQAAwAEAAOAD
AADwDwAA/j8AAA==
EOF;
	return base64_decode($output);
}
function get_header() {
	$output = <<<'EOF'
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
09q0iQAAAABJRU5ErkJggg==
EOF;
	return base64_decode($output);
}

?>