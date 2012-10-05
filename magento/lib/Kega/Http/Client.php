<?php

/**
 * Kega_Http_Client
 *
 */
class Kega_Http_Client
{
    protected $_requestUrl          = null;
    protected $_defaultRequestData  = null;

    // Default request settings.
    protected $_timeout             = 120;
    protected $_maximumRedirects    = 10;
    protected $_requestHeaders      = array('Content-Type' => 'application/x-www-form-urlencoded',
                                            'Pragma' => 'no-cache',
                                            'Connection' => 'close'
                                            );

    protected $_userAgent           = 'Kega-Framework';

    protected $_lastResponseData    = null;
    protected $_lastResponseHeaders = null;
    protected $_lastResponseError   = null;

    const CRLF = "\r\n";

    /**
     * Setting up client connection
     *
     * @param String $url
     * @param Array  $defaultRequestData
     * @param String $referer
     */
    public function __construct($url, $defaultRequestData = null, $referer = null)
    {
        $this->_requestUrl = $this->_checkUrl($url);

        $this->_defaultRequestData = $defaultRequestData;

        if (!empty($referer) ) {
            $this->_requestHeaders['Referer'] = $referer;
        }
    }

    /**
     * Send data to server (using POST) and retrieve result
     *
     * @param Array $requestData
     * @param Array $extraHeaders
     * @return String
     */
    public function doPost($requestData = null, $extraHeaders = array())
    {
        return $this->doRequest($requestData, $extraHeaders, true);
    }

    /**
     * Send data to server (using GET) and retrieve result
     *
     * @param Array $requestData
     * @param Array $extraHeaders
     * @return String
     */
    public function doGet($requestData = null, $extraHeaders = array())
    {
        return $this->doRequest($requestData, $extraHeaders, false);
    }

    /**
     * Send data to server (using POST/GET) and retrieve result
     *
     * @param Array $requestData
     * @param Array $extraHeaders
     * @return String
     */
    private function doRequest($requestData = null, $extraHeaders = array(), $post = false)
    {
        // Clear lastResponse data.
        $this->_lastResponseData    = null;
        $this->_lastResponseHeaders = null;
        $this->_lastResponseError   = null;

        $httpParams = array('method'        => ($post ? 'POST' : 'GET'),
                            'user_agent'    => $this->_userAgent,
                            'max_redirects' => $this->_maximumRedirects,
                            'timeout'       => $this->_timeout,
                            'header'        => $this->_getRequestHeaders($extraHeaders)
                            );

        // Url to request.
        $url = $this->_requestUrl;

        // If there is defaultRequestData.
        if (is_array($this->_defaultRequestData)) {
            if (is_array($requestData)) {
                $requestData = array_merge($this->_defaultRequestData, $requestData);
            } else {
                $requestData = $this->_defaultRequestData;
            }
        }

        // If there is requestData.
        if (is_array($requestData)) {

            // Encode the data as String.
            $requestData = $this->_encodeRequestData($requestData);

            if ($post) {
                // If POST, then add 'content' & 'Content-Length'.
                $httpParams['content'] = $requestData;
                $httpParams['header'] .= 'Content-Length: ' . strlen($requestData) . Kega_Http_Client::CRLF;
            } else {
                // If GET, then put the data after the url (seperated by ? or &).
                $url .= (strpos($url, '?') === false ? '?' : '&') . $requestData;
            }
        }

        try {
            // Set the context options.
            $streamContext = stream_context_create( array('http' => $httpParams) );

            // file_get_contents automaticly sets a variable called: $http_response_header
            $http_response_header = null;
            //die($url);
            // Send request to the server.

            $this->_lastResponseData = @file_get_contents($url, false, $streamContext);
            if(!$this->_lastResponseData) {
                return false;
                throw new Exception('Mailplust not available');
            }
            // file_get_contents automaticly sets a variable called: $http_response_header
            $this->_lastResponseHeaders = $http_response_header;
        }

        catch (Kega_Exceptions_PhpError $e) {
            $this->_lastResponseData = null;

            $message = $e->getMessage();
            if (strpos($message, 'php_network_getaddresses') !== false) {
                $this->_lastResponseError = 'Wrong url (domain not found)';
            } else if (strpos($message, 'Connection timed out') !== false) {
                $this->_lastResponseError = 'Connection timed out (>' . $this->_timeout . ' seconds)';
            } else if (strpos($message, 'HTTP/1.0 404 Not Found') !== false) {
                $this->_lastResponseError = 'HTTP/1.0 404 Not Found';
            } else if (strpos($message, 'HTTP/1.1 401 Unauthorized') !== false) {
                $this->_lastResponseError = 'HTTP/1.1 401 Unauthorized';
            } else {
                $this->_lastResponseError = 'Unknown: ' . $message;
            }
        }

        return $this->_lastResponseData;
    }

