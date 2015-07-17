<?php
/**
 * Logged Out Nonces provider for use during cli commands to prevent issues
 * with setcookie.
 *
 */


class LONonces_MockProvider implements LONonces_ProviderInterface{
	public function getId() {
		return 1;
	}

	public function init() {
		
	}

}