# Yii1 Excel Importer
<p>Will Import CSV file and create CActiveRecords<p>
<p>You can import models with default values and also check them for unique.<p>
<p>If there are more 1+ attributes with unique set to true then it will search single model by 1+ attributes.</p>

Usage
--------------------------
```php
$file = CUploadedFile::getInstanceByName('file');

$importer = new YiiExcelImporter($file->tempName);
$importer->import('PRODUCT', [
      [
        'attribute' => 'REFERENCE',
        'columnNumber' => 0,
        'unique' => true,
      ],
      [
        'attribute' => 'NAME',
        'columnNumber' => 16
      ],
      [
        'attribute' => 'PRICE',
        'value' => 16.44
      ],
      [
        'attribute' => 'DESCRIPTION',
        'value' => 'Default description imported'
      ],
]);
```
