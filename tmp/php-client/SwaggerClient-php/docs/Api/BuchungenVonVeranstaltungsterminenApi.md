# Swagger\Client\BuchungenVonVeranstaltungsterminenApi

All URIs are relative to *https://asp.veda.net/jwareplus_SiFa_Devel/api*

Method | HTTP request | Description
------------- | ------------- | -------------
[**lernerfolgAufVeranstaltungsterminMeldenUsingPOST**](BuchungenVonVeranstaltungsterminenApi.md#lernerfolgAufVeranstaltungsterminMeldenUsingPOST) | **POST** /v2/veranstaltungsterminteilnehmerbuchungen/{teilnehmerbuchungId}/lernerfolgmelden | Ermöglicht es, zu einer Teilnehmerbuchung eines Veranstaltungstermins einen Lernerfolg zu melden
[**storniereBuchungAufVeranstaltungsterminUsingPOST**](BuchungenVonVeranstaltungsterminenApi.md#storniereBuchungAufVeranstaltungsterminUsingPOST) | **POST** /v2/veranstaltungsterminteilnehmerbuchungen/{teilnehmerbuchungId}/stornieren | Storniert die Buchung eines Teilnehmers auf einen Veranstaltungstermin (Noch nicht implementiert)


# **lernerfolgAufVeranstaltungsterminMeldenUsingPOST**
> lernerfolgAufVeranstaltungsterminMeldenUsingPOST($teilnehmerbuchung_id, $veranstaltungstermin_teilnehmerbuchung_lernerfolg_melden_api_dto)

Ermöglicht es, zu einer Teilnehmerbuchung eines Veranstaltungstermins einen Lernerfolg zu melden

Mit dieser Schnittstelle kann zu einer Teilnehmerbuchung eines Veranstaltungstermins ein Lernerfolg gemeldet werden. Zur Verwendung der Schnittstelle wird die Tokenberechtigung \"TEILNAHME_VERANSTALTUNGSTERMIN_BUCHEN\" benötigt.

### Example
```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');

$apiInstance = new Swagger\Client\Api\BuchungenVonVeranstaltungsterminenApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$teilnehmerbuchung_id = "teilnehmerbuchung_id_example"; // string | ID der Teilnehmerbuchung
$veranstaltungstermin_teilnehmerbuchung_lernerfolg_melden_api_dto = new \Swagger\Client\Model\VeranstaltungsterminTeilnehmerbuchungLernerfolgMeldenApiDto(); // \Swagger\Client\Model\VeranstaltungsterminTeilnehmerbuchungLernerfolgMeldenApiDto | Informationen zum Melden des Lernerfolgs.

try {
    $apiInstance->lernerfolgAufVeranstaltungsterminMeldenUsingPOST($teilnehmerbuchung_id, $veranstaltungstermin_teilnehmerbuchung_lernerfolg_melden_api_dto);
} catch (Exception $e) {
    echo 'Exception when calling BuchungenVonVeranstaltungsterminenApi->lernerfolgAufVeranstaltungsterminMeldenUsingPOST: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **teilnehmerbuchung_id** | **string**| ID der Teilnehmerbuchung |
 **veranstaltungstermin_teilnehmerbuchung_lernerfolg_melden_api_dto** | [**\Swagger\Client\Model\VeranstaltungsterminTeilnehmerbuchungLernerfolgMeldenApiDto**](../Model/VeranstaltungsterminTeilnehmerbuchungLernerfolgMeldenApiDto.md)| Informationen zum Melden des Lernerfolgs. | [optional]

### Return type

void (empty response body)

### Authorization

No authorization required

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: */*

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

# **storniereBuchungAufVeranstaltungsterminUsingPOST**
> storniereBuchungAufVeranstaltungsterminUsingPOST($teilnehmerbuchung_id, $teilnehmer_aktion_stornieren_api_dto)

Storniert die Buchung eines Teilnehmers auf einen Veranstaltungstermin (Noch nicht implementiert)

Mit dieser Schnittstelle wird eine Buchung eines Teilnehmers auf einen Veranstaltungstermin storniert. Zur Verwendung der Schnittstelle wird die Tokenberechtigung \"TEILNAHME_VERANSTALTUNGSTERMIN_STORNIEREN\" benötigt.

### Example
```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');

$apiInstance = new Swagger\Client\Api\BuchungenVonVeranstaltungsterminenApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$teilnehmerbuchung_id = "teilnehmerbuchung_id_example"; // string | ID der Teilnehmerbuchung, die storniert werden soll.
$teilnehmer_aktion_stornieren_api_dto = new \Swagger\Client\Model\TeilnehmerAktionStornierenApiDto(); // \Swagger\Client\Model\TeilnehmerAktionStornierenApiDto | Definition der Daten die zum stornieren benötigt werden

try {
    $apiInstance->storniereBuchungAufVeranstaltungsterminUsingPOST($teilnehmerbuchung_id, $teilnehmer_aktion_stornieren_api_dto);
} catch (Exception $e) {
    echo 'Exception when calling BuchungenVonVeranstaltungsterminenApi->storniereBuchungAufVeranstaltungsterminUsingPOST: ', $e->getMessage(), PHP_EOL;
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

