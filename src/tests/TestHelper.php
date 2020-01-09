<?php

use Slim\Psr7\Request;

class TestHelper {
	public static function http(string $type,string $url,array $data = null) : array {
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $type);
		if($type === "POST" || $type === "PUT") {
			curl_setopt($ch, CURLOPT_POSTFIELDS,http_build_query($data));
		}
		$response = json_decode(curl_exec($ch), true);
		return $response;
	}
}