# Swagger\Client\ZielgruppenApi

All URIs are relative to *https://asp.veda.net/jwareplus_SiFa_Devel/api*

Method | HTTP request | Description
------------- | ------------- | -------------
[**getZielgruppeUsingGET**](ZielgruppenApi.md#getZielgruppeUsingGET) | **GET** /v2/zielgruppen/{id} | Ruft eine Zielgruppe ab
[**getZielgruppenUsingGET**](ZielgruppenApi.md#getZielgruppenUsingGET) | **GET** /v2/zielgruppen | Ruft alle Zielgruppen ab


# **getZielgruppeUsingGET**
> \Swagger\Client\Model\Zielgruppe getZielgruppeUsingGET($id)

Ruft eine Zielgruppe ab

Mit dieser Schnittstelle wird genau eine Zielgruppe abgerufen. Zur Verwendung der Schnittstelle wird eine der folgenden Tokenberechtigungen \"VERANSTALTUNGSTYP_GET\" oder \"WEBBASEDTRAINING_GET\" benötigt.

### Example
```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');

$apiInstance = new Swagger\Client\Api\ZielgruppenApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$id = "id_example"; // string | ID der Zeilgruppe

try {
    $result = $apiInstance->getZielgruppeUsingGET($id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ZielgruppenApi->getZielgruppeUsingGET: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **id** | **string**| ID der Zeilgruppe |

### Return type

[**\Swagger\Client\Model\Zielgruppe**](../Model/Zielgruppe.md)

### Authorization

No authorization required

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

# **getZielgruppenUsingGET**
> \Swagger\Client\Model\Zielgruppe[] getZielgruppenUsingGET()

Ruft alle Zielgruppen ab

Mit dieser Schnittstelle werden alle Zielgruppen abgerufen. Zur Verwendung der Schnittstelle wird eine der folgenden Tokenberechtigungen \"VERANSTALTUNGSTYP_GET\" oder \"WEBBASEDTRAINING_GET\" benötigt.

### Example
```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');

$apiInstance = new Swagger\Client\Api\ZielgruppenApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);

try {
    $result = $apiInstance->getZielgruppenUsingGET();
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ZielgruppenApi->getZielgruppenUsingGET: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters
This endpoint does not need any parameter.

### Return type

[**\Swagger\Client\Model\Zielgruppe[]**](../Model/Zielgruppe.md)

### Authorization

No authorization required

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

