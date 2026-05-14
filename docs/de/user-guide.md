# Benutzerhandbuch

## sSettings öffnen

Öffnen Sie im Evolution CMS Manager **Werkzeuge -> Benutzereinstellungen**. Das
Modul hat zwei Tabs:

- **Einstellungen** - Werte bearbeiten.
- **Konfiguration** - Tabs, Felder, Labels, Beschreibungen und Feldtypen
  bearbeiten.

Die Oberfläche verwendet evo-ui und Livewire. Tabwechsel und Speichern
aktualisieren nur den Modulbereich.

## Einstellungsansicht

Die Einstellungsansicht zeigt jeweils einen Konfigurationstab. Jede Feldzeile
enthält:

- das Feldlabel;
- den Systemschlüssel, zum Beispiel `[(sset_phone)]`;
- das Eingabefeld;
- die Beschreibung unter dem Eingabefeld.

Die Beschreibung erklärt, wo der Wert auf der Website verwendet wird. Klicken
Sie nach Änderungen auf **Speichern**.

## Feldtypen

| Typ | Verwendung |
| --- | --- |
| Text | Kurze Werte wie E-Mail, Telefon, URL oder ID. |
| Textarea | Längere Texte, HTML-Fragmente oder Tracking-Skripte. |
| TextareaMini | Kurze mehrzeilige Werte. |
| RichText | Formatierter Inhalt im Manager rich editor. |
| DropDown List Menu | Ein Wert aus einer kompakten Dropdown-Liste. |
| Listbox | Ein oder mehrere Werte aus einer hoeheren Liste. |
| Radio Options | Ein Wert aus sichtbaren Optionen. |
| Checkbox Group | Mehrere Werte aus sichtbaren Optionen. |
| Image | Bildpfad, ausgewählt über den Manager-Helfer. |
| File | Dateipfad, ausgewählt über den Manager-Helfer. |
| Checkbox | Aktiviert oder deaktiviert. |
| Divider | Visuelle Trennlinie; speichert keinen Wert. |

Checkbox-Werte werden als `1` oder `0` gespeichert. Option-basierte Felder
verwenden value/label Zeilen. Plus fuegt eine Option nach der aktuellen Zeile
ein, der Papierkorb entfernt sie, Pfeile sortieren manuell und der drag handle
sortiert per DnD.

## Konfigurationsansicht

Die Konfigurationsansicht ändert das Schema, aus dem die Einstellungsansicht
gebaut wird.

Eine Tabzeile enthält:

- Drag Handle;
- Tab-Schlüssel;
- übersetztes Tablabel;
- kompakte Aktionen zum Hinzufügen eines Tabs, Hinzufügen eines Feldes oder
  Entfernen des Tabs.

Eine Feldzeile enthält:

- Drag Handle;
- Feldlabel;
- Systemschlüssel-Chip;
- Feldbeschreibung;
- Feldtyp-Chip;
- Aktionen für Einstellungen, Hinzufügen danach und Löschen.

Klicken Sie auf das Einstellungssymbol eines Feldes, um das kompakte Modal zu
öffnen.

## Reihenfolge ändern

Verwenden Sie den Drag Handle, um Tabs oder Felder neu zu sortieren. Felder
koennen innerhalb eines Tabs oder zwischen Tabs verschoben werden.
Optionszeilen in Feldmodals nutzen denselben EvoUI drag handle und behalten
Pfeile fuer praezise manuelle Sortierung.

## Systemschlüssel

Jedes Wertefeld erstellt eine Evolution Systemeinstellung:

```text
Feldname: phone
Systemschlüssel: sset_phone
Template-Tag: [(sset_phone)]
```

Wenn der Feldname geändert wird, ändert sich der Systemschlüssel nach dem
Speichern der Konfiguration.

## Speichern und Berechtigungen

Die Einstellungsansicht speichert Werte. Die Konfiguration speichert das Schema
und synchronisiert Systemeinstellungen. Für die Konfiguration ist die Evolution
Berechtigung `settings` erforderlich.

Wenn die Schemadatei nicht schreibbar ist, zeigt sSettings einen Schreibfehler.

## Fehlerbehebung

- Wenn ein Feld fehlt, prüfen Sie den Tab Konfiguration und speichern Sie das
  Schema.
- Wenn ein Wert im Frontend nicht erscheint, leeren Sie nach dem Speichern den
  Evolution Cache.
- Wenn die Konfiguration nicht verfügbar ist, prüfen Sie die Berechtigung
  `settings` in der Managerrolle.
- Wenn ein Label wie `sSettings::global.email` aussieht, fehlt eine Übersetzung
  für die aktive Managersprache.
