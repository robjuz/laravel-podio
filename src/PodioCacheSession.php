<?php
/**
 * Created by IntelliJ IDEA.
 * User: stefanmdt
 * Date: 02.08.17
 * Time: 07:43
 */

namespace stefanmdt\LaravelPodio;


use Illuminate\Support\Facades\Cache;
use PodioOAuth;

class PodioCacheSession
{
    /**
     * Get oauth object from session, if present. We use $auth_type as
     * basis for the cache key.
     */
    public static function get($auth_type = null)
    {

        // If no $auth_type is set, just return empty
        // since we won't be able to find anything.
        if ( ! $auth_type) {
            return new PodioOauth();
        }

        $cache_key = "podio_cache_" . $auth_type['type'] . "_" . $auth_type['identifier'];

        // Check if we have a stored session
        if (Cache::store('file')->has($cache_key)) {

            // We have a session, create new PodioOauth object and return it
            $cached_value = Cache::store('file')->get($cache_key);

            return new PodioOAuth(
                $cached_value['access_token'],
                $cached_value['refresh_token'],
                $cached_value['expires_in'],
                array("type" => $cached_value['ref_type'], "id" => $cached_value['ref_id'])
            );
        }

        // Else return an empty object
        return new PodioOAuth();
    }

    /**
     * Store the oauth object in the session. We ignore $auth_type since
     * it doesn't work with server-side authentication.
     */
    public static function set($oauth, $auth_type = null)
    {
        $cache_key = "podio_cache_" . $auth_type['type'] . "_" . $auth_type['identifier'];

        // Save all properties of the oauth object in cache
        if (!empty($oauth->access_token) || !empty($oauth->refresh_token)) {

            $minutes = 28800; // 20 days (refresh token validity) (default value)
            if (!empty($oauth->expires_in)) {
                $minutes = $oauth->expires_in / 60 + 28800; // expiration time + 20 days (refresh token validity)
            }

            Cache::store('file')->put($cache_key, [
                'access_token' => $oauth->access_token,
                'refresh_token' => $oauth->refresh_token,
                'expires_in' => $oauth->expires_in,
                'ref_type' => $oauth->ref["type"],
                'ref_id' => $oauth->ref["id"],
            ], $minutes);

        // podio-php uses empty oauth objects to clear cache
        } else if (Cache::store('file')->has($cache_key)) {
            Cache::store('file')->forget($cache_key);
        }

    }
}