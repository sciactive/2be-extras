<?php
class slim{
const version='1.0';
private$so,$cf;
public$stub,$metadata,$filename,$compression='deflate',$compression_level=9,$working_directory='',$preserve_owner=false,$preserve_mode=false,$preserve_times=false,$file_integrity=false;
private function a_s($path){
if(substr($path,-1)!='/')
return"{$path}/";
return$path;
}
private function m_p($p,$awd=true){
if($awd){
if(substr($p,1)!='/'&&$this->working_directory!='')
return$this->a_s($this->working_directory).$p;
return$p;
} else {
if($this->working_directory!=''&&substr($p,0,strlen($this->working_directory))==$this->working_directory)
return substr($p,strlen($this->working_directory));
return$p;
}
}
private function a_f($h,$m){
switch($this->compression){
case'deflate':
$this->cf=stream_filter_append($h,$m=='w'?'zlib.deflate':'zlib.inflate',$m=='w'?STREAM_FILTER_WRITE:STREAM_FILTER_READ,$this->compression_level);
break;
case'bzip2':
$this->cf=stream_filter_append($h,$m=='w'?'bzip2.compress':'bzip2.decompress',$m=='w'?STREAM_FILTER_WRITE:STREAM_FILTER_READ);
break;
}
}
private function p_f($p,$f){
if(is_string($f))
return !preg_match($f,$p);
if(!is_array($f))
return false;
foreach($f as$cf){
if(preg_match($cf,$p))
return false;
}
return true;
}
private function fsk($h,$o,$w=null){
switch($this->compression){
case'deflate':
case'bzip2':
if(isset($w)){
if($w==SEEK_CUR){
$d=ftell($h)-$this->so;
if($d){
$t=$o-$d;
if($t<0){
fseek($h,0);
stream_filter_remove($this->cf);
fseek($h,$this->so);
$this->a_f($h,'r');
} else
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
} else
return fseek($h,$o);
break;
default:
if($w==SEEK_CUR)
return fseek($h,$this->so+$o);
else if(isset($w))
return fseek($h,$o,$w);
else
return fseek($h,$o);
break;
}
}
public function read($fn=null){
if(is_null($fn))
$fn=$this->filename;
else
$this->filename=$fn;
if(!file_exists($fn)||!($fh=fopen($fn,'r')))
return false;
$this->stub='';
$c=fgets($fh);
if(substr($c,0,2)=='#!'){
$this->stub=$c;
$c=fgets($fh);
}
if(substr($c,-8)!="slim1.0\n")
return false;
do{
$this->stub .= $c;
$c=fgets($fh);
}while(!feof($fh)&&$c!="HEADER\n");
if(!($this->stub=substr($this->stub,0,-1)))
return false;
$md='';
do $md .= fgets($fh);
while(!feof($fh)&&substr($md,-7)!="STREAM\n");
if(substr($md,-7)!="STREAM\n"||!($md=substr($md,0,-7)))
return false;
if(substr($md,0,1)=='D')
$md=gzinflate(substr($md,1));
if(!($this->metadata=json_decode($md,true)))
return false;
$this->compression=(string)$this->metadata['comp'];
$this->compression_level=(int)$this->metadata['compl'];
$this->file_integrity=(bool)$this->metadata['ichk'];
$this->so=ftell($fh);
return fclose($fh);
}
public function extract($p='',$r=true,$f=null){
$rt=true;
$ps=$this->a_s($p);
if(!is_array($this->metadata['files'])||!($fh=fopen($this->filename,'r')))
return false;
$this->fsk($fh,$this->so);
$this->a_f($fh,'r');
foreach($this->metadata['files']as$ce){
if($p!=''){
if($r){
$cps=$this->a_s($ce['path']);
if($ce['path']!=$p&&substr($cps,0,strlen($ps))!=$ps)
continue;
} else {
if($ce['path']!=$p)
continue;
}
}
if(isset($f)&&!$this->p_f($ce['path'],$f))
continue;
$cp=$this->m_p($ce['path']);
switch($ce['type']){
case'file':
$this->fsk($fh,$ce['offset'],SEEK_CUR);
if(!($fw=fopen($cp,'w'))){
$rt=false;
continue;
}
@set_time_limit(21600);
$bytes=stream_copy_to_stream($fh,$fw,$ce['size']);
$rt=$rt&&($bytes==$ce['size'])&&fclose($fw);
if($this->file_integrity&&$ce['md5']!=md5_file($cp))
$rt=false;
break;
case'dir':
$dp=$cp;
if(!is_dir($dp))
$rt=$rt&&mkdir($dp);
break;
case'link':
$cwd=getcwd();
if(!chdir(dirname($cp)))
$rt=false;
if(!is_file($cp))
$rt=$rt&&symlink($ce['target'],basename($cp));
if(!chdir($cwd))
$rt=false;
break;
}
if($this->preserve_owner&&isset($ce['uid']))
chown($cp,$ce['uid']);
if($this->preserve_owner&&isset($ce['gid']))
chgrp($cp,$ce['gid']);
if($this->preserve_mode&&isset($ce['mode']))
chmod($cp,$ce['mode']);
if($this->preserve_times&&(isset($ce['atime'])||isset($ce['mtime'])))
touch($cp,$ce['mtime'],$ce['atime']);
}
$rt=$rt&&fclose($fh);
return$rt;
}
}
?>