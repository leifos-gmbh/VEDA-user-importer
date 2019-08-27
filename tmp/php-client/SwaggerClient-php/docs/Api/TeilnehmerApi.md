# Swagger\Client\TeilnehmerApi

All URIs are relative to *https://asp.veda.net/jwareplus_SiFa_Devel/api*

Method | HTTP request | Description
------------- | ------------- | -------------
[**createTeilnehmerUsingPOST**](TeilnehmerApi.md#createTeilnehmerUsingPOST) | **POST** /v2/teilnehmer | Legt einen Teilnehmer an
[**getAktiveTeilnehmerUsingGET**](TeilnehmerApi.md#getAktiveTeilnehmerUsingGET) | **GET** /v2/teilnehmer | Ruft alle aktiven Teilnehmer ab
[**getTeilnehmerUsingGET**](TeilnehmerApi.md#getTeilnehmerUsingGET) | **GET** /v2/teilnehmer/personennr/{personenNr} | Ruft einen Teilnehmer anhand der Personen-Nr. ab
[**getTeilnehmerUsingGET1**](TeilnehmerApi.md#getTeilnehmerUsingGET1) | **GET** /v2/teilnehmer/{id} | Ruft einen Teilnehmer ab
[**updateTeilnehmerUsingPUT**](TeilnehmerApi.md#updateTeilnehmerUsingPUT) | **PUT** /v2/teilnehmer | Modifiziert ein Teilnehmer Datensatz


# **createTeilnehmerUsingPOST**
> \Swagger\Client\Model\Teilnehmer createTeilnehmerUsingPOST($create_teilnehmer_api_dto)

Legt einen Teilnehmer an

Mit dieser Schnittstelle kann genau ein Teilnehmer angelegt werden. Zur Verwendung der Schnittstelle wird die Tokenberechtigung \"TEILNEHMER_CREATE\" benötigt.

### Example
```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');

$apiInstance = new Swagger\Client\Api\TeilnehmerApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$create_teilnehmer_api_dto = new \Swagger\Client\Model\TeilnehmerCreateApiDto(); // \Swagger\Client\Model\TeilnehmerCreateApiDto | Definition eines Teilnehmers, der angelegt werden soll.

try {
    $result = $apiInstance->createTeilnehmerUsingPOST($create_teilnehmer_api_dto);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling TeilnehmerApi->createTeilnehmerUsingPOST: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **create_teilnehmer_api_dto** | [**\Swagger\Client\Model\TeilnehmerCreateApiDto**](../Model/TeilnehmerCreateApiDto.md)| Definition eines Teilnehmers, der angelegt werden soll. | [optional]

### Return type

[**\Swagger\Client\Model\Teilnehmer**](../Model/Teilnehmer.md)

### Authorization

No authorization required

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: */*

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

# **getAktiveTeilnehmerUsingGET**
> \Swagger\Client\Model\Teilnehmer[] getAktiveTeilnehmerUsingGET()

Ruft alle aktiven Teilnehmer ab

Mit dieser Schnittstelle werden alle aktiven Teilnehmer abgerufen. Zur Verwendung der Schnittstelle wird die Tokenberechtigung \"TEILNEHMER_GET\" benötigt.

### Example
```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');

$apiInstance = new Swagger\Client\Api\TeilnehmerApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);

try {
    $result = $apiInstance->getAktiveTeilnehmerUsingGET();
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling TeilnehmerApi->getAktiveTeilnehmerUsingGET: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters
This endpoint does not need any parameter.

### Return type

[**\Swagger\Client\Model\Teilnehmer[]**](../Model/Teilnehmer.md)

### Authorization

No authorization required

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

# **getTeilnehmerUsingGET**
> \Swagger\Client\Model\Teilnehmer getTeilnehmerUsingGET($personen_nr)

Ruft einen Teilnehmer anhand der Personen-Nr. ab

Mit dieser Schnittstelle wird genau ein Teilnehmer anhand der Personen-Nr. abgerufen. Dieser Teilnehmer kann auch inaktiv sein. Zur Verwendung der Schnittstelle wird die Tokenberechtigung \"TEILNEHMER_GET\" benötigt.

### Example
```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');

$apiInstance = new Swagger\Client\Api\TeilnehmerApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$personen_nr = "personen_nr_example"; // string | PersonenNr. des Teilnehmers

try {
    $result = $apiInstance->getTeilnehmerUsingGET($personen_nr);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling TeilnehmerApi->getTeilnehmerUsingGET: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **personen_nr** | **string**| PersonenNr. des Teilnehmers |

### Return type

[**\Swagger\Client\Model\Teilnehmer**](../Model/Teilnehmer.md)

### Authorization

No authorization required

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

# **getTeilnehmerUsingGET1**
> \Swagger\Client\Model\Teilnehmer getTeilnehmerUsingGET1($id)

Ruft einen Teilnehmer ab

Mit dieser Schnittstelle wird genau ein Teilnehmer abgerufen. Dieser Teilnehmer kann auch inaktiv sein. Zur Verwendung der Schnittstelle wird die Tokenberechtigung \"TEILNEHMER_GET\" benötigt.

### Example
```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');

$apiInstance = new Swagger\Client\Api\TeilnehmerApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$id = "id_example"; // string | ID des Teilnehmers

try {
    $result = $apiInstance->getTeilnehmerUsingGET1($id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling TeilnehmerApi->getTeilnehmerUsingGET1: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **id** | **string**| ID des Teilnehmers |

### Return type

[**\Swagger\Client\Model\Teilnehmer**](../Model/Teilnehmer.md)

### Authorization

No authorization required

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

# **updateTeilnehmerUsingPUT**
> \Swagger\Client\Model\Teilnehmer updateTeilnehmerUsingPUT($update_teilnehmer_api_dto)

Modifiziert ein Teilnehmer Datensatz

Mit dieser Schnittstelle kann genau ein Teilnehmer Datensatz überschrieben werden. Zur Verwendung der Schnittstelle wird die Tokenberechtigung \"TEILNEHMER_UPDATE\" benötigt.

### Example
```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');

$apiInstance = new Swagger\Client\Api\TeilnehmerApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$update_teilnehmer_api_dto = new \Swagger\Client\Model\Teilnehmer(); // \Swagger\Client\Model\Teilnehmer | Definition eines Teilnehmers, der aktualisiert werden soll.

try {
    $result = $apiInstance->updateTeilnehmerUsingPUT($update_teilnehmer_api_dto);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling TeilnehmerApi->updateTeilnehmerUsingPUT: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **update_teilnehmer_api_dto** | [**\Swagger\Client\Model\Teilnehmer**](../Model/Teilnehmer.md)| Definition eines Teilnehmers, der aktualisiert werden soll. | [optional]

### Return type

[**\Swagger\Client\Model\Teilnehmer**](../Model/Teilnehmer.md)

### Authorization

No authorization required

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: */*

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

