# Veranstaltungstermin

## Properties
Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**oid** | **string** | UUID des Datensatzes | 
**anz_teilnehmertage_pro_teilnehmer** | **float** | Anzahl Teilnehmertage pro Teilnehmer des Veranstaltungstermins | [optional] 
**anzahl_freier_plaetze** | **int** | Anzahl freier Plätze des Veranstaltungstermins | [optional] 
**anzahl_freier_wartelistenplaetze** | **int** | Anzahl freier Wartelistenplätze des Veranstaltungstermins | [optional] 
**anzahl_teilnehmerbuchungen** | **int** | Anzahl Teilnehmerbuchungen des Veranstaltungstermins | [optional] 
**anzahl_ue** | **float** | Anzahl der Unterrichtseinheiten des Veranstaltungstermins | [optional] 
**anzahl_wartelistenbuchungen** | **int** | Anzahl Wartelistenbuchungen des Veranstaltungstermins | [optional] 
**auslastungsstatus** | **string** | Auslastungsstatus des Veranstaltungstermins | [optional] 
**beschreibung** | **string** | Beschreibung des Veranstaltungstermins | [optional] 
**dauer_in_tagen** | **float** | Die Dauer in Tagen des Veranstaltungstermins | [optional] 
**hinweise** | **string** | Hinweise zum Veranstaltungstermin | [optional] 
**inhalt** | **string** | Inhalte  des Veranstaltungstermins | [optional] 
**links** | [**\Swagger\Client\Model\Link[]**](Link.md) |  | [optional] 
**methodik** | **string** | Methodik des Veranstaltungstermins | [optional] 
**preis** | **float** | Der Standardpreis des Veranstaltungstermins. | [optional] 
**preise_je_teilnehmergruppe** | [**\Swagger\Client\Model\TeilnehmergruppePreis[]**](TeilnehmergruppePreis.md) | Die Preise je nach Teilnehmergruppe des Veranstaltungstermins. | [optional] 
**reg_uhrzeit_bis** | **string** | Reguläres Ende des Veranstaltungstermins | [optional] 
**reg_uhrzeit_von** | **string** | Reguläre Startzeit des Veranstaltungstermins | [optional] 
**sprache** | **string** | Die Sprache, in der der Veranstaltungstermin durchgeführt wird. | [optional] 
**teilnehmermaximum** | **int** | Teilnehmermaximum des Veranstaltungstermins | [optional] 
**teilnehmerminimum** | **int** | Teilnehmerminimum des Veranstaltungstermins | [optional] 
**termin_bis** | [**\DateTime**](\DateTime.md) | Termin bis des Veranstaltungstermins | 
**termin_von** | [**\DateTime**](\DateTime.md) | Termin von des Veranstaltungstermins | 
**thema** | **string** | Das Thema des Veranstaltungstermins. | 
**thema2** | **string** | Thema 2 des Veranstaltungstermins. | [optional] 
**veranstaltungs_nr** | **string** | Die Veranstaltungs-Nr. des Veranstaltungstermins. | 
**veranstaltungsanbieter** | [**\Swagger\Client\Model\Veranstaltungsanbieter**](Veranstaltungsanbieter.md) | Der Veranstaltungsanbieter des Veranstaltungstermins. | [optional] 
**veranstaltungsart** | **string** | Veranstaltungsart des Veranstaltungstermins, zulässig sind hier VIRTUELL für Virtuell und PRAESENZ für Präsenz. | 
**veranstaltungskategorie** | [**\Swagger\Client\Model\Veranstaltungskategorie**](Veranstaltungskategorie.md) | Die Kategorie, der der Veranstaltungstermin zugeordnet ist. | [optional] 
**veranstaltungsort** | [**\Swagger\Client\Model\Veranstaltungsort**](Veranstaltungsort.md) | Der Veranstaltungsanbieter des Veranstaltungstermins. | [optional] 
**veranstaltungstyp_id** | **string** | Die ID des Veranstaltungstyps des Veranstaltungstermins. Ist die ID gesetzt so wird zusätzlich ein Link auf den Veranstaltungstypen geliefert. | 
**voraussetzung** | **string** | Voraussetzungen  des Veranstaltungstermins | [optional] 
**wartelistenmaximum** | **int** | Wartelistenmaximum des Veranstaltungstermins | [optional] 
**wbd_relevant** | **bool** | Flag der Veranstaltungstermins WBD relevant ist oder nicht | [optional] 
**wbd_thema** | **string** | WBD Thema des Veranstaltungstermins | [optional] 
**ziel** | **string** | Ziele  des Veranstaltungstermins | [optional] 
**zielgruppen** | [**\Swagger\Client\Model\Zielgruppe[]**](Zielgruppe.md) | Die Zielgruppen für den Veranstaltungstermin. | [optional] 

[[Back to Model list]](../README.md#documentation-for-models) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to README]](../README.md)


