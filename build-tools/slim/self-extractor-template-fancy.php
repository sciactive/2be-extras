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
if (isset($_REQUEST['directory'])) {

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
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>PHP Slim Self Extractor</title>
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
<div class="wrapper">
<form action="" method="post">
<div class="header"><h1>Slim Self Extractor</h1><hr /></div>
<p>Please enter the directory where you would like to extract the files stored in this Slim Archive. Leave this blank to use the current directory. If the directory does not exist, it will be created for you. Please do not try to use parent directories, they will not work. After the files are extracted, you will be redirected to the directory.</p>
<label>Directory: <input type="text" name="directory" value="" /></label>
<div class="buttons"><input type="submit" value="Extract and Run" name="submit" /> <input type="reset" value="Reset" name="reset" /></div>
</form>
<p><small>This Slim Self Extractor was developed by Hunter Perrin as part of <a href="http://pinesframework.org/">Pines</a>.</small></p>
</div>
</body>
</html>
<?php
__halt_compiler();