# Swagger\Client\VeranstaltungskategorienApi

All URIs are relative to *https://asp.veda.net/jwareplus_SiFa_Devel/api*

Method | HTTP request | Description
------------- | ------------- | -------------
[**getAlleVeranstaltungskategorienUsingGET**](VeranstaltungskategorienApi.md#getAlleVeranstaltungskategorienUsingGET) | **GET** /v2/veranstaltungskategorien | Ruft Veranstaltungskategorien ab.


# **getAlleVeranstaltungskategorienUsingGET**
> \Swagger\Client\Model\Veranstaltungskategorie[] getAlleVeranstaltungskategorienUsingGET()

Ruft Veranstaltungskategorien ab.

Mit dieser Schnittstelle werden alle Veranstaltungskategorien abgerufen. Zur Verwendung der Schnittstelle wird die Tokenberechtigung \"VERANSTALTUNGSKATEGORIE_GET\" benötigt.

### Example
```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');

$apiInstance = new Swagger\Client\Api\VeranstaltungskategorienApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);

try {
    $result = $apiInstance->getAlleVeranstaltungskategorienUsingGET();
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling VeranstaltungskategorienApi->getAlleVeranstaltungskategorienUsingGET: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters
This endpoint does not need any parameter.

### Return type

[**\Swagger\Client\Model\Veranstaltungskategorie[]**](../Model/Veranstaltungskategorie.md)

### Authorization

No authorization required

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

