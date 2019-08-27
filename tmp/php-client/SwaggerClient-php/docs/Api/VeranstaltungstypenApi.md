# Swagger\Client\VeranstaltungstypenApi

All URIs are relative to *https://asp.veda.net/jwareplus_SiFa_Devel/api*

Method | HTTP request | Description
------------- | ------------- | -------------
[**getAllePraesenzVirtuellVeranstaltungstypenUsingGET**](VeranstaltungstypenApi.md#getAllePraesenzVirtuellVeranstaltungstypenUsingGET) | **GET** /v2/veranstaltungstypen | Ruft alle Präsenz- und Virtuell-Veranstaltungstypen ab
[**getPraesenzVirtuellVeranstaltungstypUsingGET**](VeranstaltungstypenApi.md#getPraesenzVirtuellVeranstaltungstypUsingGET) | **GET** /v2/veranstaltungstypen/{id} | Ruft einen Präsenz- und Virtuell-Veranstaltungstypen ab


# **getAllePraesenzVirtuellVeranstaltungstypenUsingGET**
> \Swagger\Client\Model\Veranstaltungstyp[] getAllePraesenzVirtuellVeranstaltungstypenUsingGET($teilnehmergruppekuerzel)

Ruft alle Präsenz- und Virtuell-Veranstaltungstypen ab

Mit dieser Schnittstelle werden alle Präsenz- und Virtuell-Veranstaltungstypen abgerufen. Zur Verwendung der Schnittstelle wird die Tokenberechtigung \"VERANSTALTUNGSTYP_GET\" benötigt.

### Example
```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');

$apiInstance = new Swagger\Client\Api\VeranstaltungstypenApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$teilnehmergruppekuerzel = "teilnehmergruppekuerzel_example"; // string | Optionaler Parameter, um zu steuern, für welche Teilnehmergruppe die Veranstaltungen abgerufen werden sollen. Ist eine Teilnehmergruppe angegeben, so werden alle publiziertenVeranstaltungen für diese Teilnehmergruppe zurückgegeben.

try {
    $result = $apiInstance->getAllePraesenzVirtuellVeranstaltungstypenUsingGET($teilnehmergruppekuerzel);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling VeranstaltungstypenApi->getAllePraesenzVirtuellVeranstaltungstypenUsingGET: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **teilnehmergruppekuerzel** | **string**| Optionaler Parameter, um zu steuern, für welche Teilnehmergruppe die Veranstaltungen abgerufen werden sollen. Ist eine Teilnehmergruppe angegeben, so werden alle publiziertenVeranstaltungen für diese Teilnehmergruppe zurückgegeben. | [optional]

### Return type

[**\Swagger\Client\Model\Veranstaltungstyp[]**](../Model/Veranstaltungstyp.md)

### Authorization

No authorization required

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

# **getPraesenzVirtuellVeranstaltungstypUsingGET**
> \Swagger\Client\Model\Veranstaltungstyp getPraesenzVirtuellVeranstaltungstypUsingGET($id)

Ruft einen Präsenz- und Virtuell-Veranstaltungstypen ab

Mit dieser Schnittstelle wird genau ein Präsenz- und Virtuell-Veranstaltungstyp abgerufen. Zur Verwendung der Schnittstelle wird die Tokenberechtigung \"VERANSTALTUNGSTYP_GET\" benötigt.

### Example
```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');

$apiInstance = new Swagger\Client\Api\VeranstaltungstypenApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$id = "id_example"; // string | ID des Veranstaltungstypen

try {
    $result = $apiInstance->getPraesenzVirtuellVeranstaltungstypUsingGET($id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling VeranstaltungstypenApi->getPraesenzVirtuellVeranstaltungstypUsingGET: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **id** | **string**| ID des Veranstaltungstypen |

### Return type

[**\Swagger\Client\Model\Veranstaltungstyp**](../Model/Veranstaltungstyp.md)

### Authorization

No authorization required

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

