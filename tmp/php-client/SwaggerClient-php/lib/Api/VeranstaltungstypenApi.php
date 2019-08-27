<?php
/**
 * VeranstaltungstypenApi
 * PHP version 5
 *
 * @category Class
 * @package  Swagger\Client
 * @author   Swagger Codegen team
 * @link     https://github.com/swagger-api/swagger-codegen
 */

/**
 * VEDA Bildungsmanager API
 *
 * Dokumentation der REST-Schnittstellen des VEDA Bildungsmanagers für die Version 2. Die Dokumentation zu speziellen Versionen kann über die Angabe des zusätzlichen Parameters \"group\" angezeigt werden. Beispiel: .../api/docs?group=v1 für die Dokumentation der Version 1, die aktuelle Version ist unter .../api/docs erreichbar.
 *
 * OpenAPI spec version: 2
 * Contact: info@veda.net
 * Generated by: https://github.com/swagger-api/swagger-codegen.git
 * Swagger Codegen version: 2.4.8
 */

/**
 * NOTE: This class is auto generated by the swagger code generator program.
 * https://github.com/swagger-api/swagger-codegen
 * Do not edit the class manually.
 */

namespace Swagger\Client\Api;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\MultipartStream;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\RequestOptions;
use Swagger\Client\ApiException;
use Swagger\Client\Configuration;
use Swagger\Client\HeaderSelector;
use Swagger\Client\ObjectSerializer;

/**
 * VeranstaltungstypenApi Class Doc Comment
 *
 * @category Class
 * @package  Swagger\Client
 * @author   Swagger Codegen team
 * @link     https://github.com/swagger-api/swagger-codegen
 */
class VeranstaltungstypenApi
{
    /**
     * @var ClientInterface
     */
    protected $client;

    /**
     * @var Configuration
     */
    protected $config;

    /**
     * @var HeaderSelector
     */
    protected $headerSelector;

    /**
     * @param ClientInterface $client
     * @param Configuration   $config
     * @param HeaderSelector  $selector
     */
    public function __construct(
        ClientInterface $client = null,
        Configuration $config = null,
        HeaderSelector $selector = null
    ) {
        $this->client = $client ?: new Client();
        $this->config = $config ?: new Configuration();
        $this->headerSelector = $selector ?: new HeaderSelector();
    }

    /**
     * @return Configuration
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Operation getAllePraesenzVirtuellVeranstaltungstypenUsingGET
     *
     * Ruft alle Präsenz- und Virtuell-Veranstaltungstypen ab
     *
     * @param  string $teilnehmergruppekuerzel Optionaler Parameter, um zu steuern, für welche Teilnehmergruppe die Veranstaltungen abgerufen werden sollen. Ist eine Teilnehmergruppe angegeben, so werden alle publiziertenVeranstaltungen für diese Teilnehmergruppe zurückgegeben. (optional)
     *
     * @throws \Swagger\Client\ApiException on non-2xx response
     * @throws \InvalidArgumentException
     * @return \Swagger\Client\Model\Veranstaltungstyp[]
     */
    public function getAllePraesenzVirtuellVeranstaltungstypenUsingGET($teilnehmergruppekuerzel = null)
    {
        list($response) = $this->getAllePraesenzVirtuellVeranstaltungstypenUsingGETWithHttpInfo($teilnehmergruppekuerzel);
        return $response;
    }

