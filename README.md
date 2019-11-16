
# Google Spreadsheet Client for PHP


Google Spreadsheet Client for PHP. This requires 'google/apiclient' package.


## Get started

### 1. Get key file

1. Log in [Google Developer Console](https://console.developers.google.com)
2. Create new project
3. Create **Service Account** credentials in the project
4. Download key file as JSON

### 2. Create spreadsheet

1. Create a new spreadsheet in [Google Drive](https://drive.google.com)
2. Authorize the email address, which is found as 'client_email' in key file, to read and edit.
3. Save the **file ID** from address bar.

### 3. Access by PHP

```php
$client = Google_Spreadsheet::getClient('the/path/to/credential.json');
// Get the sheet instance by sheets_id and sheet name
$sheet = $client->file('XXXxxxXXXXxxxXXXX')->sheet('Sheet1');
// Fetch data from remote (or cache)
$sheet->fetch();
// Flush all rows in the sheet
var_dump($sheet->items);
```

## Usage

### Initialize sheet (>= 1.1.0)

The target sheet must be empty

```php
$sheet->init(array(
  'id',
  'name',
  'age',
  'email',
  'note'
));
```

### Select rows

```php
// Array
$items = $sheet->select(array(
  'id' => '1'
));
// Closure
$items = $sheet->select(function($row){
  return (int) $row['age'] < 30;
});
```

### Insert a new row

```php
// Insert a new row
$sheet->insert(array(
  'name' => 'John',
  'age' => 23,
  'email' => 'john@example.com'
));

// Get up-to-date items
$items = $sheet->fetch(true)->items;
```

### Update rows

```php
// Update rows selected by array
$sheet->update(
  array(
    'email' => 'tom@example.com'
  ),
  array(
    'name' => 'Tom'
  )
);

// Update rows selected by closure
$sheet->update(
  array(
    'email' => 'tom@example.com'
  ),
  function($row){
    return $row['name'] === 'Tom';
  }
);

// Get up-to-date items
$items = $sheet->fetch(true)->items;
```

### Update cells (>=1.1.0)

`edit` method let you to update cells' value manually

```php
// Update `B2` cell
$sheet->edit(2, 2, 'Tom');

// Update `C1:C4` cells
$sheet->edit(3, 1, array(1, 'John', 23, 'john@example.com'));
```

### Get up-to-date table data

```php
// Pass `true` to ignore cache
$items = $sheet->fetch(true)->items;
```

### Save cache option

```php
$sheet->config(array(
  'cache' => true,
  'cache_dir' => __DIR__ . '/cache',
  'cache_expires' => 360
));
```


## Requirement

- [google/apiclient](https://github.com/google/google-api-php-client) (Apache License v2.0)

