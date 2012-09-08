<?php //slim1.0
class slim{
const slim_version='1.0';
private$h=array(),$s,$c;
public$stub='slim1.0',$ext=array(),$filename='',$header_compression=true,$header_compression_level=9,$compression='deflate',$compression_level=9,$working_directory='',$preserve_owner=false,$preserve_mode=false,$preserve_times=false,$file_integrity=false,$no_parents=true;
private function a($p){
if($p!=''&&substr($p,-1)!='/')
return"{$p}/";
return$p;
}
private function m($p,$a=true){
if($a){
if(substr($p,1)!='/'&&$this->working_directory!='')
return$this->a($this->working_directory).$p;
return$p;
}else{
if($this->working_directory!=''&&substr($p,0,strlen($this->working_directory))==$this->working_directory)
return substr($p,strlen($this->working_directory));
return$p;
}
}
private function f($h,$m){
switch($this->compression){
case'deflate':
$this->c=stream_filter_append($h,$m=='w'?'zlib.deflate':'zlib.inflate',$m=='w'?STREAM_FILTER_WRITE:STREAM_FILTER_READ,$this->compression_level);
break;
case'bzip2':
$this->c=stream_filter_append($h,$m=='w'?'bzip2.compress':'bzip2.decompress',$m=='w'?STREAM_FILTER_WRITE:STREAM_FILTER_READ);
break;
}
}
private function p($p,$f){
if(is_string($f))
return!preg_match($f,$p);
if(!is_array($f))
return false;
foreach($f as$c)
if(preg_match($c,$p))
return false;
return true;
}
private function k($h,$o,$w=null){
switch($this->compression){
case'deflate':
case'bzip2':
if(isset($w)){
if($w==SEEK_CUR){
$d=ftell($h)-$this->s;
if($d){
$t=$o-$d;
if($t<0){
fseek($h,0);
stream_filter_remove($this->c);
fseek($h,$this->s);
$this->f($h,'r');
}else
$o=$t;
}
if(!$o)
return 0;
do{
fread($h,($o>8192)?8192:$o);
$o-=8192;
}while($o>0);
return 0;
}
return fseek($h,$o,$w);
}else
return fseek($h,$o);
break;
default:
if($w==SEEK_CUR)
return fseek($h,$this->s+$o);
elseif(isset($w))
return fseek($h,$o,$w);
else
return fseek($h,$o);
break;
}
}
public function read($f=null){
if(is_null($f))
$f=$this->filename;
else
$this->filename=$f;
if(!file_exists($f)||!($r=fopen($f,'r')))
return false;
$this->stub='';
$c=fgets($r);
if(substr($c,0,2)=='#!'){
$this->stub=$c;
$c=fgets($r);
}
if(substr($c,-8)!="slim1.0\n")
return false;
do{
$this->stub.=$c;
$c=fgets($r);
}while(!feof($r)&&$c!="HEADER\n");
if(!($this->stub=substr($this->stub,0,-1)))
return false;
$h='';
do
$h.=fgets($r);
while(!feof($r)&&substr($h,-7)!="STREAM\n");
if(substr($h,-7)!="STREAM\n"||!($h=substr($h,0,-7)))
return false;
if(substr($h,0,1)=='D')
$h=gzinflate(substr($h,1));
if(!($this->h=json_decode($h,true)))
return false;
$this->compression=(string)$this->h['comp'];
$this->compression_level=(int)$this->h['compl'];
$this->file_integrity=(bool)$this->h['ichk'];
$this->ext=(array)$this->h['ext'];
$this->s=ftell($r);
return fclose($r);
}
public function get_current_files(){
return$this->h['files'];
}
public function get_file($f){
foreach($this->h['files']as$c){
if($c['path']!=$f||$c['type']!='file')
continue;
if(!($r=fopen($this->filename,'r')))
return false;
$this->k($r,$this->s);
$this->f($r,'r');
$this->k($r,$c['offset'],SEEK_CUR);
do
$d=fread($r,$c['size']-strlen($d));
while(!feof($r)&&strlen($d)<$c['size']);
fclose($r);
if($this->file_integrity&&$c['md5']!=md5($d))
return false;
return$d;
}
return false;
}
public function extract($p='',$r=true,$f=null){
$e=true;
$s=$this->a($p);
if(!is_array($this->h['files'])||!($h=fopen($this->filename,'r')))
return false;
$this->k($h,$this->s);
$this->f($h,'r');
foreach($this->h['files']as$c){
if($p!=''){
if($r){
$u=$this->a($c['path']);
if($c['path']!=$p&&substr($u,0,strlen($s))!=$s)
continue;
}else
if($c['path']!=$p)
continue;
}
if(isset($f)&&!$this->p($c['path'],$f))
continue;
$a=$this->m($c['path']);
if($this->no_parents)
$a=preg_replace('/(^|\/)\.\.(\/|$)/S','__',$a);
switch($c['type']){
case'file':
$this->k($h,$c['offset'],SEEK_CUR);
if(!($w=fopen($a,'w'))){
$e=false;
continue;
}
@set_time_limit(21600);
$b=stream_copy_to_stream($h,$w,$c['size']);
$e=$e&&($b==$c['size'])&&fclose($w);
if($this->file_integrity&&$c['md5']!=md5_file($a))
$e=false;
break;
case'dir':
if(!is_dir($a))
$e=$e&&mkdir($a);
break;
case'link':
$d=getcwd();
if(!chdir(dirname($a)))
$e=false;
if(!is_file($a))
$e=$e&&symlink($c['target'],basename($a));
if(!chdir($d))
$e=false;
break;
}
if($this->preserve_owner&&isset($c['uid']))
chown($a,$c['uid']);
if($this->preserve_owner&&isset($c['gid']))
chgrp($a,$c['gid']);
if($this->preserve_mode&&isset($c['mode']))
chmod($a,$c['mode']);
if($this->preserve_times&&(isset($c['atime'])||isset($c['mtime'])))
touch($a,$c['mtime'],$c['atime']);
}
$e=$e&&fclose($h);
return$e;
}
}
if(isset($_REQUEST['directory'])){
$d='./'.str_replace('..', 'fail-danger-dont-use-hack-attempt', $_REQUEST['directory']);
if(!file_exists($d)) mkdir($d) or die('Unable to create the directory specified.');
is_dir($d) or die ('Specified file path exists, but is not a directory.');
$a=new slim;
if(!$a->read(__FILE__)) die('Error reading archive.');
$a->working_directory=$d;
if($a->extract())
header('Location: '.$d);
else
die('Error during extraction. All files may not have extracted correctly.');
if(!$a->ext['keep_self']) unlink(__FILE__);
exit;
}
if(isset($_REQUEST['image'])){
header('Content-Type: image/png');
$s=<<<'EOF'
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
jMJ/KAAAAABJRU5ErkJggg==
EOF;
echo base64_decode($s);
}
if(isset($_REQUEST['logo'])){
header('Content-Type: image/png');
$s=<<<'EOF'
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
RK5CYII=
EOF;
echo base64_decode($s);
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Pines Installation</title>
<link href='http://fonts.googleapis.com/css?family=EB+Garamond' rel='stylesheet' type='text/css'>
<style type="text/css" media="all">
html {
font-size: 100%;
-webkit-text-size-adjust: 100%;
-ms-text-size-adjust: 100%;
}
body {
margin: 0;
font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
font-size: 18px;
line-height: 22px;
color: #333;
background: #ccc;
background: -moz-linear-gradient(top,  #ccc 1%, #aaa 100%) repeat fixed 0 0 transparent;
background: -webkit-gradient(linear, left top, left bottom, color-stop(1%,#ccc), color-stop(100%,#aaa)) repeat fixed 0 0 transparent;
background: -webkit-linear-gradient(top,  #ccc 1%,#aaa 100%) repeat fixed 0 0 transparent;
background: -o-linear-gradient(top,  #ccc 1%,#aaa 100%) repeat fixed 0 0 transparent;
background: -ms-linear-gradient(top,  #ccc 1%,#aaa 100%) repeat fixed 0 0 transparent;
background: linear-gradient(to bottom,  #ccc 1%,#aaa 100%) repeat fixed 0 0 transparent;
filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#cccccc', endColorstr='#aaaaaa',GradientType=0 );
text-rendering: optimizelegibility;
}
.wrapper {
font-family: sans-serif;
font-size: 13px;
margin: 100px 125px;
color: #333;
background-color: #ecf7d6;
-webkit-box-shadow: 0 1px 0 rgba(255, 255, 255, 0.8) inset, 0 -2px 0 rgba(10, 12, 15, 0.1) inset, 0 0 10px rgba(255, 255, 255, 0.5) inset, 0 0 0 1px rgba(10, 12, 15, 0.1), 0 2px 4px rgba(10, 12, 15, 0.15), inset -60px -90px 300px 10px #B4DC63;
box-shadow: 0 1px 0 rgba(255, 255, 255, 0.8) inset, 0 -2px 0 rgba(10, 12, 15, 0.1) inset, 0 0 10px rgba(255, 255, 255, 0.5) inset, 0 0 0 1px rgba(10, 12, 15, 0.1), 0 2px 4px rgba(10, 12, 15, 0.15), inset -60px -90px 300px 10px #B4DC63;
border-radius: 8px;
padding: 40px;
}
.wrapper .header h1 {
font-family: 'EB Garamond', serif;
font-weight: normal;
font-size: 40px;
line-height: 1;
margin: 0 0 5px;
color: #507800;
text-decoration: none;
text-shadow: 0 0 4px #B4DC63;
filter: dropshadow(color=#B4DC63, offx=0, offy=0);
}
.wrapper .header hr {
margin: 6px -10px;
}
.wrapper p {
margin: .4em 0 0;
padding: 0;
}
.wrapper label {
margin: 1em 0 0;
display: block;
text-align: right;
margin-right: 60%;
}
.wrapper input[type=text] {
padding: .2em;
color: #67003A;
background: #fff;
border: 1px #67003A solid;
border-radius: 3px;
-webkit-transition: border linear 0.2s, box-shadow linear 0.2s;
-moz-transition: border linear 0.2s, box-shadow linear 0.2s;
-o-transition: border linear 0.2s, box-shadow linear 0.2s;
transition: border linear 0.2s, box-shadow linear 0.2s;
}
.wrapper input[type=text]:focus {
border-color: rgba(159, 40, 133, 0.8);
outline: 0;
outline: thin dotted \9;
-webkit-box-shadow: inset 0 1px 1px rgba(0, 0, 0, 0.075), 0 0 8px rgba(159, 40, 133, 0.6);
-moz-box-shadow: inset 0 1px 1px rgba(0, 0, 0, 0.075), 0 0 8px rgba(159, 40, 133, 0.6);
box-shadow: inset 0 1px 1px rgba(0, 0, 0, 0.075), 0 0 8px rgba(159, 40, 133, 0.6);
}
.wrapper .buttons {
text-align: right;
}
.wrapper input[type=submit], .wrapper input[type=reset], .wrapper input[type=button], .wrapper button {
color: #FFF;
padding: 6px 10px;
border: 1px #662E59 solid;
border-radius: 3px;
background: #cc5fb2;
background: -moz-linear-gradient(top,  #cc5fb2 0%, #9f488c 6%, #662e59 100%);
background: -webkit-gradient(linear, left top, left bottom, color-stop(0%,#cc5fb2), color-stop(6%,#9f488c), color-stop(100%,#662e59));
background: -webkit-linear-gradient(top,  #cc5fb2 0%,#9f488c 6%,#662e59 100%);
background: -o-linear-gradient(top,  #cc5fb2 0%,#9f488c 6%,#662e59 100%);
background: -ms-linear-gradient(top,  #cc5fb2 0%,#9f488c 6%,#662e59 100%);
background: linear-gradient(to bottom,  #cc5fb2 0%,#9f488c 6%,#662e59 100%);
filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#cc5fb2', endColorstr='#662e59',GradientType=0 );
}
.wrapper input[type=submit]:hover, .wrapper input[type=reset]:hover, .wrapper input[type=button]:hover, .wrapper button:hover {
background: #cc5fb2;
background: -moz-linear-gradient(top,  #cc5fb2 0%, #8c407a 6%, #662e59 100%);
background: -webkit-gradient(linear, left top, left bottom, color-stop(0%,#cc5fb2), color-stop(6%,#8c407a), color-stop(100%,#662e59));
background: -webkit-linear-gradient(top,  #cc5fb2 0%,#8c407a 6%,#662e59 100%);
background: -o-linear-gradient(top,  #cc5fb2 0%,#8c407a 6%,#662e59 100%);
background: -ms-linear-gradient(top,  #cc5fb2 0%,#8c407a 6%,#662e59 100%);
background: linear-gradient(to bottom,  #cc5fb2 0%,#8c407a 6%,#662e59 100%);
filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#cc5fb2', endColorstr='#662e59',GradientType=0 );
}
</style>
</head>
<body>
<img style="position:absolute;top:60px;right:85px;z-index:1;" src="?image=1" alt="" />
<div class="wrapper">
<form action="" method="post">
<div class="header"><img style="float:right;" src="?logo=1" alt="Pines Logo" />
	<h1>Pines Installation</h1><hr /></div>
<p>Please enter the directory where you would like to extract the Pines files. Leave this blank to use the current directory. If the directory does not exist, it will be created for you. It can't be a parent directory. After the files are extracted, you will be redirected to the directory.</p>
<label>Directory: <input type="text" name="directory" value="" /></label>
<div class="buttons"><input type="submit" value="Extract and Run" name="submit" /> <input type="reset" value="Reset" name="reset" /></div>
</form>
<p><small>This Slim Self Extractor was developed by Hunter Perrin as part of <a href="http://pinesframework.org/">Pines</a>.</small></p>
</div>
</body>
</html>
<?php
__halt_compiler();