    /**
     * Operation getAllePraesenzVirtuellVeranstaltungstypenUsingGETWithHttpInfo
     *
     * Ruft alle Präsenz- und Virtuell-Veranstaltungstypen ab
     *
     * @param  string $teilnehmergruppekuerzel Optionaler Parameter, um zu steuern, für welche Teilnehmergruppe die Veranstaltungen abgerufen werden sollen. Ist eine Teilnehmergruppe angegeben, so werden alle publiziertenVeranstaltungen für diese Teilnehmergruppe zurückgegeben. (optional)
     *
     * @throws \Swagger\Client\ApiException on non-2xx response
     * @throws \InvalidArgumentException
     * @return array of \Swagger\Client\Model\Veranstaltungstyp[], HTTP status code, HTTP response headers (array of strings)
     */
    public function getAllePraesenzVirtuellVeranstaltungstypenUsingGETWithHttpInfo($teilnehmergruppekuerzel = null)
    {
        $returnType = '\Swagger\Client\Model\Veranstaltungstyp[]';
        $request = $this->getAllePraesenzVirtuellVeranstaltungstypenUsingGETRequest($teilnehmergruppekuerzel);

        try {
            $options = $this->createHttpClientOption();
            try {
                $response = $this->client->send($request, $options);
            } catch (RequestException $e) {
                throw new ApiException(
                    "[{$e->getCode()}] {$e->getMessage()}",
                    $e->getCode(),
                    $e->getResponse() ? $e->getResponse()->getHeaders() : null,
                    $e->getResponse() ? $e->getResponse()->getBody()->getContents() : null
                );
            }

            $statusCode = $response->getStatusCode();

            if ($statusCode < 200 || $statusCode > 299) {
                throw new ApiException(
                    sprintf(
                        '[%d] Error connecting to the API (%s)',
                        $statusCode,
                        $request->getUri()
                    ),
                    $statusCode,
                    $response->getHeaders(),
                    $response->getBody()
                );
            }

            $responseBody = $response->getBody();
            if ($returnType === '\SplFileObject') {
                $content = $responseBody; //stream goes to serializer
            } else {
                $content = $responseBody->getContents();
                if ($returnType !== 'string') {
                    $content = json_decode($content);
                }
            }

            return [
                ObjectSerializer::deserialize($content, $returnType, []),
                $response->getStatusCode(),
                $response->getHeaders()
            ];

        } catch (ApiException $e) {
            switch ($e->getCode()) {
                case 200:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\Swagger\Client\Model\Veranstaltungstyp[]',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    break;
            }
            throw $e;
        }
    }

