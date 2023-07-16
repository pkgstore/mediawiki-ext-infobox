# Information

Интеграция информационных блоков в статью.

## Install

1. Загрузите папки и файлы в директорию `extensions/MW_EXT_InfoBox`.
2. В самый низ файла `LocalSettings.php` добавьте строку:

```php
wfLoadExtension( 'MW_EXT_InfoBox' );
```

## Syntax

```html
{{#infobox: type = Website
| image =
| title = {{BASEPAGENAME}}
| type =
| url =
| owner =
| creator =
| language =
}}
```
