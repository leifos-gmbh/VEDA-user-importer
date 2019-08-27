# Swagger\Client\ELearningApi

All URIs are relative to *https://asp.veda.net/jwareplus_SiFa_Devel/api*

Method | HTTP request | Description
------------- | ------------- | -------------
[**getTeilnehmerELearningPlattformUsingGET**](ELearningApi.md#getTeilnehmerELearningPlattformUsingGET) | **GET** /v2/elearningplattformen/{plattformId}/teilnehmer | Ruft alle Teilnehmer einer E-Learning-Plattform ab
[**meldeElearningaccountAlsExternExistierendUsingPOST**](ELearningApi.md#meldeElearningaccountAlsExternExistierendUsingPOST) | **POST** /v2/elearningplattformen/{plattformId}/teilnehmer/{teilnehmerId}/meldeexternexistierend | Meldet, dass der Benutzer auf der externen E-Learning-Plattform existiert
[**meldeErstmaligErfolgreichEingeloggtUsingPOST**](ELearningApi.md#meldeErstmaligErfolgreichEingeloggtUsingPOST) | **POST** /v2/elearningplattformen/{plattformId}/teilnehmer/{teilnehmerId}/meldeinitialespasswortgeaendert | Setzt das Datum für das Ändern des initialen Passworts


# **getTeilnehmerELearningPlattformUsingGET**
> \Swagger\Client\Model\TeilnehmerELearningPlattform[] getTeilnehmerELearningPlattformUsingGET($plattform_id)

Ruft alle Teilnehmer einer E-Learning-Plattform ab

Mit dieser Schnittstelle werden alle Teilnehmer abgerufen, die zu der angegebenen E-Learning-Plattform Zugriff haben. Zur Verwendung der Schnittstelle wird die Tokenberechtigung \"E_LEARNING_ACCOUNT_GET\" benötigt.

### Example
```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');

$apiInstance = new Swagger\Client\Api\ELearningApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$plattform_id = "plattform_id_example"; // string | ID der E-Learning-Plattform

try {
    $result = $apiInstance->getTeilnehmerELearningPlattformUsingGET($plattform_id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ELearningApi->getTeilnehmerELearningPlattformUsingGET: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **plattform_id** | **string**| ID der E-Learning-Plattform |

### Return type

[**\Swagger\Client\Model\TeilnehmerELearningPlattform[]**](../Model/TeilnehmerELearningPlattform.md)

### Authorization

No authorization required

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

# **meldeElearningaccountAlsExternExistierendUsingPOST**
> meldeElearningaccountAlsExternExistierendUsingPOST($plattform_id, $teilnehmer_id)

Meldet, dass der Benutzer auf der externen E-Learning-Plattform existiert

Zur Protokollierung, dass der Benutzer für den angegebenen Teilnehmer existiert, wird das aktuelle Datum mit Uhrzeit gespeichert. Zur Verwendung der Schnittstelle wird die Tokenberechtigung \"E_LEARNING_ACCOUNT_LOGGING\" benötigt.

### Example
```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');

$apiInstance = new Swagger\Client\Api\ELearningApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$plattform_id = "plattform_id_example"; // string | ID der E-Learning-Plattform
$teilnehmer_id = "teilnehmer_id_example"; // string | ID des Teilnehmers

try {
    $apiInstance->meldeElearningaccountAlsExternExistierendUsingPOST($plattform_id, $teilnehmer_id);
} catch (Exception $e) {
    echo 'Exception when calling ELearningApi->meldeElearningaccountAlsExternExistierendUsingPOST: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **plattform_id** | **string**| ID der E-Learning-Plattform |
 **teilnehmer_id** | **string**| ID des Teilnehmers |

### Return type

void (empty response body)

### Authorization

No authorization required

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: */*

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

# **meldeErstmaligErfolgreichEingeloggtUsingPOST**
> meldeErstmaligErfolgreichEingeloggtUsingPOST($plattform_id, $teilnehmer_id)

Setzt das Datum für das Ändern des initialen Passworts

Setzt das Datum wann der Benutzer das initiale Passwort geändert hat (bspw. erstmaliges Einloggen) auf das aktuelle Datum mit Uhrzeit. Zur Verwendung der Schnittstelle wird die Tokenberechtigung \"E_LEARNING_ACCOUNT_LOGGING\" benötigt.

### Example
```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');

$apiInstance = new Swagger\Client\Api\ELearningApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$plattform_id = "plattform_id_example"; // string | ID der E-Learning-Plattform
$teilnehmer_id = "teilnehmer_id_example"; // string | ID des Teilnehmers

try {
    $apiInstance->meldeErstmaligErfolgreichEingeloggtUsingPOST($plattform_id, $teilnehmer_id);
} catch (Exception $e) {
    echo 'Exception when calling ELearningApi->meldeErstmaligErfolgreichEingeloggtUsingPOST: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **plattform_id** | **string**| ID der E-Learning-Plattform |
 **teilnehmer_id** | **string**| ID des Teilnehmers |

### Return type

void (empty response body)

### Authorization

No authorization required

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: */*

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

