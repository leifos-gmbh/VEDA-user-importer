# Swagger\Client\AusbildungApi

All URIs are relative to *https://asp.veda.net/jwareplus_SiFa_Devel/api*

Method | HTTP request | Description
------------- | ------------- | -------------
[**getAusbildungenVonAusbildungsgangUsingGET**](AusbildungApi.md#getAusbildungenVonAusbildungsgangUsingGET) | **GET** /v2/ausbildungszuege/{id}/ausbildungen | Ruft alle laufenden Ausbildungen eines Ausbildungszugs ab
[**lekBearbeitetUsingPOST**](AusbildungApi.md#lekBearbeitetUsingPOST) | **POST** /v2/ausbildungszugabschnitte/{ausbildungszugabschnittId}/teilnehmer/{teilnehmerId}/lekBearbeitet | Ermöglicht es, die LEK als bearbeitet zu kennzeichnen
[**lekFreischaltenUsingPOST**](AusbildungApi.md#lekFreischaltenUsingPOST) | **POST** /v2/ausbildungszugabschnitte/{ausbildungszugabschnittId}/teilnehmer/{teilnehmerId}/lekFreischalten | Ermöglicht es, die LEK freizuschalten
[**lernerfolgMeldenUsingPOST**](AusbildungApi.md#lernerfolgMeldenUsingPOST) | **POST** /v2/ausbildungszugabschnitte/{ausbildungszugabschnittId}/teilnehmer/{teilnehmerId}/lernerfolgMelden | Ermöglicht es, den Lernerfolg eines Ausbildungszugabschnitts für den angegebenen Teilnehmer zu melden
[**lernfristStartenUsingPOST**](AusbildungApi.md#lernfristStartenUsingPOST) | **POST** /v2/ausbildungszugabschnitte/{ausbildungszugabschnittId}/teilnehmer/{teilnehmerId}/lernfristStarten | Ermöglicht es, die Lernfrist zu starten


# **getAusbildungenVonAusbildungsgangUsingGET**
> \Swagger\Client\Model\Ausbildung[] getAusbildungenVonAusbildungsgangUsingGET($id)

Ruft alle laufenden Ausbildungen eines Ausbildungszugs ab

Mit dieser Schnittstelle werden alle laufenden Ausbildungen eines Ausbildungszugs abgerufen. DIESE SCHNITTSTELLE IST NOCH NICHT IMPLEMENTIERT.

### Example
```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');

$apiInstance = new Swagger\Client\Api\AusbildungApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$id = "id_example"; // string | ID des Ausbildungszugs

try {
    $result = $apiInstance->getAusbildungenVonAusbildungsgangUsingGET($id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling AusbildungApi->getAusbildungenVonAusbildungsgangUsingGET: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **id** | **string**| ID des Ausbildungszugs |

### Return type

[**\Swagger\Client\Model\Ausbildung[]**](../Model/Ausbildung.md)

### Authorization

No authorization required

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

# **lekBearbeitetUsingPOST**
> lekBearbeitetUsingPOST($ausbildungszugabschnitt_id, $teilnehmer_id, $lek_bearbeitet_api_dto)

Ermöglicht es, die LEK als bearbeitet zu kennzeichnen

Mit dieser Schnittstelle kann die LEK des Ausbildungszugabschnitts für den angegebenen Teilnehmer  als bearbeitet gekennzeichnet werden. DIESE SCHNITTSTELLE IST NOCH NICHT IMPLEMENTIERT.

### Example
```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');

$apiInstance = new Swagger\Client\Api\AusbildungApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$ausbildungszugabschnitt_id = "ausbildungszugabschnitt_id_example"; // string | ID des Ausbildungszugabschnitts
$teilnehmer_id = "teilnehmer_id_example"; // string | ID des Teilnehmers
$lek_bearbeitet_api_dto = new \Swagger\Client\Model\LekBearbeitetApiDto(); // \Swagger\Client\Model\LekBearbeitetApiDto | Informationen zum Bearbeiten einer LEK.

try {
    $apiInstance->lekBearbeitetUsingPOST($ausbildungszugabschnitt_id, $teilnehmer_id, $lek_bearbeitet_api_dto);
} catch (Exception $e) {
    echo 'Exception when calling AusbildungApi->lekBearbeitetUsingPOST: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **ausbildungszugabschnitt_id** | **string**| ID des Ausbildungszugabschnitts |
 **teilnehmer_id** | **string**| ID des Teilnehmers |
 **lek_bearbeitet_api_dto** | [**\Swagger\Client\Model\LekBearbeitetApiDto**](../Model/LekBearbeitetApiDto.md)| Informationen zum Bearbeiten einer LEK. | [optional]

### Return type

void (empty response body)

### Authorization

No authorization required

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: */*

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

# **lekFreischaltenUsingPOST**
> lekFreischaltenUsingPOST($ausbildungszugabschnitt_id, $teilnehmer_id, $lek_freischalten_api_dto)

Ermöglicht es, die LEK freizuschalten

Mit dieser Schnittstelle kann die LEK des Ausbildungszugabschnitts für den angegebenen Teilnehmer freigeschaltet werden. DIESE SCHNITTSTELLE IST NOCH NICHT IMPLEMENTIERT.

### Example
```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');

$apiInstance = new Swagger\Client\Api\AusbildungApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$ausbildungszugabschnitt_id = "ausbildungszugabschnitt_id_example"; // string | ID des Ausbildungszugabschnitts
$teilnehmer_id = "teilnehmer_id_example"; // string | ID des Teilnehmers
$lek_freischalten_api_dto = new \Swagger\Client\Model\LekFreischaltenApiDto(); // \Swagger\Client\Model\LekFreischaltenApiDto | Informationen zum Freischalten der LEK.

try {
    $apiInstance->lekFreischaltenUsingPOST($ausbildungszugabschnitt_id, $teilnehmer_id, $lek_freischalten_api_dto);
} catch (Exception $e) {
    echo 'Exception when calling AusbildungApi->lekFreischaltenUsingPOST: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **ausbildungszugabschnitt_id** | **string**| ID des Ausbildungszugabschnitts |
 **teilnehmer_id** | **string**| ID des Teilnehmers |
 **lek_freischalten_api_dto** | [**\Swagger\Client\Model\LekFreischaltenApiDto**](../Model/LekFreischaltenApiDto.md)| Informationen zum Freischalten der LEK. | [optional]

### Return type

void (empty response body)

### Authorization

No authorization required

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: */*

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

# **lernerfolgMeldenUsingPOST**
> lernerfolgMeldenUsingPOST($ausbildungszugabschnitt_id, $teilnehmer_id, $lernerfolg_melden_api_dto)

Ermöglicht es, den Lernerfolg eines Ausbildungszugabschnitts für den angegebenen Teilnehmer zu melden

Mit dieser Schnittstelle kann der Lernerfolg des Ausbildungszugabschnitts für den angegebenen Teilnehmer  gemeldet werden. DIESE SCHNITTSTELLE IST NOCH NICHT IMPLEMENTIERT.

### Example
```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');

$apiInstance = new Swagger\Client\Api\AusbildungApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$ausbildungszugabschnitt_id = "ausbildungszugabschnitt_id_example"; // string | ID des Ausbildungszugabschnitts
$teilnehmer_id = "teilnehmer_id_example"; // string | ID des Teilnehmers
$lernerfolg_melden_api_dto = new \Swagger\Client\Model\LernerfolgMeldenApiDto(); // \Swagger\Client\Model\LernerfolgMeldenApiDto | Informationen zum Bearbeiten einer LEK.

try {
    $apiInstance->lernerfolgMeldenUsingPOST($ausbildungszugabschnitt_id, $teilnehmer_id, $lernerfolg_melden_api_dto);
} catch (Exception $e) {
    echo 'Exception when calling AusbildungApi->lernerfolgMeldenUsingPOST: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **ausbildungszugabschnitt_id** | **string**| ID des Ausbildungszugabschnitts |
 **teilnehmer_id** | **string**| ID des Teilnehmers |
 **lernerfolg_melden_api_dto** | [**\Swagger\Client\Model\LernerfolgMeldenApiDto**](../Model/LernerfolgMeldenApiDto.md)| Informationen zum Bearbeiten einer LEK. | [optional]

### Return type

void (empty response body)

### Authorization

No authorization required

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: */*

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

# **lernfristStartenUsingPOST**
> lernfristStartenUsingPOST($ausbildungszugabschnitt_id, $teilnehmer_id, $lernfrist_starten_api_dto)

Ermöglicht es, die Lernfrist zu starten

Mit dieser Schnittstelle kann die Lernfrist des Ausbildungszugabschnitts für den angegebenen Teilnehmer  gestartet werden. DIESE SCHNITTSTELLE IST NOCH NICHT IMPLEMENTIERT.

### Example
```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');

$apiInstance = new Swagger\Client\Api\AusbildungApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$ausbildungszugabschnitt_id = "ausbildungszugabschnitt_id_example"; // string | ID des Ausbildungszugabschnitts
$teilnehmer_id = "teilnehmer_id_example"; // string | ID des Teilnehmers
$lernfrist_starten_api_dto = new \Swagger\Client\Model\LernfristStartenApiDto(); // \Swagger\Client\Model\LernfristStartenApiDto | Informationen zum Starten der Lernfrist.

try {
    $apiInstance->lernfristStartenUsingPOST($ausbildungszugabschnitt_id, $teilnehmer_id, $lernfrist_starten_api_dto);
} catch (Exception $e) {
    echo 'Exception when calling AusbildungApi->lernfristStartenUsingPOST: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **ausbildungszugabschnitt_id** | **string**| ID des Ausbildungszugabschnitts |
 **teilnehmer_id** | **string**| ID des Teilnehmers |
 **lernfrist_starten_api_dto** | [**\Swagger\Client\Model\LernfristStartenApiDto**](../Model/LernfristStartenApiDto.md)| Informationen zum Starten der Lernfrist. | [optional]

### Return type

void (empty response body)

### Authorization

No authorization required

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: */*

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

