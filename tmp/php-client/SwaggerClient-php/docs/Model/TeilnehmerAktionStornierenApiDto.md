# TeilnehmerAktionStornierenApiDto

## Properties
Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**kuerzel_stornierungs_grund** | **string** | Ein Kürzel aus der Bildungsmanager-Tabelle Teilnehmerstornierungsgründe. Je nach Kürzel wird dann der dazu passende Teilnehmerstornierungsgrund gesetzt. Wird kein Teilnehmerstornierungsgrund gesetzt, so greift der in den Systemeinstellungen der REST-Schnittstelle hinterlegte Standardgrund. Ist hier nichts hinterlegt, kann nicht storniert werden. | [optional] 
**links** | [**\Swagger\Client\Model\Link[]**](Link.md) |  | [optional] 
**stornierungsdatum_fremdsystem** | [**\DateTime**](\DateTime.md) | Stornierungsdatum des aufrufenden Systems. Wird nichts gesetzt, wird mit dem aktuellen Tagesdatum des Bildungsmanager-Servers vorbelegt. | [optional] 

[[Back to Model list]](../README.md#documentation-for-models) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to README]](../README.md)


