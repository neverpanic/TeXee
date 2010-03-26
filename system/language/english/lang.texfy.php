<?php

$L = array(

//----------------------------------------
// Required for extension settings page
//----------------------------------------
"ldelimiter" =>
"Left delimiter for LaTeX code block",

"rdelimiter" =>
"Right delimiter for LaTeX code block",

"tag_name" =>
"Tag name for LaTeX code block",

"cache_cutoff" =>
"Cache time in seconds",

"encoding" =>
'Encoding to use for LaTeX. Valid values are those valid for the latex inputenc package',

"method" =>
"Rendering mechanism",

"method_wpcom" =>
"wordpress.com service (no installation needed)",

"method_dvipng" =>
"PNG files via local DVI (requires local LaTeX-installation)",

"method_dvips" =>
"PS? files via local DVI (requires local LaTeX-installation)",

"default_color" =>
"Text color when none is specified",

"default_background" =>
"Background color when none is specified",

"default_size" =>
"Text size when none is specified",

"img_tag" =>
"img-tag HTML, %1\$s will be replaced by the image URL, %2\$s is the alt-text",

"yes" =>
"Yes",

"no" =>
"No",

"check_for_updates" =>
"Check for updates at the author's web site",

"latex_path" =>
"Absolute path to the latex executable (required for any local method)",

"dvipng_path" =>
"Absolute path to the dvipng executable (required for PNG files via DVI only)",

"outdir" =>
"Path (with trailing slash) to the directory, where generated files will be stored",

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
"[An error occured rendering this formula: %1\$d: %2\$s]",

'EINVRENDER' =>
'invalid render method: %s',

'ENOINPUT' =>
'formula empty',

'EMALICIOUS' =>
'potentially dangerous formula',

'EMATHMODE' =>
'formula leaves inline math mode',

'ETOOLONG' =>
'formula too long',

'ETMPFILE' =>
'error creating temporary .tex file',

'ETPLFILE' =>
'error reading .tex template file',

'ETEXWRITE' =>
'error writing .tex file',

'EPARSE' =>
'error running latex: return code %d, output: %s',

'ECREATODIR' =>
'unable to create output directory %s',

'EWRITODIR' =>
'error writing output file %s',

'EDVIPNG' =>
'error running dvipng: return code %d, output %s',

//----------------------------------------
// END
''=>''
);
?>