    /**
     * Check if the URL has a correct scheme
     *
     * @param String $url
     * @return String
     */
    private function _checkUrl($url)
    {
        $urlSegments = parse_url($url);

        if (!isset($urlSegments['scheme'])) {
            throw new Kega_Http_Client_Exception('Only HTTP(s) request are supported, no scheme found!');
        }

        if ($urlSegments['scheme'] != 'http' && $urlSegments['scheme'] != 'https') {
            throw new Kega_Http_Client_Exception('Only HTTP(s) request are supported, found: ' . $urlSegments['scheme']);
        }

        return $url;
    }

    /**
     * Encode the request data (and convert from Array to String).
     *
     * @param Array $requestData
     * @return String
     */
    private function _encodeRequestData($requestData = null)
    {
        $encodedData = array();

        if (!is_null($requestData)) {
            foreach($requestData AS $key => $value) {
                $encodedData[] = $key .'='. rawurlencode($value);
            }
        }

        return implode('&', $encodedData);
    }

    /**
     * Retrieve the request headers (and convert from Array to String).
     *
     * @param Array $extraHeaders
     * @return String
     */
    private function _getRequestHeaders($extraHeaders = array())
    {
        $headerData = '';

        $headers = array_merge($this->_requestHeaders, $extraHeaders);

        foreach ($headers AS $key => $value) {
            $headerData .= $key . ': ' . $value . Kega_Http_Client::CRLF;
        }

        return $headerData;
    }

    /**
     * Retrieve the contenttype of the response.
     *
     * @return String
     */
    private function _getResponseContentType() {

        $contentType = null;

        if (!is_null($this->_lastResponseHeaders)) {
            // Get the Content-Type from the HTTP response
            foreach ($this->_lastResponseHeaders AS $value) {
                if ( substr( $value, 0, 12) == 'Content-Type' ) {
                    $contentType = trim(substr( $value, 13 ));
                }
            }
        }

        return $contentType;
    }

    /**
     * Set authorisation parameters for request.
     *
     * @param String $username
     * @param String $password
     */
    public function setAuthorization($username, $password)
    {
        if (!is_null($username) && !is_null($password) ) {
            $base64string = base64_encode($username . ':' . $password);
            $this->_requestHeaders['Authorization'] = 'Basic ' . $base64string;
        }
    }

    /**
     * Set timeout for request.
     *
     * @param int $seconds
     */
    public function setTimeout($seconds)
    {
        $this->_timeout = $seconds;
    }

    /**
     * Set maximum redirects for request.
     *
     * @param int $maximumRedirects
     */
    public function setMaximumRedirects($maximumRedirects)
    {
        $this->_maximumRedirects = $maximumRedirects;
    }

    /**
     * Retrieve the headers of the response.
     *
     * @return Array
     */
    public function getResponseHeaders()
    {
        return $this->_lastResponseHeaders;
    }

    /**
     * Retrieve the error of the response.
     *
     * @return String
     */
    public function getResponseError()
    {
        return $this->_lastResponseError;
    }

    /**
     * Retrieve the mimetype of the response.
     *
     * @return String
     */
    public function getResponseMimeType()
    {
        $mimeType = null;

        $contentType = $this->_getResponseContentType();

        if (!is_null($contentType)) {
            // Read the the mimeType from the contentType
            list($mimeType) = explode(';', $contentType);
        }

        return $mimeType;
    }

    /**
     * Retrieve the characterset of the response.
     *
     * @return String
     */
    public function getResponseCharset()
    {
        $charset = null;

        $contentType = $this->_getResponseContentType();

        if (!is_null($contentType)) {

            // Try to find the charset in the contentType
            $position = strpos( $contentType, 'charset=');
            if ($position !== false ) {
                $charset = trim(substr( $contentType, $position + 8) );
            } else {
                // Try to find the charset in the document data.
                $mime = $this->getResponseMimeType();
                if ($mime == 'text/html') {
                    // If it is a HTML document
                    $matches = null;
                    preg_match( '@<meta\s+http-equiv="Content-Type"\s+content="([\w/]+)(;\s+charset=([^\s"]+))?@i',
                        $this->_lastResponseData, $matches );
                    if ( isset( $matches[3] ) )
                        $charset = $matches[3];
                } else if ($mime == 'text/xml' || $mime == 'application/xml') {
                    // If it is a XML document
                    preg_match( '@<\?xml.+encoding="([^\s"]+)@si', $this->_lastResponseData, $matches );
                    if ( isset( $matches[1] ) )
                        $charset = $matches[1];
                }
            }
        }

        return $charset;
    }
}