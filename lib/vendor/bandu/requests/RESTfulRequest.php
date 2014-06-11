<?php

namespace Bandu\Requests;

class RESTfulRequest{

    const GET = 'GET';
    const POST = 'POST';
    const PUT = 'PUT';
    const DELETE = 'DELETE';

    /**
     * Headers to be included with the request
     *
     * @var array
     */
    protected $requestHeaders = array();

    /**
     *
     * @var string
     */
    protected $requestMethod;

    /**
     * The destination of the request
     *
     * @var array
     */
    protected $requestUrl;

    /**
     *
     * @var string
     */
    protected $payload;

    /**
     * The cURL handler user to send the request
     *
     * @var resource
     */
    protected $ch;

    /**
     * The response from the request
     *
     * @var string
     */
    protected $response;

    /**
     * The headers included in the response from the destination server
     *
     * @var array
     */
    protected $responseHeaders = array();

    public function setRequestMethod($method) {
        switch (strtoupper($method)) {
            case self::GET:
            case self::POST:
            case self::PUT:
            case self::DELETE:
                $this->requestMethod = strtoupper($method);
                break;
            default:
                throw new Exception("Request Method Not Supported", 301);
                break;
        }
        return $this;
    }

    public function setRequestURL($url) {
        $components = parse_url($url);
        $this->urlSchemeIsValid($components['scheme'])
        ->hostIsValidDomainOrIPAddress($components['host']);
        $this->requestUrl = $components;
        return $this;
    }

    public function setBaseDomain($domain) {
        $components = parse_url($domain);
        $this->urlSchemeIsValid($components['scheme'])
        ->hostIsValidDomainOrIPAddress($components['host']);
        $this->requestUrl = $components;
        return $this;
    }

    public function setRequestURI($uri) {
        $this->requestUrl['path'] = uri;
        return $this;
    }

    public function setRequestQuery($query) {
        $this->requestUrl['query'] = $query;
        return $this;
    }

    public function setPayload($payload) {
        $this->payload = $payload;
        return $this;
    }

    public function setRequestHeader($key, $value) {
        $this->requestHeaders[$key] = $value;
        return $this;
    }

    public function send() {
        $this->buildRequest();
        $this->response = curl_exec($this->ch);
        if ($this->requestWasSuccessful()) {
            $this->readResponseHeaders();
        }
        return $this;
    }

    public function getResponse() {
        return $this->response;
    }

    public function getResponseHeaders() {
        return $this->responseHeaders;
    }

    public function getResponseHeader($name) {
        if (array_key_exists($name, $this->responseHeaders)) {
            return $this->responseHeaders[$name];
        }
        return null;
    }

    protected function urlSchemeIsValid($scheme) {
        if (!in_array($scheme, array('http', 'https'))) {
            throw new Exception('No Scheme Provided');
        }
        return $this;
    }

    protected function hostIsValidDomainOrIPAddress($host) {
        $validDomain = filter_var($host, FILTER_VALIDATE_URL);
        $validIPAddr = filter_var($host, FILTER_VALIDATE_IP);
        if ($validDomain || $validIPAddr) {
            throw new \Exception('Invalid Domain or IP Address');
        }
        return $this;
    }

    protected function getRequestHeaders() {
        $requestHeaders = array();
        foreach ($this->requestHeaders as $key => $value) {
            $requestHeaders[] = sprintf("%s: %s", $key, $value);
        }
        return $requestHeaders;
    }

    protected function buildRequest() {
        $this->ch = curl_init($this->buildUrl($this->requestUrl));
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->ch, CURLOPT_HEADER, true);
        curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, $this->requestMethod);
        curl_setopt($this->ch, CURLOPT_POSTFIELDS, $this->payload);
        curl_setopt($this->ch, CURLOPT_HTTPHEADER, $this->getRequestHeaders());
    }

    protected function requestWasSuccessful() {
        if (curl_errno($this->ch) > 0) {
            $error = array(
                    curl_error($this->ch),
                    curl_errno($this->ch)
            );
            $message = vsprintf('Error Sending Request: %s(%s)', $error);
            throw new Exception('Error Sending Request: %s(%s)');
        }
        return true;
    }

    protected function readResponseHeaders() {
        $headerSize = curl_getinfo($this->ch, CURLINFO_HEADER_SIZE);
        $header = substr($this->response, 0, $headerSize);
        $this->response = substr($this->response, $headerSize - 1);

        foreach (explode("\r\n", $header) as $i => $line) {
            if (strlen(trim($line))) {
                if ($i === 0) {
                    $this->responseHeaders['Http-Code'] = $line;
                } else {
                    list ($key, $value) = explode(': ', $line);
                    $this->responseHeaders[$key] = $value;
                }
            }
        }
    }

    protected function buildUrl() {
        extract($this->requestUrl);
        $url = sprintf("%s://%s", $scheme, $host);
        $url.= (isset($path))? $path : "/";
        $url.= (isset($query))? "?".$query : "";
        return $url;
    }

    public function __destruct() {
        if (is_resource($this->ch)) {
            curl_close($this->ch);
        }
    }

}