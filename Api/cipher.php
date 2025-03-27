<?php
//https://stackoverflow.com/questions/44259188/openssl-encrypt-256-cbc-raw-data-in-java
//https://stackoverflow.com/questions/44639366/how-to-send-a-secretkey-from-java-to-php-using-of-android-studio-phpstorm

class Crypto{
	private $key;
	private $iv;
	
	public function __construct()
    {
    }

	function decrypt($encrypted, $key, $iv){
		$cipher="AES-128-CBC";
		$original_plaintext = openssl_decrypt(base64_decode($encrypted), $cipher, $key, $options=OPENSSL_RAW_DATA, $iv);
		
	return $original_plaintext;
	}
	
	function encrypt($string, $key, $iv){
		$cipher="AES-128-CBC";
		$ciphertext_raw = openssl_encrypt($string, $cipher, $key, $options=OPENSSL_RAW_DATA, $iv);

	return $ciphertext_raw;
	}
}
?>