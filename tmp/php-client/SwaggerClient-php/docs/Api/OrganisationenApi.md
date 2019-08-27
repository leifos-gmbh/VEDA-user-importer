# Swagger\Client\OrganisationenApi

All URIs are relative to *https://asp.veda.net/jwareplus_SiFa_Devel/api*

Method | HTTP request | Description
------------- | ------------- | -------------
[**getOrganisationUsingGET**](OrganisationenApi.md#getOrganisationUsingGET) | **GET** /v2/20010/organisationen/{organisationId} | Ruft eine Organisation ab


# **getOrganisationUsingGET**
> \Swagger\Client\Model\Organisation getOrganisationUsingGET($organisation_id)

Ruft eine Organisation ab

Mit dieser Schnittstelle wird die angegebene Organisation abgerufen.

### Example
```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');

$apiInstance = new Swagger\Client\Api\OrganisationenApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$organisation_id = "organisation_id_example"; // string | ID der Organisation

try {
    $result = $apiInstance->getOrganisationUsingGET($organisation_id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling OrganisationenApi->getOrganisationUsingGET: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **organisation_id** | **string**| ID der Organisation |

### Return type

[**\Swagger\Client\Model\Organisation**](../Model/Organisation.md)

### Authorization

No authorization required

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

