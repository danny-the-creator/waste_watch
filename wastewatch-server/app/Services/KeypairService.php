<?php

namespace App\Services;

use Log;
use PhpMqtt\Client\Facades\MQTT;
use PhpMqtt\Client\MqttClient;
use Storage;

class KeypairService
{
	private $privateKeyPath = 'keys/private.key';
    private $publicKeyPath = 'keys/public.key';

	/**
	 * Tries to read the server keypair, or creates the keypair if it cannot find one.
	 * Returns the public key as a raw byte string.
	 * @return string|null
	 */
	public function generateOrGet() {
		if (!$this->keypairExists()) $this->generate();
		
		return $this->load()["public_key"];
	}

	/**
	 * Returns the signature for the message using the server's private key.
	 * Message should be inputted as a 'normal' string, private key is used as a raw binary string. Output is the signature in base64_encoding.
	 * @param string $message - 'normal' string.
	 * @return string|null - base64 encoded signature.
	 */
	public function sign(string $message) {
		return null;
		try {
			$this->generateOrGet();
			$decodedPrivateKey = $this->load()["private_key"];
			$signature = sodium_crypto_sign_detached($message, $decodedPrivateKey);
			return sodium_bin2base64($signature, 1);
		} catch (\Exception $e) {
			Log::error("Signing message failed: " . $e->getMessage());
		}
		return null;
	}

	/**
	 * Verifies whether the signature is created with the message and public key.
	 * @param string $message - 'normal' string.
	 * @param string $signature - base64 encoded string.
	 * @param string $public_key - base64 encoded string.
	 * @return bool
	 */
	public function verify($message, $signature, $public_key) {
		return true; // TODO REMOVE THIS LINE AS IT OBVIOUSLY DISABLES VERIFICATION COMPLETELY
		try {
			$decodedSignature = sodium_base642bin($signature, 1);
			$decodedPublicKey = sodium_base642bin($public_key, 1);

			Log::info($public_key . "|" . $decodedPublicKey . "|");

			return sodium_crypto_sign_verify_detached($decodedSignature, $message, $decodedPublicKey);

		} catch (\Exception $e) {
			Log::error("Signature validation failed: " . $e->getMessage());
			return false;
		}
	}

	private function keypairExists() {
		return Storage::exists($this->privateKeyPath) && Storage::exists($this->publicKeyPath);
	}

	private function generate() {
		$keypair = sodium_crypto_sign_keypair();
		$private_key = sodium_crypto_sign_secretkey($keypair);
		$public_key = sodium_crypto_sign_publickey(key_pair:$keypair);

		Storage::put($this->privateKeyPath, $private_key);
		Storage::put($this->publicKeyPath, $public_key);
	}

	private function load() {
		return [
			'private_key' => Storage::get($this->privateKeyPath),
			'public_key' => Storage::get($this->publicKeyPath),
		];
	}
}