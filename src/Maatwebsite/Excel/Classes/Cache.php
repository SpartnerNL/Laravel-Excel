<?php namespace Maatwebsite\Excel\Classes;

use PHPExcel_Settings;
use PHPExcel_CachedObjectStorageFactory;
use PhpOffice\PhpSpreadsheet\Settings;
use Psr\SimpleCache\CacheInterface;
use Psr\SimpleCache\DateInterval;

class Cache implements CacheInterface
{

	/**
	 * PHPExcel cache class
	 * @var string
	 */
	protected $class = 'Maatwebsite\Excel\Classes\PHPExcel_CachedObjectStorageFactory';

	/**
	 * Available caching drivers
	 * @var array
	 */
	protected $available = [
		'memory' => 'cache_in_memory',
		'gzip' => 'cache_in_memory_gzip',
		'serialized' => 'cache_in_memory_serialized',
		'igbinary' => 'cache_igbinary',
		'discISAM' => 'cache_to_discISAM',
		'apc' => 'cache_to_apc',
		'memcache' => 'cache_to_memcache',
		'temp' => 'cache_to_phpTemp',
		'wincache' => 'cache_to_wincache',
		'sqlite' => 'cache_to_sqlite',
		'sqlite3' => 'cache_to_sqlite3'
	];

	/**
	 * The name of the config file
	 * @var string
	 */
	private $configName = 'excel.cache';

	/**
	 * Cache constructor
	 */
	public function __construct()
	{
		// Get driver and settings from the config
		$this->driver = config($this->configName . '.driver', 'memory');
		$this->settings = config($this->configName . '.settings', []);

		// Init if caching is enabled
		if ($this->isEnabled())
			$this->init();
	}

	/**
	 * Init the cache
	 * @return void
	 */
	public function init()
	{
		// Find the cache driver
		$this->findDriver();

		// Set the storage driver
		Settings::setCache($this);
	}

	/**
	 * Set the right driver
	 * @return void
	 */
	public function findDriver()
	{
		$property = $this->detect();
//		$this->method = $this->class . '::' . $property;
	}

	/**
	 * Detect the caching driver
	 * @return string $driver
	 */
	protected function detect()
	{
		// Add additional settings
		$this->addAdditionalSettings();

		// return the driver
		return isset($this->available[$this->driver]) ? $this->available[$this->driver] : reset($this->available);
	}

	/**
	 * Add additional settings for the current driver
	 * @return  void
	 */
	protected function addAdditionalSettings()
	{
		switch ($this->driver) {
			case 'memcache':

				// Add extra memcache settings
				$this->settings = array_merge($this->settings, [
					'memcacheServer' => config($this->configName . '.memcache.host', 'localhost'),
					'memcachePort' => config($this->configName . '.memcache.port', 11211)
				]);

				break;

			case 'discISAM':

				// Add dir
				$this->settings = array_merge($this->settings, [
					'dir' => config($this->configName . '.dir', storage_path('cache')),
				]);

				break;
		}
	}

	/**
	 * Check if caching is enabled
	 * @return boolean
	 */
	public function isEnabled()
	{
		return config($this->configName . '.enable', true) ? true : false;
	}

	/**
	 * Fetches a value from the cache.
	 *
	 * @param string $key The unique key of this item in the cache.
	 * @param mixed $default Default value to return if the key does not exist.
	 *
	 * @return mixed The value of the item from the cache, or $default in case of cache miss.
	 *
	 * @throws \Psr\SimpleCache\InvalidArgumentException
	 *   MUST be thrown if the $key string is not a legal value.
	 */
	public function get($key, $default = null)
	{
		// TODO: Implement get() method.
	}

	/**
	 * Persists data in the cache, uniquely referenced by a key with an optional expiration TTL time.
	 *
	 * @param string $key The key of the item to store.
	 * @param mixed $value The value of the item to store, must be serializable.
	 * @param null|int|DateInterval $ttl Optional. The TTL value of this item. If no value is sent and
	 *                                     the driver supports TTL then the library may set a default value
	 *                                     for it or let the driver take care of that.
	 *
	 * @return bool True on success and false on failure.
	 *
	 * @throws \Psr\SimpleCache\InvalidArgumentException
	 *   MUST be thrown if the $key string is not a legal value.
	 */
	public function set($key, $value, $ttl = null)
	{
		// TODO: Implement set() method.
	}

	/**
	 * Delete an item from the cache by its unique key.
	 *
	 * @param string $key The unique cache key of the item to delete.
	 *
	 * @return bool True if the item was successfully removed. False if there was an error.
	 *
	 * @throws \Psr\SimpleCache\InvalidArgumentException
	 *   MUST be thrown if the $key string is not a legal value.
	 */
	public function delete($key)
	{
		// TODO: Implement delete() method.
	}

	/**
	 * Wipes clean the entire cache's keys.
	 *
	 * @return bool True on success and false on failure.
	 */
	public function clear()
	{
		// TODO: Implement clear() method.
	}

	/**
	 * Obtains multiple cache items by their unique keys.
	 *
	 * @param iterable $keys A list of keys that can obtained in a single operation.
	 * @param mixed $default Default value to return for keys that do not exist.
	 *
	 * @return iterable A list of key => value pairs. Cache keys that do not exist or are stale will have $default as value.
	 *
	 * @throws \Psr\SimpleCache\InvalidArgumentException
	 *   MUST be thrown if $keys is neither an array nor a Traversable,
	 *   or if any of the $keys are not a legal value.
	 */
	public function getMultiple($keys, $default = null)
	{
		// TODO: Implement getMultiple() method.
	}

	/**
	 * Persists a set of key => value pairs in the cache, with an optional TTL.
	 *
	 * @param iterable $values A list of key => value pairs for a multiple-set operation.
	 * @param null|int|DateInterval $ttl Optional. The TTL value of this item. If no value is sent and
	 *                                      the driver supports TTL then the library may set a default value
	 *                                      for it or let the driver take care of that.
	 *
	 * @return bool True on success and false on failure.
	 *
	 * @throws \Psr\SimpleCache\InvalidArgumentException
	 *   MUST be thrown if $values is neither an array nor a Traversable,
	 *   or if any of the $values are not a legal value.
	 */
	public function setMultiple($values, $ttl = null)
	{
		// TODO: Implement setMultiple() method.
	}

	/**
	 * Deletes multiple cache items in a single operation.
	 *
	 * @param iterable $keys A list of string-based keys to be deleted.
	 *
	 * @return bool True if the items were successfully removed. False if there was an error.
	 *
	 * @throws \Psr\SimpleCache\InvalidArgumentException
	 *   MUST be thrown if $keys is neither an array nor a Traversable,
	 *   or if any of the $keys are not a legal value.
	 */
	public function deleteMultiple($keys)
	{
		// TODO: Implement deleteMultiple() method.
	}

	/**
	 * Determines whether an item is present in the cache.
	 *
	 * NOTE: It is recommended that has() is only to be used for cache warming type purposes
	 * and not to be used within your live applications operations for get/set, as this method
	 * is subject to a race condition where your has() will return true and immediately after,
	 * another script can remove it making the state of your app out of date.
	 *
	 * @param string $key The cache item key.
	 *
	 * @return bool
	 *
	 * @throws \Psr\SimpleCache\InvalidArgumentException
	 *   MUST be thrown if the $key string is not a legal value.
	 */
	public function has($key)
	{
		// TODO: Implement has() method.
	}
}