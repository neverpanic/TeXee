<?php

$L = array(

//----------------------------------------
// Required for extension settings page
//----------------------------------------
"ldelimiter" =>
"Linker Begrenzer für TeXee-Block",

"rdelimiter" =>
"Rechter Begrenzer für TeXee-Block",

"tag_name" =>
"Name des TeXee-Tags",

"cache_cutoff" =>
"maximale Cachedauer in Sekunden",

"encoding" =>
'Zeichenkodierung für LaTeX. Mögliche Werte entsprechen den möglichen Werten des LaTeX-Pakets inputenc',

"method" =>
"Render-Methode",

"method_wpcom" =>
"wordpress.com Service (keine LaTeX-Installation erforderlich)",

"method_dvipng" =>
"PNG-Datei durch lokales DVI (LaTeX-Installation erforderlich)",

"method_dvips" =>
"PNG-Datei durch lokales DVI und PS (LaTeX-Installation erforderlich)",

"default_color" =>
"Textfarbe, falls keine angegeben wurde",

"default_background" =>
"Hintergrundfarbe, falls keine angegeben wurde",

"default_size" =>
"Textgröße, falls keine angegeben wurde",

"img_tag" =>
"HTML-Code des img-Tags, %1\$s wird durch die Bild-URL, %2\$s durch den Alt-Text ersetzt",

"yes" =>
"Ja",

"no" =>
"Nein",

"check_for_updates" =>
"Auf der Webseite des Autors nach Aktualisierungen suchen",

"latex_path" =>
"Absoluter Pfad zum latex-Binary (nur für lokale Render-Methoden erforderlich)",

"dvipng_path" =>
"Absoluter Pfad zum dvipng-Binary (nur für die Render-Methode PNG durch DVI erforderlich)",

"outdir" =>
"Pfad mit abschließendem Slash zum Verzeichnis, in dem generierte Dateien gespeichert werden sollen",

"outurl" =>
"URL dieses Verzeichnisses (auch mit abschließendem Slash)",

"size_tiny" =>
"\\\\tiny",

"size_scriptsize" =>
"\\\\scriptsize",

"size_footnotesize" =>
"\\\\footnotesize",

"size_small" =>
"\\\\small",

"size_normalsize" =>
"normal (12pt)",

"size_large" =>
"\\\\large",

"size_Large" =>
"\\\\Large",

"size_LARGE" =>
"\\\\LARGE",

"size_huge" =>
"\\\\huge",

"errmsg" =>
"[Beim rendern dieser Formel ist ein Fehler aufgetreten: %d: %s]",

'EINVRENDER' =>
'ungültige Render-Methode: %s',

'ENOINPUT' =>
'leere Formel',

'EMALICIOUS' =>
'möglicherweise bösartige Formel',

'EMATHMODE' =>
'Formel verlässt den Inline-Math-Mode',

'ETOOLONG' =>
'Formel zu lang',

'ETMPFILE' =>
'Fehler beim Erstellen einer temporären Datei',

'ETPLFILE' =>
'Fehler beim Lesen der .tex-Template-Datei',

'ETEXWRITE' =>
'Fehler beim Schreiben der .tex-Datei',

'EPARSE' =>
'Fehler beim Ausführen von latex: Rückgabewert %d, Ausgabe: %s',

'ECREATODIR' =>
'Kann Ausgabeverzeichnis %s nicht erstellen',

'EWRITODIR' =>
'Fehler beim Schreiben der Ausgabe-Datei %s',

'EDVIPNG' =>
'Fehler beim Ausführen von dvipng: Rückgabewert %d, Ausgabe %s',

'EDVIPS' =>
'Fehler beim Ausführen von dvips: Rückgabewert %d, Ausgabe %s',

'ECONVERT' =>
'Fehler beim Ausführen von convert: Rückgabewert %d, Ausgabe %s',

//----------------------------------------
// END
''=>''
);
?>
