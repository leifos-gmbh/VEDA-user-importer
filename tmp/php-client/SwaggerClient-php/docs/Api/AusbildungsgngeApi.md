# Swagger\Client\AusbildungsgngeApi

All URIs are relative to *https://asp.veda.net/jwareplus_SiFa_Devel/api*

Method | HTTP request | Description
------------- | ------------- | -------------
[**getAusbildungsgangUsingGET**](AusbildungsgngeApi.md#getAusbildungsgangUsingGET) | **GET** /v2/ausbildungsgaenge/{ausbildungsgangId} | Ruft die Informationen zu einem Ausbildungsgang ab


# **getAusbildungsgangUsingGET**
> \Swagger\Client\Model\Ausbildungsgang getAusbildungsgangUsingGET($ausbildungsgang_id)

Ruft die Informationen zu einem Ausbildungsgang ab

Mit dieser Schnittstelle wird der angegebene Ausbildungsgang mit seinen Ausbildungsgangabschnitten abgerufen. Zur Verwendung der Schnittstelle wird die Tokenberechtigung \"AUSBILDUNGSGANG_GET\" benötigt.

### Example
```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');

$apiInstance = new Swagger\Client\Api\AusbildungsgngeApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$ausbildungsgang_id = "ausbildungsgang_id_example"; // string | ID des Ausbildungsgangs

try {
    $result = $apiInstance->getAusbildungsgangUsingGET($ausbildungsgang_id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling AusbildungsgngeApi->getAusbildungsgangUsingGET: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **ausbildungsgang_id** | **string**| ID des Ausbildungsgangs |

### Return type

[**\Swagger\Client\Model\Ausbildungsgang**](../Model/Ausbildungsgang.md)

### Authorization

No authorization required

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

