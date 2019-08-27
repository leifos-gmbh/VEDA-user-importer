# Swagger\Client\BuchungenVonWebBasedTrainingsApi

All URIs are relative to *https://asp.veda.net/jwareplus_SiFa_Devel/api*

Method | HTTP request | Description
------------- | ------------- | -------------
[**lernerfolgAufWebBasedTrainingMeldenUsingPOST**](BuchungenVonWebBasedTrainingsApi.md#lernerfolgAufWebBasedTrainingMeldenUsingPOST) | **POST** /v2/wbtteilnehmerbuchungen/{teilnehmerbuchungId}/lernerfolgmelden | Ermöglicht es, zu einer Teilnehmerbuchung eines Web Based Trainings einen Lernerfolg zu melden.
[**storniereBuchungAufWebBasedTrainingUsingPOST**](BuchungenVonWebBasedTrainingsApi.md#storniereBuchungAufWebBasedTrainingUsingPOST) | **POST** /v2/wbtteilnehmerbuchungen/{teilnehmerbuchungId}/stornieren | Storniert die Buchung eines Teilnehmers auf ein Web Based Training (Noch nicht implementiert)


# **lernerfolgAufWebBasedTrainingMeldenUsingPOST**
> lernerfolgAufWebBasedTrainingMeldenUsingPOST($teilnehmerbuchung_id, $wbt_teilnehmerbuchung_lernerfolg_melden_api_dto)

Ermöglicht es, zu einer Teilnehmerbuchung eines Web Based Trainings einen Lernerfolg zu melden.

Mit dieser Schnittstelle kann zu einer Teilnehmerbuchung eines Web Based Trainings ein Lernerfolg gemeldet werden. Falls ein wiederholter Lernerfolg gemeldet wird, ist nur ein Wechsel von 'nicht erfolgreich' auf 'erfolgreich' möglich. Zur Verwendung der Schnittstelle wird die Tokenberechtigung \"TEILNAHME_WEBBASEDTRAINING_BUCHEN\" benötigt.

### Example
```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');

$apiInstance = new Swagger\Client\Api\BuchungenVonWebBasedTrainingsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$teilnehmerbuchung_id = "teilnehmerbuchung_id_example"; // string | ID der Teilnehmerbuchung
$wbt_teilnehmerbuchung_lernerfolg_melden_api_dto = new \Swagger\Client\Model\WbtTeilnehmerbuchungLernerfolgMeldenApiDto(); // \Swagger\Client\Model\WbtTeilnehmerbuchungLernerfolgMeldenApiDto | Informationen zum Melden des Lernerfolgs.

try {
    $apiInstance->lernerfolgAufWebBasedTrainingMeldenUsingPOST($teilnehmerbuchung_id, $wbt_teilnehmerbuchung_lernerfolg_melden_api_dto);
} catch (Exception $e) {
    echo 'Exception when calling BuchungenVonWebBasedTrainingsApi->lernerfolgAufWebBasedTrainingMeldenUsingPOST: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **teilnehmerbuchung_id** | **string**| ID der Teilnehmerbuchung |
 **wbt_teilnehmerbuchung_lernerfolg_melden_api_dto** | [**\Swagger\Client\Model\WbtTeilnehmerbuchungLernerfolgMeldenApiDto**](../Model/WbtTeilnehmerbuchungLernerfolgMeldenApiDto.md)| Informationen zum Melden des Lernerfolgs. | [optional]

### Return type

void (empty response body)

### Authorization

No authorization required

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: */*

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

# **storniereBuchungAufWebBasedTrainingUsingPOST**
> storniereBuchungAufWebBasedTrainingUsingPOST($teilnehmerbuchung_id, $teilnehmer_aktion_stornieren_api_dto)

Storniert die Buchung eines Teilnehmers auf ein Web Based Training (Noch nicht implementiert)

Mit dieser Schnittstelle wird eine Buchung eines Teilnehmers auf ein Web Based Training storniert. Zur Verwendung der Schnittstelle wird die Tokenberechtigung \"TEILNAHME_WEBBASEDTRAINING_STORNIEREN\" benötigt.

### Example
```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');

$apiInstance = new Swagger\Client\Api\BuchungenVonWebBasedTrainingsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$teilnehmerbuchung_id = "teilnehmerbuchung_id_example"; // string | ID der Teilnehmerbuchung, die storniert werden soll.
$teilnehmer_aktion_stornieren_api_dto = new \Swagger\Client\Model\TeilnehmerAktionStornierenApiDto(); // \Swagger\Client\Model\TeilnehmerAktionStornierenApiDto | Definition der Daten die zum stornieren benötigt werden

try {
    $apiInstance->storniereBuchungAufWebBasedTrainingUsingPOST($teilnehmerbuchung_id, $teilnehmer_aktion_stornieren_api_dto);
} catch (Exception $e) {
    echo 'Exception when calling BuchungenVonWebBasedTrainingsApi->storniereBuchungAufWebBasedTrainingUsingPOST: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **teilnehmerbuchung_id** | **string**| ID der Teilnehmerbuchung, die storniert werden soll. |
 **teilnehmer_aktion_stornieren_api_dto** | [**\Swagger\Client\Model\TeilnehmerAktionStornierenApiDto**](../Model/TeilnehmerAktionStornierenApiDto.md)| Definition der Daten die zum stornieren benötigt werden | [optional]

### Return type

void (empty response body)

### Authorization

No authorization required

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: */*

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

