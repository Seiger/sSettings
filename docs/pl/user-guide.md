# Przewodnik użytkownika

## Otwieranie sSettings

W managerze Evolution CMS wybierz **Narzędzia -> Ustawienia użytkownika**. Moduł
ma dwie zakładki:

- **Ustawienia** - edycja wartości.
- **Konfiguracja** - edycja zakładek, pól, etykiet, opisów i typów pól.

Interfejs używa evo-ui i Livewire, więc przełączanie zakładek oraz zapisywanie
odświeża tylko obszar modułu.

## Ekran ustawień

Ekran Ustawienia pokazuje jedną zakładkę konfiguracji naraz. Każdy wiersz pola
zawiera:

- etykietę pola;
- klucz systemowy, na przykład `[(sset_phone)]`;
- kontrolkę wejściową;
- opis pola pod kontrolką.

Opis wyjaśnia, gdzie wartość jest używana na stronie. Po zmianach kliknij
**Zapisz**.

## Typy pól

| Typ | Zastosowanie |
| --- | --- |
| Text | Krótkie wartości, takie jak email, telefon, URL lub ID. |
| Textarea | Dłuższy tekst, HTML lub skrypty śledzenia. |
| TextareaMini | Krótkie wartości wielowierszowe. |
| RichText | Formatowana tresc w manager rich editor. |
| DropDown List Menu | Jedna wartosc z kompaktowej listy dropdown. |
| Listbox | Jedna albo wiele wartosci z wyzszej listy. |
| Radio Options | Jedna wartosc z widocznych opcji. |
| Checkbox Group | Wiele wartosci z widocznych opcji. |
| Image | Ścieżka obrazu wybrana przez helper managera. |
| File | Ścieżka pliku wybrana przez helper managera. |
| Checkbox | Flaga włączone lub wyłączone. |
| Divider | Separator wizualny; nie zapisuje wartości. |

Checkbox jest zapisywany jako `1` albo `0`. Pola z opcjami uzywaja wierszy
value/label. Plus dodaje opcje po aktualnym wierszu, kosz usuwa, strzalki
sortuja recznie, a drag handle zmienia kolejnosc listy.

## Ekran konfiguracji

Ekran Konfiguracja zmienia schemat, który buduje ekran Ustawienia.

Wiersz zakładki zawiera:

- uchwyt przeciągania;
- klucz zakładki;
- przetłumaczoną etykietę zakładki;
- kompaktowe akcje dodania zakładki, dodania pola albo usunięcia zakładki.

Wiersz pola zawiera:

- uchwyt przeciągania;
- etykietę pola;
- chip klucza systemowego;
- opis pola;
- chip typu pola;
- akcje ustawień, dodania po bieżącym i usunięcia.

Kliknij ikonę ustawień pola, aby otworzyć kompaktowe okno modalne.

## Zmiana kolejności

Użyj uchwytu przeciągania, aby zmienić kolejność zakładek lub pól. Pola można
przenosić w zakładce albo między zakładkami. Opcje w modalu pola używają tego
samego EvoUI drag handle i mają strzałki do precyzyjnego ręcznego sortowania.

## Klucze systemowe

Każde pole wartości tworzy ustawienie systemowe Evolution:

```text
nazwa pola: phone
klucz systemowy: sset_phone
tag szablonu: [(sset_phone)]
```

Zmiana nazwy pola zmienia klucz systemowy po zapisaniu Konfiguracji.

## Zapisywanie i uprawnienia

Ekran Ustawienia zapisuje wartości. Ekran Konfiguracja zapisuje schemat i
synchronizuje ustawienia systemowe. Konfiguracja wymaga uprawnienia Evolution
`settings`.

Jeśli plik schematu nie jest zapisywalny, sSettings pokaże błąd zapisu.

## Rozwiązywanie problemów

- Jeśli pole zniknęło, sprawdź zakładkę Konfiguracja i zapisz schemat.
- Jeśli wartość nie pojawia się na frontendzie, wyczyść cache Evolution po
  zapisaniu.
- Jeśli Konfiguracja jest niedostępna, sprawdź uprawnienie `settings` w roli
  managera.
- Jeśli etykieta wygląda jak `sSettings::global.email`, brakuje tłumaczenia dla
  aktywnego języka managera.