    /**
     * Operation getAllePraesenzVirtuellVeranstaltungstypenUsingGETAsync
     *
     * Ruft alle Präsenz- und Virtuell-Veranstaltungstypen ab
     *
     * @param  string $teilnehmergruppekuerzel Optionaler Parameter, um zu steuern, für welche Teilnehmergruppe die Veranstaltungen abgerufen werden sollen. Ist eine Teilnehmergruppe angegeben, so werden alle publiziertenVeranstaltungen für diese Teilnehmergruppe zurückgegeben. (optional)
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function getAllePraesenzVirtuellVeranstaltungstypenUsingGETAsync($teilnehmergruppekuerzel = null)
    {
        return $this->getAllePraesenzVirtuellVeranstaltungstypenUsingGETAsyncWithHttpInfo($teilnehmergruppekuerzel)
            ->then(
                function ($response) {
                    return $response[0];
                }
            );
    }

    /**
     * Operation getAllePraesenzVirtuellVeranstaltungstypenUsingGETAsyncWithHttpInfo
     *
     * Ruft alle Präsenz- und Virtuell-Veranstaltungstypen ab
     *
     * @param  string $teilnehmergruppekuerzel Optionaler Parameter, um zu steuern, für welche Teilnehmergruppe die Veranstaltungen abgerufen werden sollen. Ist eine Teilnehmergruppe angegeben, so werden alle publiziertenVeranstaltungen für diese Teilnehmergruppe zurückgegeben. (optional)
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function getAllePraesenzVirtuellVeranstaltungstypenUsingGETAsyncWithHttpInfo($teilnehmergruppekuerzel = null)
    {
        $returnType = '\Swagger\Client\Model\Veranstaltungstyp[]';
        $request = $this->getAllePraesenzVirtuellVeranstaltungstypenUsingGETRequest($teilnehmergruppekuerzel);

        return $this->client
            ->sendAsync($request, $this->createHttpClientOption())
            ->then(
                function ($response) use ($returnType) {
                    $responseBody = $response->getBody();
                    if ($returnType === '\SplFileObject') {
                        $content = $responseBody; //stream goes to serializer
                    } else {
                        $content = $responseBody->getContents();
                        if ($returnType !== 'string') {
                            $content = json_decode($content);
                        }
                    }

                    return [
                        ObjectSerializer::deserialize($content, $returnType, []),
                        $response->getStatusCode(),
                        $response->getHeaders()
                    ];
                },
                function ($exception) {
                    $response = $exception->getResponse();
                    $statusCode = $response->getStatusCode();
                    throw new ApiException(
                        sprintf(
                            '[%d] Error connecting to the API (%s)',
                            $statusCode,
                            $exception->getRequest()->getUri()
                        ),
                        $statusCode,
                        $response->getHeaders(),
                        $response->getBody()
                    );
                }
            );
    }

    /**
     * Create request for operation 'getAllePraesenzVirtuellVeranstaltungstypenUsingGET'
     *
     * @param  string $teilnehmergruppekuerzel Optionaler Parameter, um zu steuern, für welche Teilnehmergruppe die Veranstaltungen abgerufen werden sollen. Ist eine Teilnehmergruppe angegeben, so werden alle publiziertenVeranstaltungen für diese Teilnehmergruppe zurückgegeben. (optional)
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Psr7\Request
     */
    protected function getAllePraesenzVirtuellVeranstaltungstypenUsingGETRequest($teilnehmergruppekuerzel = null)
    {

        $resourcePath = '/v2/veranstaltungstypen';
        $formParams = [];
        $queryParams = [];
        $headerParams = [];
        $httpBody = '';
        $multipart = false;

        // query params
        if ($teilnehmergruppekuerzel !== null) {
            $queryParams['teilnehmergruppekuerzel'] = ObjectSerializer::toQueryValue($teilnehmergruppekuerzel);
        }


        // body params
        $_tempBody = null;

        if ($multipart) {
            $headers = $this->headerSelector->selectHeadersForMultipart(
                ['application/json']
            );
        } else {
            $headers = $this->headerSelector->selectHeaders(
                ['application/json'],
                ['application/json']
            );
        }

        // for model (json/xml)
        if (isset($_tempBody)) {
            // $_tempBody is the method argument, if present
            $httpBody = $_tempBody;
            // \stdClass has no __toString(), so we should encode it manually
            if ($httpBody instanceof \stdClass && $headers['Content-Type'] === 'application/json') {
                $httpBody = \GuzzleHttp\json_encode($httpBody);
            }
        } elseif (count($formParams) > 0) {
            if ($multipart) {
                $multipartContents = [];
                foreach ($formParams as $formParamName => $formParamValue) {
                    $multipartContents[] = [
                        'name' => $formParamName,
                        'contents' => $formParamValue
                    ];
                }
                // for HTTP post (form)
                $httpBody = new MultipartStream($multipartContents);

            } elseif ($headers['Content-Type'] === 'application/json') {
                $httpBody = \GuzzleHttp\json_encode($formParams);

            } else {
                // for HTTP post (form)
                $httpBody = \GuzzleHttp\Psr7\build_query($formParams);
            }
        }


        $defaultHeaders = [];
        if ($this->config->getUserAgent()) {
            $defaultHeaders['User-Agent'] = $this->config->getUserAgent();
        }

        $headers = array_merge(
            $defaultHeaders,
            $headerParams,
            $headers
        );

        $query = \GuzzleHttp\Psr7\build_query($queryParams);
        return new Request(
            'GET',
            $this->config->getHost() . $resourcePath . ($query ? "?{$query}" : ''),
            $headers,
            $httpBody
        );
    }

