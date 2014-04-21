<?php namespace Pulpitum\Core;

/*
 * ---------------------------------------------
 * | Do not remove!!!!                         |
 * |                                           |
 * | @package   Pulpitum                       |
 * | @version   1.0                            |
 * | @develper  RGuedes                        |
 * | @author    Pulpitum Development Team      |
 * | @license   Free to all                    |
 * | @copyright 2014 Pulpitum                  |
 * | @link      https://github.com/pulpitum    |
 * ---------------------------------------------
 *
 * Example syntax:
 * use Settings (If you are using namespaces)
 *
 * Single dimension
 * set:         Settings::set('name', 'Phil'))
 * get:         Settings::get('name')
 * forget:      Settings::forget('name')
 * has:         Settings::has('name')
 *
 * Multi dimensional
 * set:         Settings::set('names' , array('firstName' => 'Phil', 'surname' => 'F'))
 * get:         Settings::get('names.firstName')
 * forget:      Settings::forget('names.surname'))
 * has:         Settings::has('names.firstName')
 *
 * Clear:
 * clear:        Settings::clear()
 */

use \Pulpitum\Core\Models\Helpers\Tools;
use Request;
use Eloquent;

/**
 * Class Settings
 * @package Dev3gntw\Core
 */
class Settings extends Eloquent {

    protected $table = 'settings';
    protected $primaryKey = 'id';

    /**
     * Create the Settings instance
     * @param interfaces\FallbackInterface $fallback
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Get a value and return it
     * @param  string $key String using dot notation
     * @param  boolean $force True = Use DB, False = Use Cache
     * @return Mixed             The value(s) found
     */
    public function get($key, $force=false, $default="")
    {
        /**
         * Setup cache key
         */
        $cacheKey = 'settings_' . md5($key);
                /**
         * Check if in cache
         */
        if (\Cache::has($cacheKey) && !$force) {
            return \Cache::get($cacheKey);
        }

        /**
         * Fetch from database
         */
        $settings = Settings::where('key', '=', $key)->first();

        /**
         * If a row was found, return the value
         */
        if (is_object($settings) && $settings->id) {

            /**
             * Return the data
             */
            if(Tools::is_json($settings->value)){
                $settings->value = json_decode($settings->value, true);
            }

            /**
             * Store in cache
             */
            \Cache::forever($cacheKey, $settings->value);

            return $settings->value;
        }

        return $default;
    }

    /**
     * Store Settings
     *
     * @param $key
     * @param $value
     * @return bool
     */
    public function set($key, $value)
    {

        if($key=="") return false;
        /**
         * Setup cache key
         */
        $cacheKey = 'settings_' . md5($key);

        /**
         * Fetch from database
         */
        $settings = Settings::where('key', '=', $key)->first();

        /**
         * If nothing was found, create a new object
         */
        if (!is_object($settings)) {
            $settings = new Settings;
        }

        /**
         * Set the values
         */
        $settings->key = $key;
        $settings->value = $value;
        $settings->website = 1;

        if($settings->save()){
            /**
             * Expire the cache
             */
            \Cache::forget($cacheKey);
            return true;
        }

        return false;
    }

    /**
     * Forget the value(s) currently stored
     * @param  mixed $deleteKey The value(s) to be removed (dot notation)
     * @return void
     */
    public function forget($deleteKey)
    {
        /**
         * Setup cache key
         */
        $cacheKey = 'settings_' . md5($deleteKey);

        /**
         * Fetch from database
         */
        $settings = Settings::where('key', '=', $deleteKey)->first();
        if (is_object($settings)) {
            $settings->delete();
        }        

        /**
         * Expire the cache
         */
        \Cache::forget($cacheKey);

        return true;
    }

    /**
     * Check to see if the value exists
     * @param  string  $searchKey The key to search for
     * @return boolean            True: found - False not found
     */
    public function has($key, $force=false)
    {
        /**
         * Setup cache key
         */
        $cacheKey = 'settings_' . md5($key);

        /**
         * Check if in cache
         */
        if (\Cache::has($cacheKey) && !$force) {
            return true;
        }

        /**
         * Fetch from database
         */
        $settings = Settings::where('key', '=', $key)->first();

        /**
         * If a row was found, return the value
         */
        if (is_object($settings) && $settings->id) {
            return true;
        }

        return false;
    }

    /**
     * Clears the JSON Config file
     */
    public function clear()
    {
        return true;
    }

}