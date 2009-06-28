<?php
/**
 * phpWebSite Flickr
 *
 * Based on phpFlickr 2.2.0 written by Dan Coulter (dan@dancoulter.com) and released
 * under GNU Lesser General Public License (http://www.gnu.org/copyleft/lgpl.html).
 * For more information about phpFlickr, visit http://www.phpflickr.com.
 *
 * This file strips out unneeded functionality from phpFlickr and was made usable
 * by phpWebSite.
 *
 * @package FeaturedPhoto
 * @author Greg Meiste <greg.meiste+github@gmail.com>
 */

require_once 'HTTP/Request.php';

/**
 * The Flickr URL for REST requests.
 */
define('PHPWS_FLICKR_REST_URL', 'http://api.flickr.com/services/rest/');

class PHPWS_Flickr
{
    /**
     * Flickr API Key
     * @var string
     */
    var $_api_key;

    /**
     * Instance of HTTP_Request
     * @var HTTP_Request
     */
    var $_req;

    /**
     * Constructor
     *
     * @access   public
     * @param    string  API key (http://www.flickr.com/services/api/misc.api_keys.html) to use for requests
     */
    function PHPWS_Flickr($api_key)
    {
        $this->_api_key = $api_key;

        // All calls to the API are done via the POST method using the PEAR::HTTP_Request package.
        $this->_req = new HTTP_Request(PHPWS_FLICKR_REST_URL);
        $this->_req->setMethod(HTTP_REQUEST_METHOD_POST);
    }

    /**
     * Clean nodes
     *
     * @access   private
     * @param    array  The array to clean
     * @return   mixed  Results of the cleaning
     */
    function _clean_text_nodes($arr)
    {
        if (!is_array($arr) || (count($arr) == 0))
        {
            return $arr;
        }
        else if (count($arr) == 1 && array_key_exists('_content', $arr))
        {
            return $arr['_content'];
        }
        else
        {
            foreach ($arr as $key => $element)
            {
                $arr[$key] = $this->_clean_text_nodes($element);
            }
            return($arr);
        }
    }

    /**
     * Issue a request to Flickr. Will be pulled from cache if available.
     *
     * @access   private
     * @param    string Flickr API method (http://www.flickr.com/services/api)
     * @param    array  Array of arguments for the method
     * @return   mixed  Parsed response on success, PHPWS_Error on failure
     */
    function _request($method, $args=array())
    {
        // Clear POST data from previous request
        $this->_req->clearPostData();

        // Process arguments
        $args = array_merge(array('method'=>$method, 'format'=>'php_serial', 'api_key'=>$this->_api_key), $args);
        ksort($args);

        // Generate the cache key
        $cache_key = serialize($args);

        $response = PHPWS_Cache::get($cache_key);
        if (empty($response))
        {
            $auth_sig = '';
            foreach ($args as $key => $data)
            {
                $auth_sig .= $key . $data;
                $this->_req->addPostData($key, $data);
            }

            // Attempt to send the request to Flickr
            $this->_req->addHeader('Connection', 'Keep-Alive');
            if (!$this->_req->sendRequest())
            {
                return PHPWS_Error::get(FEATUREDPHOTO_FLICKR_CMD_SEND_FAIL, 'featuredphoto', 'PHPWS_Flickr::_request');
            }

            $response = $this->_req->getResponseBody();
            PHPWS_Cache::save($cache_key, $response);
        }

        $parsed_response = $this->_clean_text_nodes(unserialize($response));
        if ($parsed_response['stat'] == 'fail')
        {
            // Don't keep a failure in the cache
            PHPWS_Cache::remove($cache_key);

            $msg = $parsed_response['code'] . ' - ' . $parsed_response['message'];
            return PHPWS_Error::get(FEATUREDPHOTO_FLICKR_CMD_RESP_FAIL, 'featuredphoto', 'PHPWS_Flickr::_request', $msg);
        }

        return $parsed_response;
    }

    /**
     * Get the largest size that will fit in the specified dimensions.
     *
     * @access   public
     *
     * @param    int    Maximum width of a photo
     * @param    int    Maximum height of a photo
     * @return   string Photo size that best fits the dimensions
     */
    function best_photo_size($width, $height)
    {
        $sizes = array('large'     => 1024,
                       'medium'    => 500,
                       'small'     => 240,
                       'thumbnail' => 100,
                       'square'    => 75);

        $min = (($width < $height) ? $width : $height);
        foreach ($sizes as $str => $px)
        {
            if ($min > $px)
            {
                return $str;
            }
        }

        /* If we make it to here, return the smallest size possible. */
        return 'square';
    }

