<?php

class FajrConfig {

	protected static $config = null;

	public static function load() {
		if (self::isConfigured()) return;

		@$result = (include 'configuration.php');
		if ($result !== false && is_array($result)) {
			self::$config = $result;
		}
	}

	public static function isConfigured() {
		return (self::$config !== null);
	}

	public static function get($key) {
		if (!isset(self::$config[$key])) return null;
		return self::$config[$key];
	}
}

FajrConfig::load();