# Yii1 Excel Importer
<p>Will Import CSV file and create CActiveRecords<p>
<p>If there are more 1+ attributes with unique set to true then it will search single model by 1+ attributes.</p>

Usage
--------------------------
```php
$file = CUploadedFile::getInstanceByName('file');

$importer = new YiiExcelImporter($file->tempName);
$importer->import('PRODUCTO', [
        [
            'attribute' => 'NOMBRE',
            'value' => '$row[0]',
            'unique' => true,
        ],
        [
            'attribute' => 'FABRICANTE',
            'value' => '$row[2]',
        ]
]);
$importer->import('PRODUCTO_DETAILS', [
        [
            'attribute' => 'PRODUCTO',
            'value' => 'PRODUCTO::model()->findByAttributes(["NOMBRE" => "$row[0]"])->ID'
        ],
        [
            'attribute' => 'RESPONSABLE',
            'value' => '$row[1]'
        ],
]);
```