    /**
     * Operation getPraesenzVirtuellVeranstaltungstypUsingGET
     *
     * Ruft einen Präsenz- und Virtuell-Veranstaltungstypen ab
     *
     * @param  string $id ID des Veranstaltungstypen (required)
     *
     * @throws \Swagger\Client\ApiException on non-2xx response
     * @throws \InvalidArgumentException
     * @return \Swagger\Client\Model\Veranstaltungstyp
     */
    public function getPraesenzVirtuellVeranstaltungstypUsingGET($id)
    {
        list($response) = $this->getPraesenzVirtuellVeranstaltungstypUsingGETWithHttpInfo($id);
        return $response;
    }

    /**
     * Operation getPraesenzVirtuellVeranstaltungstypUsingGETWithHttpInfo
     *
     * Ruft einen Präsenz- und Virtuell-Veranstaltungstypen ab
     *
     * @param  string $id ID des Veranstaltungstypen (required)
     *
     * @throws \Swagger\Client\ApiException on non-2xx response
     * @throws \InvalidArgumentException
     * @return array of \Swagger\Client\Model\Veranstaltungstyp, HTTP status code, HTTP response headers (array of strings)
     */
    public function getPraesenzVirtuellVeranstaltungstypUsingGETWithHttpInfo($id)
    {
        $returnType = '\Swagger\Client\Model\Veranstaltungstyp';
        $request = $this->getPraesenzVirtuellVeranstaltungstypUsingGETRequest($id);

        try {
            $options = $this->createHttpClientOption();
            try {
                $response = $this->client->send($request, $options);
            } catch (RequestException $e) {
                throw new ApiException(
                    "[{$e->getCode()}] {$e->getMessage()}",
                    $e->getCode(),
                    $e->getResponse() ? $e->getResponse()->getHeaders() : null,
                    $e->getResponse() ? $e->getResponse()->getBody()->getContents() : null
                );
            }

            $statusCode = $response->getStatusCode();

            if ($statusCode < 200 || $statusCode > 299) {
                throw new ApiException(
                    sprintf(
                        '[%d] Error connecting to the API (%s)',
                        $statusCode,
                        $request->getUri()
                    ),
                    $statusCode,
                    $response->getHeaders(),
                    $response->getBody()
                );
            }

            $responseBody = $response->getBody();
            if ($returnType === '\SplFileObject') {
                $content = $responseBody; //stream goes to serializer
            } else {
                $content = $responseBody->getContents();
                if ($returnType !== 'string') {
                    $content = json_decode($content);
                }
            }

            return [
                ObjectSerializer::deserialize($content, $returnType, []),
                $response->getStatusCode(),
                $response->getHeaders()
            ];

        } catch (ApiException $e) {
            switch ($e->getCode()) {
                case 200:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\Swagger\Client\Model\Veranstaltungstyp',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    break;
            }
            throw $e;
        }
    }

