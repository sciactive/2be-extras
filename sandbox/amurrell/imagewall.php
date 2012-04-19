<?php
header('Content-Type: application/json');

class Flickr { 
	private $apiKey = 'b3029ffc87db2e9ba6e122906d19185e'; 
 
	public function __construct() {
	} 
 
	public function search($query = null) { 
		$search = 'http://flickr.com/services/rest/?method=flickr.photos.search&api_key=' . $this->apiKey . '&text=' . urlencode($query) . '&per_page=100&format=php_serial'; 
		$result = file_get_contents($search); 
		$result = unserialize($result); 
		return $result; 
	} 
}

$photos = array();
$keyword = htmlspecialchars($_REQUEST['keyword']);

if(isset($keyword) and $keyword !="") {
	$Flickr = new Flickr; 
	$data = $Flickr->search($keyword); 
	$getphotos = array();
	$getlinks = array();

	foreach($data['photos']['photo'] as $photo) { 
		// the image URL becomes somthing like 
		// http://farm{farm-id}.static.flickr.com/{server-id}/{id}_{secret}.jpg  

		$getphotos[] = '<img src="http://farm' . $photo["farm"] . '.static.flickr.com/' . $photo["server"] . '/' . $photo["id"] . '_' . $photo["secret"] . '.jpg">'; 
		$getlinks [] = 'http://flickr.com/photo.gne?id='.$photo[id];
	}
	$photos[0] = $getphotos;
	$photos[1] = $getlinks;
	echo json_encode($photos);
}


?>
