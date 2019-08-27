# Swagger\Client\VeranstaltungstermineApi

All URIs are relative to *https://asp.veda.net/jwareplus_SiFa_Devel/api*

Method | HTTP request | Description
------------- | ------------- | -------------
[**createVeranstaltungsterminBuchungUsingPOST**](VeranstaltungstermineApi.md#createVeranstaltungsterminBuchungUsingPOST) | **POST** /v2/veranstaltungstermine/{terminId}/buchen | Ermöglicht es, einen Teilnehmer auf einen Veranstaltungstermin zu buchen.
[**getTeilnehmerbuchungenZuVeranstaltungsterminUsingGET**](VeranstaltungstermineApi.md#getTeilnehmerbuchungenZuVeranstaltungsterminUsingGET) | **GET** /v2/veranstaltungstermine/{id}/teilnehmerbuchungen | Ermöglicht es, Teilnehmerbuchungen die den Status \&quot;Angemeldet\&quot;, \&quot;Eingeladen\&quot; oder \&quot;Bestätigt\&quot; gesetzt haben für einen Veranstaltungstermin abzurufen.
[**getVeranstaltungsterminUsingGET**](VeranstaltungstermineApi.md#getVeranstaltungsterminUsingGET) | **GET** /v2/veranstaltungstermine/{id} | Ruft einen Veranstaltungstermin ab (nicht storniert oder storniert)
[**getVeranstaltungstermineUsingGET**](VeranstaltungstermineApi.md#getVeranstaltungstermineUsingGET) | **GET** /v2/veranstaltungstermine | Ruft Veranstaltungstermine ab (nicht stornierte oder stornierte)


# **createVeranstaltungsterminBuchungUsingPOST**
> \Swagger\Client\Model\TeilnehmerbuchungApiDto createVeranstaltungsterminBuchungUsingPOST($termin_id, $teilnehmerbuchung_create_api_dto)

Ermöglicht es, einen Teilnehmer auf einen Veranstaltungstermin zu buchen.

Mit dieser Schnittstelle kann genau eine Teilnehmerbuchung für einen Teilnehmer auf einen Veranstaltungstermin angelegt werden. Die Teilnehmerbuchung erhält den Teilnehmerbuchungsstatus \"Angemeldet\". Zur Verwendung der Schnittstelle wird die Tokenberechtigung \"TEILNAHME_VERANSTALTUNGSTERMIN_BUCHEN\" benötigt.

### Example
```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');

$apiInstance = new Swagger\Client\Api\VeranstaltungstermineApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$termin_id = "termin_id_example"; // string | ID des Veranstaltungstermins
$teilnehmerbuchung_create_api_dto = new \Swagger\Client\Model\TeilnehmerAktionBuchenApiDto(); // \Swagger\Client\Model\TeilnehmerAktionBuchenApiDto | Informationen zu einer Teilnehmerbuchung, die angelegt werden soll.

try {
    $result = $apiInstance->createVeranstaltungsterminBuchungUsingPOST($termin_id, $teilnehmerbuchung_create_api_dto);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling VeranstaltungstermineApi->createVeranstaltungsterminBuchungUsingPOST: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **termin_id** | **string**| ID des Veranstaltungstermins |
 **teilnehmerbuchung_create_api_dto** | [**\Swagger\Client\Model\TeilnehmerAktionBuchenApiDto**](../Model/TeilnehmerAktionBuchenApiDto.md)| Informationen zu einer Teilnehmerbuchung, die angelegt werden soll. | [optional]

### Return type

[**\Swagger\Client\Model\TeilnehmerbuchungApiDto**](../Model/TeilnehmerbuchungApiDto.md)

### Authorization

No authorization required

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: */*

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

# **getTeilnehmerbuchungenZuVeranstaltungsterminUsingGET**
> \Swagger\Client\Model\TeilnehmerbuchungAbrufenApiDto[] getTeilnehmerbuchungenZuVeranstaltungsterminUsingGET($id)

Ermöglicht es, Teilnehmerbuchungen die den Status \"Angemeldet\", \"Eingeladen\" oder \"Bestätigt\" gesetzt haben für einen Veranstaltungstermin abzurufen.

Mit dieser Schnittstelle kann eine Liste von Teilnehmerbuchung für einen Veranstaltungstermin abgerufen werden. Zur Verwendung der Schnittstelle wird die Tokenberechtigung \"VERANSTALTUNGSTERMIN_TEILNEHMERBUCHUNGEN_ABRUFEN\" benötigt.

### Example
```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');

$apiInstance = new Swagger\Client\Api\VeranstaltungstermineApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$id = "id_example"; // string | ID des Veranstaltungstermins, zu dem die Teilnehmerbuchungen geladen werden sollen

try {
    $result = $apiInstance->getTeilnehmerbuchungenZuVeranstaltungsterminUsingGET($id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling VeranstaltungstermineApi->getTeilnehmerbuchungenZuVeranstaltungsterminUsingGET: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **id** | **string**| ID des Veranstaltungstermins, zu dem die Teilnehmerbuchungen geladen werden sollen |

### Return type

[**\Swagger\Client\Model\TeilnehmerbuchungAbrufenApiDto[]**](../Model/TeilnehmerbuchungAbrufenApiDto.md)

### Authorization

No authorization required

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: */*

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

# **getVeranstaltungsterminUsingGET**
> \Swagger\Client\Model\Veranstaltungstermin getVeranstaltungsterminUsingGET($id, $stornierten_termin_abrufen)

Ruft einen Veranstaltungstermin ab (nicht storniert oder storniert)

Mit dieser Schnittstelle wird ein Veranstaltungstermin abgerufen. Unterschieden wird, ob ein nicht stornierter oder ein stornierter abgerufen wird. Dies wird mittels des boolean Parameters storniertenTerminAbrufen unterschieden. Storniert ist ein Veranstaltungstermin, wenn der Status des Termins auf dem Systemstatus Abgesagt/Storniert basiert. Zur Verwendung der Schnittstelle wird die Tokenberechtigung \"VERANSTALTUNGSTERMIN_GET\" benötigt.

### Example
```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');

$apiInstance = new Swagger\Client\Api\VeranstaltungstermineApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$id = "id_example"; // string | ID des Veranstaltungstermins
$stornierten_termin_abrufen = true; // bool | Optionaler Parameter, um zu steuern, ob ein stornierter oder ein nicht stornierter Veranstaltungstermin abgerufen werden soll. Der Default ist false.

try {
    $result = $apiInstance->getVeranstaltungsterminUsingGET($id, $stornierten_termin_abrufen);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling VeranstaltungstermineApi->getVeranstaltungsterminUsingGET: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **id** | **string**| ID des Veranstaltungstermins |
 **stornierten_termin_abrufen** | **bool**| Optionaler Parameter, um zu steuern, ob ein stornierter oder ein nicht stornierter Veranstaltungstermin abgerufen werden soll. Der Default ist false. | [optional]

### Return type

[**\Swagger\Client\Model\Veranstaltungstermin**](../Model/Veranstaltungstermin.md)

### Authorization

No authorization required

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

# **getVeranstaltungstermineUsingGET**
> \Swagger\Client\Model\Veranstaltungstermin[] getVeranstaltungstermineUsingGET($modifiziert_ab, $stornierte_termine_abrufen, $veranstaltungstyp_id, $teilnehmergruppekuerzel, $veranstaltungskategorie_id)

Ruft Veranstaltungstermine ab (nicht stornierte oder stornierte)

Mit dieser Schnittstelle werden alle Veranstaltungstermine abgerufen. Unterschieden wird, ob alle nicht stornierten oder alle stornierten abgerufen werden. Dies wird mittels des boolean Parameters stornierteTermineAbrufen unterschieden. Storniert ist ein Veranstaltungstermin, wenn der Status des Termins auf dem Systemstatus Abgesagt/Storniert basiert. Zur Verwendung der Schnittstelle wird die Tokenberechtigung \"VERANSTALTUNGSTERMIN_GET\" benötigt.

### Example
```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');

$apiInstance = new Swagger\Client\Api\VeranstaltungstermineApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$modifiziert_ab = new \DateTime("2013-10-20T19:20:30+01:00"); // \DateTime | Änderungs- bzw. Einfügezeitpunkt, ab dem die Veranstaltungstermine geliefert werden sollen. Das Format muss wie folgt sein: yyyy-MM-ddTHH:mm:ss.SSS
$stornierte_termine_abrufen = true; // bool | Optionaler Parameter, um zu steuern, ob stornierte oder nicht stornierte Veranstaltungstermine abgerufen werden sollen. Der Default ist false.
$veranstaltungstyp_id = "veranstaltungstyp_id_example"; // string | Optionaler Parameter, um nur Veranstaltungstermine eines bestimmten Veranstaltungstyps abzurufen. Beispiel-ID: cfa1403a-13c6-4681-8ee3-e30127554845
$teilnehmergruppekuerzel = "teilnehmergruppekuerzel_example"; // string | Optionaler Parameter, um zu steuern, für welche Teilnehmergruppe die Veranstaltungstermine abgerufen werden sollen. Ist eine Teilnehmergruppe angegeben, so werden alle in der Zukunft liegenden und publizierten Veranstaltungen für diese Teilnehmergruppe zurückgegeben.
$veranstaltungskategorie_id = "veranstaltungskategorie_id_example"; // string | Optionaler Parameter, um Veranstaltungstermine nach einer Veranstaltungskategorie zu filtern.

try {
    $result = $apiInstance->getVeranstaltungstermineUsingGET($modifiziert_ab, $stornierte_termine_abrufen, $veranstaltungstyp_id, $teilnehmergruppekuerzel, $veranstaltungskategorie_id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling VeranstaltungstermineApi->getVeranstaltungstermineUsingGET: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **modifiziert_ab** | **\DateTime**| Änderungs- bzw. Einfügezeitpunkt, ab dem die Veranstaltungstermine geliefert werden sollen. Das Format muss wie folgt sein: yyyy-MM-ddTHH:mm:ss.SSS | [optional]
 **stornierte_termine_abrufen** | **bool**| Optionaler Parameter, um zu steuern, ob stornierte oder nicht stornierte Veranstaltungstermine abgerufen werden sollen. Der Default ist false. | [optional]
 **veranstaltungstyp_id** | **string**| Optionaler Parameter, um nur Veranstaltungstermine eines bestimmten Veranstaltungstyps abzurufen. Beispiel-ID: cfa1403a-13c6-4681-8ee3-e30127554845 | [optional]
 **teilnehmergruppekuerzel** | **string**| Optionaler Parameter, um zu steuern, für welche Teilnehmergruppe die Veranstaltungstermine abgerufen werden sollen. Ist eine Teilnehmergruppe angegeben, so werden alle in der Zukunft liegenden und publizierten Veranstaltungen für diese Teilnehmergruppe zurückgegeben. | [optional]
 **veranstaltungskategorie_id** | **string**| Optionaler Parameter, um Veranstaltungstermine nach einer Veranstaltungskategorie zu filtern. | [optional]

### Return type

[**\Swagger\Client\Model\Veranstaltungstermin[]**](../Model/Veranstaltungstermin.md)

### Authorization

No authorization required

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

