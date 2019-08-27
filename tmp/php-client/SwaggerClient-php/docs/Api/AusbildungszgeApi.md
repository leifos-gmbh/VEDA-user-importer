# Swagger\Client\AusbildungszgeApi

All URIs are relative to *https://asp.veda.net/jwareplus_SiFa_Devel/api*

Method | HTTP request | Description
------------- | ------------- | -------------
[**getBeteiligteDozentenVonAusbildungszugUsingGET**](AusbildungszgeApi.md#getBeteiligteDozentenVonAusbildungszugUsingGET) | **GET** /v2/ausbildungszuege/{ausbildungszugId}/dozenten | Ruft alle beteiligten Dozenten eines Ausbildungszugs ab
[**getFreigegebeneAusbildungszuegeFuerPlattformUndAusbildungsgangUsingGET**](AusbildungszgeApi.md#getFreigegebeneAusbildungszuegeFuerPlattformUndAusbildungsgangUsingGET) | **GET** /v2/elearningplattformen/{elearningplattformId}/ausbildungsgaenge/{ausbildungsgangId}/ausbildungszuege | Ruft alle Ausbildungszüge eines Ausbildungsgangs ab, die zur E-Learning-Plattform gehören
[**getLernbegleiterVonAusbildungszugUsingGET**](AusbildungszgeApi.md#getLernbegleiterVonAusbildungszugUsingGET) | **GET** /v2/ausbildungszuege/{ausbildungszugId}/lernbegleiter | Ruft alle zuständigen Lernbegleiter eines Ausbildungszugs ab
[**getTeilnehmerVonAusbildungszugUsingGET**](AusbildungszgeApi.md#getTeilnehmerVonAusbildungszugUsingGET) | **GET** /v2/ausbildungszuege/{ausbildungszugId}/teilnehmer | Ruft alle Teilnehmer eines Ausbildungszugs ab
[**meldeAusbildungszugAlsExternExistierendUsingPOST**](AusbildungszgeApi.md#meldeAusbildungszugAlsExternExistierendUsingPOST) | **POST** /v2/ausbildungszuege/{ausbildungszugId}/meldeexternexistierend | Meldet den Ausbildungszug als extern existierend


# **getBeteiligteDozentenVonAusbildungszugUsingGET**
> \Swagger\Client\Model\AusbildungszugDozent[] getBeteiligteDozentenVonAusbildungszugUsingGET($ausbildungszug_id)

Ruft alle beteiligten Dozenten eines Ausbildungszugs ab

Mit dieser Schnittstelle werden alle Dozenten abgerufen, die an dem Ausbildungszug beteiligt sind. Zur Verwendung der Schnittstelle wird die Tokenberechtigung \"AUSBILDUNGSZUG_DOZENT_GET\" benötigt.

### Example
```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');

$apiInstance = new Swagger\Client\Api\AusbildungszgeApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$ausbildungszug_id = "ausbildungszug_id_example"; // string | ID des Ausbildungszugs

try {
    $result = $apiInstance->getBeteiligteDozentenVonAusbildungszugUsingGET($ausbildungszug_id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling AusbildungszgeApi->getBeteiligteDozentenVonAusbildungszugUsingGET: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **ausbildungszug_id** | **string**| ID des Ausbildungszugs |

### Return type

[**\Swagger\Client\Model\AusbildungszugDozent[]**](../Model/AusbildungszugDozent.md)

### Authorization

No authorization required

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

# **getFreigegebeneAusbildungszuegeFuerPlattformUndAusbildungsgangUsingGET**
> \Swagger\Client\Model\Ausbildungszug[] getFreigegebeneAusbildungszuegeFuerPlattformUndAusbildungsgangUsingGET($elearningplattform_id, $ausbildungsgang_id)

Ruft alle Ausbildungszüge eines Ausbildungsgangs ab, die zur E-Learning-Plattform gehören

Mit dieser Schnittstelle werden alle Ausbildungszüge des Ausbildungsgangs abgerufen, die zur E-Learning-Plattform gehören und zur Verwendung freigegeben wurden. Zur Verwendung der Schnittstelle wird die Tokenberechtigung \"AUSBILDUNGSZUG_GET\" benötigt.

### Example
```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');

$apiInstance = new Swagger\Client\Api\AusbildungszgeApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$elearningplattform_id = "elearningplattform_id_example"; // string | ID der E-Learning-Plattform
$ausbildungsgang_id = "ausbildungsgang_id_example"; // string | ID des Ausbildungsgangs

try {
    $result = $apiInstance->getFreigegebeneAusbildungszuegeFuerPlattformUndAusbildungsgangUsingGET($elearningplattform_id, $ausbildungsgang_id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling AusbildungszgeApi->getFreigegebeneAusbildungszuegeFuerPlattformUndAusbildungsgangUsingGET: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **elearningplattform_id** | **string**| ID der E-Learning-Plattform |
 **ausbildungsgang_id** | **string**| ID des Ausbildungsgangs |

### Return type

[**\Swagger\Client\Model\Ausbildungszug[]**](../Model/Ausbildungszug.md)

### Authorization

No authorization required

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

# **getLernbegleiterVonAusbildungszugUsingGET**
> \Swagger\Client\Model\AusbildungszugLernbegleiter[] getLernbegleiterVonAusbildungszugUsingGET($ausbildungszug_id)

Ruft alle zuständigen Lernbegleiter eines Ausbildungszugs ab

Mit dieser Schnittstelle werden alle zuständigen Lernbegleiter eines Ausbildungszugs abgerufen. Die Zuständigkeit kann zeitlich eingeschränkt sein. Zur Verwendung der Schnittstelle wird die Tokenberechtigung \"AUSBILDUNGSZUG_LERNBEGLEITER_GET\" benötigt.

### Example
```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');

$apiInstance = new Swagger\Client\Api\AusbildungszgeApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$ausbildungszug_id = "ausbildungszug_id_example"; // string | ID des Ausbildungszugs

try {
    $result = $apiInstance->getLernbegleiterVonAusbildungszugUsingGET($ausbildungszug_id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling AusbildungszgeApi->getLernbegleiterVonAusbildungszugUsingGET: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **ausbildungszug_id** | **string**| ID des Ausbildungszugs |

### Return type

[**\Swagger\Client\Model\AusbildungszugLernbegleiter[]**](../Model/AusbildungszugLernbegleiter.md)

### Authorization

No authorization required

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

# **getTeilnehmerVonAusbildungszugUsingGET**
> \Swagger\Client\Model\AusbildungszugTeilnehmer[] getTeilnehmerVonAusbildungszugUsingGET($ausbildungszug_id)

Ruft alle Teilnehmer eines Ausbildungszugs ab

Mit dieser Schnittstelle werden alle Teilnehmer eines Ausbildungszugs abgerufen. Zur Verwendung der Schnittstelle wird die Tokenberechtigung \"AUSBILDUNGSZUG_TEILNEHMER_GET\" benötigt.

### Example
```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');

$apiInstance = new Swagger\Client\Api\AusbildungszgeApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$ausbildungszug_id = "ausbildungszug_id_example"; // string | ID des Ausbildungszugs

try {
    $result = $apiInstance->getTeilnehmerVonAusbildungszugUsingGET($ausbildungszug_id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling AusbildungszgeApi->getTeilnehmerVonAusbildungszugUsingGET: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **ausbildungszug_id** | **string**| ID des Ausbildungszugs |

### Return type

[**\Swagger\Client\Model\AusbildungszugTeilnehmer[]**](../Model/AusbildungszugTeilnehmer.md)

### Authorization

No authorization required

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

# **meldeAusbildungszugAlsExternExistierendUsingPOST**
> meldeAusbildungszugAlsExternExistierendUsingPOST($ausbildungszug_id)

Meldet den Ausbildungszug als extern existierend

Mit dieser Schnittstelle wird der angegebene Ausbildungszug mit dem aktuellen Datum und Uhrzeit als extern existierend gekennzeichnet. Zur Verwendung der Schnittstelle wird die Tokenberechtigung \"AUSBILDUNGSZUG_LOGGING\" benötigt.

### Example
```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');

$apiInstance = new Swagger\Client\Api\AusbildungszgeApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$ausbildungszug_id = "ausbildungszug_id_example"; // string | ID des Ausbildungszugs

try {
    $apiInstance->meldeAusbildungszugAlsExternExistierendUsingPOST($ausbildungszug_id);
} catch (Exception $e) {
    echo 'Exception when calling AusbildungszgeApi->meldeAusbildungszugAlsExternExistierendUsingPOST: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **ausbildungszug_id** | **string**| ID des Ausbildungszugs |

### Return type

void (empty response body)

### Authorization

No authorization required

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