    /**
     * Build a photo's Flickr URL.
     *
     * @access   public
     * @note     This function will always return a URL, but it doesn't guarantee the file size exists!
     *
     * @param    array  Photo data returned from an API call
     * @param    string Desired photo size (square, thumbnail, small, medium, large, original)
     * @return   string URL to photo
     */
    function build_photo_url($photo, $size='medium')
    {
        $sizes = array('square'    => '_s',
                       'thumbnail' => '_t',
                       'small'     => '_m',
                       'medium'    => '',
                       'large'     => '_b',
                       'original'  => '_o');

        $size = strtolower($size);
        if (!array_key_exists($size, $sizes))
        {
            $size = 'medium';
        }

        $url = 'http://farm' . $photo['farm'] . '.static.flickr.com/' . $photo['server'] . '/' . $photo['id'] . '_';

        if ($size == 'original')
        {
            $url .= $photo['originalsecret'] . '_o' . '.' . $photo['originalformat'];
        }
        else
        {
            $url .= $photo['secret'] . $sizes[$size] . '.jpg';
        }

        return $url;
    }

    /**
     * Lookup the Flickr NSID using the username
     *
     * @access   public
     * @note     http://www.flickr.com/services/api/flickr.people.findByUsername.html
     * @return   mixed  User node on success, PHPWS_Error on failure
     */
    function people_findByUsername($username)
    {
        $resp = $this->_request('flickr.people.findByUsername', array('username'=>$username));
        return (PEAR::isError($resp) ? $resp : $resp['user']);
    }

    /**
     * Get a user's public photos
     *
     * @access   public
     * @note     http://www.flickr.com/services/api/flickr.people.getPublicPhotos.html
     * @return   mixed  Photos node on success, PHPWS_Error on failure
     */
    function people_getPublicPhotos($user_id, $extras=NULL, $per_page=NULL, $page=NULL)
    {
        if (is_array($extras))
        {
            $extras = implode(',', $extras);
        }

        $resp = $this->_request('flickr.people.getPublicPhotos',
                                array('user_id'=>$user_id, 'extras'=>$extras, 'per_page'=>$per_page, 'page'=>$page));
        return (PEAR::isError($resp) ? $resp : $resp['photos']);
    }

    /**
     * Retrieve a photo's information
     *
     * @access   public
     * @note     http://www.flickr.com/services/api/flickr.photos.getInfo.html
     * @return   mixed  Photo node on success, PHPWS_Error on failure
     */
    function photos_getInfo($photo_id, $secret=NULL)
    {
        $resp = $this->_request('flickr.photos.getInfo', array('photo_id'=>$photo_id, 'secret'=>$secret));
        return (PEAR::isError($resp) ? $resp : $resp['photo']);
    }

    /**
     * Retrieve a photoset's information
     *
     * @access   public
     * @note     http://www.flickr.com/services/api/flickr.photosets.getInfo.html
     * @return   mixed  Photoset node on success, PHPWS_Error on failure
     */
    function photosets_getInfo($photoset_id)
    {
        $resp = $this->_request('flickr.photosets.getInfo', array('photoset_id'=>$photoset_id));
        return (PEAR::isError($resp) ? $resp : $resp['photoset']);
    }

    /**
     * Get a listing of a user's photo sets
     *
     * @access   public
     * @note     http://www.flickr.com/services/api/flickr.photosets.getList.html
     * @return   mixed  Photosets node on success, PHPWS_Error on failure
     */
    function photosets_getList($user_id=NULL)
    {
        $resp = $this->_request('flickr.photosets.getList', array('user_id' => $user_id));
        return (PEAR::isError($resp) ? $resp : $resp['photosets']['photoset']);
    }

    /**
     * Get the photos in a photo set
     *
     * @access   public
     * @note     http://www.flickr.com/services/api/flickr.photosets.getPhotos.html
     * @return   mixed  Photoset node on success, PHPWS_Error on failure
     */
    function photosets_getPhotos($photoset_id, $extras=NULL, $privacy_filter=NULL, $per_page=NULL, $page=NULL)
    {
        $resp = $this->_request('flickr.photosets.getPhotos',
                                array('photoset_id'=>$photoset_id, 'extras'=>$extras, 'privacy_filter'=>$privacy_filter,
                                      'per_page'=>$per_page, 'page'=>$page));
        return (PEAR::isError($resp) ? $resp : $resp['photoset']);
    }

    /**
     * Get the friendly URL of the user's photos
     *
     * @access   public
     * @note     http://www.flickr.com/services/api/flickr.urls.getUserPhotos.html
     * @return   mixed  URL to user's photos on success, PHPWS_Error on failure
     */
    function urls_getUserPhotos($user_id=NULL)
    {
        $resp = $this->_request('flickr.urls.getUserPhotos', array('user_id'=>$user_id));
        return (PEAR::isError($resp) ? $resp : $resp['user']['url']);
    }
}

?>