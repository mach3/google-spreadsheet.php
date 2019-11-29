
# Change Log

## 1.1.0

- add `$sheet->edit()` to update cells' value by row and column number manually
- add `$sheet->init()` to initialize sheet with header items

## 1.0.0

- rewrite all for Sheets API v4
- `config()` method moved to `Google_Spreadsheet_Sheet`
- `update()` method usage has been changed  
- `Google_Spreadsheet_Sheet` constructor no longer call fetch automatically  
  call manually `$sheet->fetch()` when starting to use

## 0.1.5

- improve: let Sheet::update() to receive condition as 1st argument

## 0.1.4

- improve: let client to throw exception when unexpected response is returned
- fix bug: undefined index notice when processing column which header is empty