    /**
     * Operation getPraesenzVirtuellVeranstaltungstypUsingGETAsync
     *
     * Ruft einen Präsenz- und Virtuell-Veranstaltungstypen ab
     *
     * @param  string $id ID des Veranstaltungstypen (required)
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function getPraesenzVirtuellVeranstaltungstypUsingGETAsync($id)
    {
        return $this->getPraesenzVirtuellVeranstaltungstypUsingGETAsyncWithHttpInfo($id)
            ->then(
                function ($response) {
                    return $response[0];
                }
            );
    }

    /**
     * Operation getPraesenzVirtuellVeranstaltungstypUsingGETAsyncWithHttpInfo
     *
     * Ruft einen Präsenz- und Virtuell-Veranstaltungstypen ab
     *
     * @param  string $id ID des Veranstaltungstypen (required)
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function getPraesenzVirtuellVeranstaltungstypUsingGETAsyncWithHttpInfo($id)
    {
        $returnType = '\Swagger\Client\Model\Veranstaltungstyp';
        $request = $this->getPraesenzVirtuellVeranstaltungstypUsingGETRequest($id);

        return $this->client
            ->sendAsync($request, $this->createHttpClientOption())
            ->then(
                function ($response) use ($returnType) {
                    $responseBody = $response->getBody();
                    if ($returnType === '\SplFileObject') {
                        $content = $responseBody; //stream goes to serializer
                    } else {
                        $content = $responseBody->getContents();
                        if ($returnType !== 'string') {
                            $content = json_decode($content);
                        }
                    }

                    return [
                        ObjectSerializer::deserialize($content, $returnType, []),
                        $response->getStatusCode(),
                        $response->getHeaders()
                    ];
                },
                function ($exception) {
                    $response = $exception->getResponse();
                    $statusCode = $response->getStatusCode();
                    throw new ApiException(
                        sprintf(
                            '[%d] Error connecting to the API (%s)',
                            $statusCode,
                            $exception->getRequest()->getUri()
                        ),
                        $statusCode,
                        $response->getHeaders(),
                        $response->getBody()
                    );
                }
            );
    }

    /**
     * Create request for operation 'getPraesenzVirtuellVeranstaltungstypUsingGET'
     *
     * @param  string $id ID des Veranstaltungstypen (required)
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Psr7\Request
     */
    protected function getPraesenzVirtuellVeranstaltungstypUsingGETRequest($id)
    {
        // verify the required parameter 'id' is set
        if ($id === null || (is_array($id) && count($id) === 0)) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $id when calling getPraesenzVirtuellVeranstaltungstypUsingGET'
            );
        }

        $resourcePath = '/v2/veranstaltungstypen/{id}';
        $formParams = [];
        $queryParams = [];
        $headerParams = [];
        $httpBody = '';
        $multipart = false;


        // path params
        if ($id !== null) {
            $resourcePath = str_replace(
                '{' . 'id' . '}',
                ObjectSerializer::toPathValue($id),
                $resourcePath
            );
        }

        // body params
        $_tempBody = null;

        if ($multipart) {
            $headers = $this->headerSelector->selectHeadersForMultipart(
                ['application/json']
            );
        } else {
            $headers = $this->headerSelector->selectHeaders(
                ['application/json'],
                ['application/json']
            );
        }

        // for model (json/xml)
        if (isset($_tempBody)) {
            // $_tempBody is the method argument, if present
            $httpBody = $_tempBody;
            // \stdClass has no __toString(), so we should encode it manually
            if ($httpBody instanceof \stdClass && $headers['Content-Type'] === 'application/json') {
                $httpBody = \GuzzleHttp\json_encode($httpBody);
            }
        } elseif (count($formParams) > 0) {
            if ($multipart) {
                $multipartContents = [];
                foreach ($formParams as $formParamName => $formParamValue) {
                    $multipartContents[] = [
                        'name' => $formParamName,
                        'contents' => $formParamValue
                    ];
                }
                // for HTTP post (form)
                $httpBody = new MultipartStream($multipartContents);

            } elseif ($headers['Content-Type'] === 'application/json') {
                $httpBody = \GuzzleHttp\json_encode($formParams);

            } else {
                // for HTTP post (form)
                $httpBody = \GuzzleHttp\Psr7\build_query($formParams);
            }
        }


        $defaultHeaders = [];
        if ($this->config->getUserAgent()) {
            $defaultHeaders['User-Agent'] = $this->config->getUserAgent();
        }

        $headers = array_merge(
            $defaultHeaders,
            $headerParams,
            $headers
        );

        $query = \GuzzleHttp\Psr7\build_query($queryParams);
        return new Request(
            'GET',
            $this->config->getHost() . $resourcePath . ($query ? "?{$query}" : ''),
            $headers,
            $httpBody
        );
    }

    /**
     * Create http client option
     *
     * @throws \RuntimeException on file opening failure
     * @return array of http client options
     */
    protected function createHttpClientOption()
    {
        $options = [];
        if ($this->config->getDebug()) {
            $options[RequestOptions::DEBUG] = fopen($this->config->getDebugFile(), 'a');
            if (!$options[RequestOptions::DEBUG]) {
                throw new \RuntimeException('Failed to open the debug file: ' . $this->config->getDebugFile());
            }
        }

        return $options;
    }
}
