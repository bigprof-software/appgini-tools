# Tool to fix encoding of captions and description in AXP project files

For non-latin AXP project files, in some scenarios that I'm currently investigating, the captions and descriptions
could become HTML-entity-encoded. For example, a field caption might show up in AppGini as:

```
&#1492;&#1508;&#1506;&#1500;&#1514; &#1492;&#1491;&#1493;"&#1495;
```

instead of the original non-latin (Hebrew in this example) caption of `הפעלת הדו"ח`.
The `fix-axp-unicode.php` file can fix this issue. Upload this file to your server 
(preferably into some obscure or protected directory) and visit it in your browser.
It would show a form to upload the AXP file. It should then fix the encoding and
the fixed AXP file would be downloaded to your default download folder.

#### [Here is the file](fix-axp-unicode.php) (click 'Download raw file').
