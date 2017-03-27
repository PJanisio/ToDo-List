ToDo List 1.0.3 - updated 
=================================
All glory to plugin creator - MyBBService.de.
This repository has been created after few fixes and updates which was done to fit my requirements.

- task creator will get PM after any change in the task (progress,
priority etc)
- administrators can edit and delete all task no matter if they has been
marked in settings (hardcoded now)
- if you are not administrator you can only edit or delete your own
created tasks ( fix that every user can edit or delete)
- predefinied text in textrarea during editing or new task: Nickname and
current datestamp.

In addition:
Please edit your language file @ : /forum/inc/languages/lang_name/global.lang.php
and add:

```php
//current time
$l['current_time'] = date("Y-m-d H:i:s");
```

ToDo List 1.0 von MyBBService.de
=================================

Verwaltet eure Projekte mit der ToDo List von MyBBService
-------------------------------------------

### Installation
Wenn ihr das Plugin runtergeladen habt, entpackt das ZIP-File und ladet die Datei todolist.php sowie die Ordner admin, images und inc
in euren Forenroot. Also dorthin wo sich auch die index.php befindet.

Geht nun ins ACP und aktiviert das Plugin. Habt ihr alle Ordner und Dateien richtig hochgeladen,
seht ihr nun im ACP unter `Konfiguration` auf der linken Seite den Menüpunkt ToDo-Liste. Darüber
könnt ihr neue Projekte erstellen und verwalten. Die dortigen Funktionen sind
eigentlich selbsterklärend und gut beschrieben.

Der Aufruf im Frontend erfolgt mit: http://www.deineseite.de/todolist.php

### Anwendungsbeispiel
Eure User im Forum machen diverse Vorschläge wie das Forum verbessert/erweitert werden könnte.
Mit unserer ToDo Liste könnt ihr für jede Verbesserung ein entsprechendes Projekt anlegen und dort den Status der Arbeiten einpflegen. So haben eure User immer
den vollen Überblick wie weit die Arbeiten schon vorangeschritten sind.

### Wichtiger Hinweis
`Alle auf MyBBservice.de erhältlichen Plugins, Themes und Grafiken unterliegen dem Urheberrecht und
dürfen weder komplett oder auch nur auszugsweise - ohne schriftliches Einverständnis weitergegeben,
verkauft oder anderweitig verwendet bzw. veröffentlicht werden!
Zuwiderhandlungen werden strafrechtlich verfolgt! Support ausschließlich bei MyBBService.de.`
