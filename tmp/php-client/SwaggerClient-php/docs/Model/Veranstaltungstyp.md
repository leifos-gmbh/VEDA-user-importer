# Veranstaltungstyp

## Properties
Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**oid** | **string** | UUID des Datensatzes | 
**anzahl_teilnehmertage** | **float** | Die Anzahl der Teilnehmertage des Veranstaltungstyps | [optional] 
**anzahl_ue** | **float** | Die Anzahl der Unterrichtseinheiten des Veranstaltungstyps | [optional] 
**beschreibung** | **string** | Die Beschreibung des Veranstaltungstyps (HTML möglich) | [optional] 
**dauer_in_tagen** | **float** | Die Dauer in Tagen des Veranstaltungstyps | [optional] 
**gueltig_ab** | [**\DateTime**](\DateTime.md) | Der Gültigkeitsbeginn des Veranstaltungstyps | [optional] 
**gueltig_bis** | [**\DateTime**](\DateTime.md) | Das Gültigkeitsende des Veranstaltungstyps | [optional] 
**hinweise** | **string** | Hinweise zum Veranstaltungstyp | [optional] 
**inhalt** | **string** | Der Inhalt des Veranstaltungstyps (HTML möglich) | [optional] 
**kurzbezeichnung** | **string** | Die Kurzbezeichnung des Veranstaltungstyps | [optional] 
**links** | [**\Swagger\Client\Model\Link[]**](Link.md) |  | [optional] 
**methodik** | **string** | Die Methodik des Veranstaltungstyps (HTML möglich) | [optional] 
**regulaere_uhrzeit_bis** | **string** | Die Reguläre bis Uhrzeit des Veranstaltungstyps. | [optional] 
**regulaere_uhrzeit_von** | **string** | Die Reguläre von Uhrzeit des Veranstaltungstyps. | [optional] 
**teilnehmermaximum** | **int** | Das Teilnehmermaximum des Veranstaltungstyps | [optional] 
**teilnehmerminimum** | **int** | Das Teilnehmerminimum des Veranstaltungstyps | [optional] 
**thema** | **string** | Das Thema des Veranstaltungstyps | 
**thema2** | **string** | Thema 2 des Veranstaltungstyps | [optional] 
**uhrzeit_bis** | **string** | Die bis Uhrzeit des Veranstaltungstyps, am letzten Tag des Veranstaltungstyps. | [optional] 
**uhrzeit_von** | **string** | Die von Uhrzeit des Veranstaltungstyps, am ersten Tag des Veranstaltungstyps. | [optional] 
**veranstaltungs_nr** | **string** | Die Veranstaltungs-Nr. des Veranstaltungstyps | 
**veranstaltungsart** | **string** | Veranstaltungsart des Veranstaltungstyps, zulässig sind hier VIRTUELL und PRAESENZ. | [optional] 
**veranstaltungskategorie** | [**\Swagger\Client\Model\Veranstaltungskategorie**](Veranstaltungskategorie.md) | Die Kategorie, der der Veranstaltungstyp zugeordnet ist. | [optional] 
**voraussetzung** | **string** | Die Voraussetzungen für den Veranstaltungstyp (HTML möglich) | [optional] 
**wbd_relevant** | **bool** | Dieses Kennzeichen gibt an, ob der Veranstaltungstyp relevant für die Weiterbildungsdatenbank(WBD) ist | [optional] 
**wbd_thema** | **string** | Das WBD-Thema des Veranstaltungstyps | [optional] 
**ziel** | **string** | Die Ziele des Veranstaltungstyps (HTML möglich) | [optional] 
**zielgruppen** | [**\Swagger\Client\Model\Zielgruppe[]**](Zielgruppe.md) | Die Zielgruppen für den Veranstaltungstyp. | [optional] 

[[Back to Model list]](../README.md#documentation-for-models) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to README]](../README.md)


