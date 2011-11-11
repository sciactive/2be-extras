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
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>
<title>PHP Slim Self Extractor</title>
<style type="text/css" media="all">
/* <![CDATA[ */
.wrapper {
	margin: 3em;
	font-family: sans;
	font-size: 80%;
}
.wrapper fieldset {
	border: 1px solid #040;
	-moz-border-radius: 10px;
}
.wrapper legend {
	padding: 0.5em 0.8em;
	border: 2px solid #040;
	color: #040;
	font-size: 120%;
	-moz-border-radius: 10px;
}
.wrapper label {
	display: block;
	text-align: right;
	margin-right: 60%;
}
.wrapper input {
	color: #040;
}
.wrapper .buttons {
	text-align: right;
}
/* ]]> */
</style>
</head>
<body>
<div class="wrapper">
<form action="" method="post">
<fieldset>
<legend>Slim Self Extractor</legend>
<p>Please enter the directory where you would like to extract the files stored in this Slim Archive. Leave this blank to use the current directory. If the directory does not exist, it will be created for you. Please do not try to use parent directories, they will not work. After the files are extracted, you will be redirected to the directory.</p>
<label>Directory: <input type="text" name="directory" value="" /></label><br />
<div class="buttons"><input type="submit" value="Extract and Run" name="submit" /> <input type="reset" value="Reset" name="reset" /></div>
</fieldset>
</form>
<p><small>This Slim Self Extractor was developed by Hunter Perrin as part of <a href="https://sourceforge.net/projects/pines/">Pines</a>.</small></p>
</div>
</body>
</html>
<?php
__halt_compiler();