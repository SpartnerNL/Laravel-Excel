<?php namespace Maatwebsite\Excel\Classes;

use PHPExcel_Settings;
use Illuminate\Support\Facades\Config;
use PHPExcel_CachedObjectStorageFactory;

class Cache {

    /**
     * PHPExcel cache class
     * @var string
     */
    protected $class = 'PHPExcel_CachedObjectStorageFactory';

    /**
     * Available caching drivers
     * @var array
     */
    protected $available = array(
        'memory'     => 'cache_in_memory',
        'gzip'       => 'cache_in_memory_gzip',
        'serialized' => 'cache_in_memory_serialized',
        'igbinary'   => 'cache_igbinary',
        'discISAM'   => 'cache_to_discISAM',
        'apc'        => 'cache_to_apc',
        'memcache'   => 'cache_to_memcache',
        'temp'       => 'cache_to_phpTemp',
        'wincache'   => 'cache_to_wincache',
        'sqlite'     => 'cache_to_sqlite',
        'sqlite3'    => 'cache_to_sqlite3'
    );

    /**
     * The name of the config file
     * @var string
     */
    private $configName = 'excel::cache';

    /**
     * Cache constructor
     */
    public function __construct()
    {
        // Get driver and settings from the config
        $this->driver = Config::get($this->configName . '.driver', 'memory');
        $this->settings = Config::get($this->configName . '.settings', array());

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
        PHPExcel_Settings::setCacheStorageMethod($this->method, $this->settings);
    }

    /**
     * Set the right driver
     * @return void
     */
    public function findDriver()
    {
        $property = $this->detect();
        $this->method = constant($this->class . '::' . $property);
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
        switch ($this->driver)
        {
            case 'memcache':

                // Add extra memcache settings
                $this->settings = array_merge($this->settings, array(
                    'memcacheServer' => Config::get($this->configName . '.memcache.host', 'localhost'),
                    'memcachePort'   => Config::get($this->configName . '.memcache.port', 11211)
                ));

                break;

            case 'discISAM':

                // Add dir
                $this->settings = array_merge($this->settings, array(
                    'dir' => Config::get($this->configName . '.dir', storage_path('cache')),
                ));

                break;
        }
    }

    /**
     * Check if caching is enabled
     * @return boolean
     */
    public function isEnabled()
    {
        return Config::get($this->configName . '.enable', true) ? true : false;
    }
}