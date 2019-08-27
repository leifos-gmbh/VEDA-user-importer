# Swagger\Client\WebBasedTrainingsApi

All URIs are relative to *https://asp.veda.net/jwareplus_SiFa_Devel/api*

Method | HTTP request | Description
------------- | ------------- | -------------
[**createWebBasedTrainingBuchungUsingPOST**](WebBasedTrainingsApi.md#createWebBasedTrainingBuchungUsingPOST) | **POST** /v2/webbasedtrainings/{wbtId}/buchen | Ermöglicht es, einen Teilnehmer auf ein Web Based Training zu buchen
[**createWebBasedTrainingUsingPOST**](WebBasedTrainingsApi.md#createWebBasedTrainingUsingPOST) | **POST** /v2/webbasedtrainings | Legt ein Web Based Training an
[**getTeilnehmerbuchungenZuWebBasedTrainingUsingGET**](WebBasedTrainingsApi.md#getTeilnehmerbuchungenZuWebBasedTrainingUsingGET) | **GET** /v2/webbasedtrainings/{id}/teilnehmerbuchungen | Ermöglicht es, Teilnehmerbuchungen die den Status \&quot;Angemeldet\&quot;, \&quot;Eingeladen\&quot; oder \&quot;Bestätigt\&quot; gesetzt haben für ein Web Based Training abzurufen.
[**getWebBasedTrainingUsingGET**](WebBasedTrainingsApi.md#getWebBasedTrainingUsingGET) | **GET** /v2/webbasedtrainings/{id} | Ruft ein Web Based Training ab
[**getWebBasedTrainingsUsingGET**](WebBasedTrainingsApi.md#getWebBasedTrainingsUsingGET) | **GET** /v2/webbasedtrainings | Ruft Web Based Trainings ab.
[**updateWebBasedTrainingUsingPUT**](WebBasedTrainingsApi.md#updateWebBasedTrainingUsingPUT) | **PUT** /v2/webbasedtrainings | Modifiziert ein bestehendes Web Based Training


# **createWebBasedTrainingBuchungUsingPOST**
> \Swagger\Client\Model\TeilnehmerbuchungApiDto createWebBasedTrainingBuchungUsingPOST($wbt_id, $teilnehmerbuchung_create_api_dto)

Ermöglicht es, einen Teilnehmer auf ein Web Based Training zu buchen

Mit dieser Schnittstelle kann genau eine Teilnehmerbuchung für einen Teilnehmer auf ein Web Based Training angelegt werden. Die Teilnehmerbuchung erhält den Teilnehmerbuchungsstatus \"Angemeldet\". Zur Verwendung der Schnittstelle wird die Tokenberechtigung \"TEILNAHME_WEBBASEDTRAINING_BUCHEN\" benötigt.

### Example
```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');

$apiInstance = new Swagger\Client\Api\WebBasedTrainingsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$wbt_id = "wbt_id_example"; // string | ID des Web Based Trainings
$teilnehmerbuchung_create_api_dto = new \Swagger\Client\Model\TeilnehmerAktionBuchenApiDto(); // \Swagger\Client\Model\TeilnehmerAktionBuchenApiDto | Informationen zu einer Teilnehmerbuchung, die angelegt werden soll.

try {
    $result = $apiInstance->createWebBasedTrainingBuchungUsingPOST($wbt_id, $teilnehmerbuchung_create_api_dto);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling WebBasedTrainingsApi->createWebBasedTrainingBuchungUsingPOST: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **wbt_id** | **string**| ID des Web Based Trainings |
 **teilnehmerbuchung_create_api_dto** | [**\Swagger\Client\Model\TeilnehmerAktionBuchenApiDto**](../Model/TeilnehmerAktionBuchenApiDto.md)| Informationen zu einer Teilnehmerbuchung, die angelegt werden soll. | [optional]

### Return type

[**\Swagger\Client\Model\TeilnehmerbuchungApiDto**](../Model/TeilnehmerbuchungApiDto.md)

### Authorization

No authorization required

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: */*

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

# **createWebBasedTrainingUsingPOST**
> \Swagger\Client\Model\WebBasedTraining createWebBasedTrainingUsingPOST($create_web_based_training_api_dto)

Legt ein Web Based Training an

Mit dieser Schnittstelle kann genau ein Web Based Training angelegt werden. Zur Verwendung der Schnittstelle wird die Tokenberechtigung \"WEBBASEDTRAINING_CREATE\" benötigt.

### Example
```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');

$apiInstance = new Swagger\Client\Api\WebBasedTrainingsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$create_web_based_training_api_dto = new \Swagger\Client\Model\CreateWebBasedTraining(); // \Swagger\Client\Model\CreateWebBasedTraining | Definition eines Web Bases Training, das angelegt werden soll.

try {
    $result = $apiInstance->createWebBasedTrainingUsingPOST($create_web_based_training_api_dto);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling WebBasedTrainingsApi->createWebBasedTrainingUsingPOST: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **create_web_based_training_api_dto** | [**\Swagger\Client\Model\CreateWebBasedTraining**](../Model/CreateWebBasedTraining.md)| Definition eines Web Bases Training, das angelegt werden soll. | [optional]

### Return type

[**\Swagger\Client\Model\WebBasedTraining**](../Model/WebBasedTraining.md)

### Authorization

No authorization required

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: */*

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

# **getTeilnehmerbuchungenZuWebBasedTrainingUsingGET**
> \Swagger\Client\Model\TeilnehmerbuchungAbrufenApiDto[] getTeilnehmerbuchungenZuWebBasedTrainingUsingGET($id)

Ermöglicht es, Teilnehmerbuchungen die den Status \"Angemeldet\", \"Eingeladen\" oder \"Bestätigt\" gesetzt haben für ein Web Based Training abzurufen.

Mit dieser Schnittstelle kann eine Liste von Teilnehmerbuchung für ein Web Based Training abgerufen werden. Zur Verwendung der Schnittstelle wird die Tokenberechtigung \"WEBBASEDTRAINING_TEILNEHMERBUCHUNGEN_ABRUFEN\" benötigt.

### Example
```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');

$apiInstance = new Swagger\Client\Api\WebBasedTrainingsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$id = "id_example"; // string | ID des Web Based Trainings, zu dem die Teilnehmerbuchungen geladen werden sollen

try {
    $result = $apiInstance->getTeilnehmerbuchungenZuWebBasedTrainingUsingGET($id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling WebBasedTrainingsApi->getTeilnehmerbuchungenZuWebBasedTrainingUsingGET: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **id** | **string**| ID des Web Based Trainings, zu dem die Teilnehmerbuchungen geladen werden sollen |

### Return type

[**\Swagger\Client\Model\TeilnehmerbuchungAbrufenApiDto[]**](../Model/TeilnehmerbuchungAbrufenApiDto.md)

### Authorization

No authorization required

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: */*

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

# **getWebBasedTrainingUsingGET**
> \Swagger\Client\Model\WebBasedTraining getWebBasedTrainingUsingGET($id)

Ruft ein Web Based Training ab

Mit dieser Schnittstelle wird genau ein Web Based Training abgerufen. Zur Verwendung der Schnittstelle wird die Tokenberechtigung \"WEBBASEDTRAINING_GET\" benötigt.

### Example
```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');

$apiInstance = new Swagger\Client\Api\WebBasedTrainingsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$id = "id_example"; // string | ID des Web Based Trainings

try {
    $result = $apiInstance->getWebBasedTrainingUsingGET($id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling WebBasedTrainingsApi->getWebBasedTrainingUsingGET: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **id** | **string**| ID des Web Based Trainings |

### Return type

[**\Swagger\Client\Model\WebBasedTraining**](../Model/WebBasedTraining.md)

### Authorization

No authorization required

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

# **getWebBasedTrainingsUsingGET**
> \Swagger\Client\Model\WebBasedTraining[] getWebBasedTrainingsUsingGET($modifiziert_ab, $teilnehmergruppekuerzel)

Ruft Web Based Trainings ab.

Mit dieser Schnittstelle werden alle Web Based Trainings abgerufen. Zur Verwendung der Schnittstelle wird die Tokenberechtigung \"WEBBASEDTRAINING_GET\" benötigt.

### Example
```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');

$apiInstance = new Swagger\Client\Api\WebBasedTrainingsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$modifiziert_ab = new \DateTime("2013-10-20T19:20:30+01:00"); // \DateTime | Änderungs- bzw. Einfügezeitpunkt, ab dem die Web Based Trainings geliefert werden sollen. Das Format muss wie folgt sein: yyyy-MM-ddTHH:mm:ss.sss
$teilnehmergruppekuerzel = "teilnehmergruppekuerzel_example"; // string | Optionaler Parameter, um zu steuern, für welche Teilnehmergruppe die Veranstaltungen abgerufen werden sollen. Ist eine Teilnehmergruppe angegeben, so werden alle publiziertenVeranstaltungen für diese Teilnehmergruppe zurückgegeben.

try {
    $result = $apiInstance->getWebBasedTrainingsUsingGET($modifiziert_ab, $teilnehmergruppekuerzel);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling WebBasedTrainingsApi->getWebBasedTrainingsUsingGET: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **modifiziert_ab** | **\DateTime**| Änderungs- bzw. Einfügezeitpunkt, ab dem die Web Based Trainings geliefert werden sollen. Das Format muss wie folgt sein: yyyy-MM-ddTHH:mm:ss.sss | [optional]
 **teilnehmergruppekuerzel** | **string**| Optionaler Parameter, um zu steuern, für welche Teilnehmergruppe die Veranstaltungen abgerufen werden sollen. Ist eine Teilnehmergruppe angegeben, so werden alle publiziertenVeranstaltungen für diese Teilnehmergruppe zurückgegeben. | [optional]

### Return type

[**\Swagger\Client\Model\WebBasedTraining[]**](../Model/WebBasedTraining.md)

### Authorization

No authorization required

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

# **updateWebBasedTrainingUsingPUT**
> \Swagger\Client\Model\WebBasedTraining updateWebBasedTrainingUsingPUT($update_web_based_training_api_dto)

Modifiziert ein bestehendes Web Based Training

Mit dieser Schnittstelle kann genau ein Web Based Training überschrieben werden. Zur Verwendung der Schnittstelle wird die Tokenberechtigung \"WEBBASEDTRAINING_UPDATE\" benötigt.

### Example
```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');

$apiInstance = new Swagger\Client\Api\WebBasedTrainingsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$update_web_based_training_api_dto = new \Swagger\Client\Model\WebBasedTraining(); // \Swagger\Client\Model\WebBasedTraining | Definition eines Web Bases Training, das aktualisiert werden soll.

try {
    $result = $apiInstance->updateWebBasedTrainingUsingPUT($update_web_based_training_api_dto);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling WebBasedTrainingsApi->updateWebBasedTrainingUsingPUT: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **update_web_based_training_api_dto** | [**\Swagger\Client\Model\WebBasedTraining**](../Model/WebBasedTraining.md)| Definition eines Web Bases Training, das aktualisiert werden soll. | [optional]

### Return type

[**\Swagger\Client\Model\WebBasedTraining**](../Model/WebBasedTraining.md)

### Authorization

No authorization required

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: */*

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

