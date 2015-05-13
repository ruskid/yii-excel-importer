<?php

/**
 * @copyright Copyright Victor Demin, 2015
 * @license https://github.com/ruskid/yii-excel-importer/LICENSE
 * @link https://github.com/ruskid/yii-excel-importer#readme
 */

/**
 * Little CSV import helper for yii1
 * @author Victor Demin <demin@trabeja.com>
 */
class YiiExcelImporter {

    /**
     * Excel's parsed rows. Arrays of arrays
     * @var array
     */
    private $_rows = [];

    /**
     * Rows getter
     * @return array
     */
    public function getRows() {
        return $this->_rows;
    }

    /**
     * @param string $filename
     * @param integer $start Start from 1 if there is HEADER row.
     * @param integer $expectedColsCount Validate import by counting columns and expected number of columns.
     * @throws Exception
     */
    public function __construct($filename, $startRow = 1, $expectedColsCount = null) {
        if (!file_exists($filename)) {
            throw new Exception(__CLASS__ . ' couldn\'t find the CSV file.');
        }
        //Read file and save all rows
        $allRows = $this->getAllRows($filename);

        //Validate expected count of columns
        if ($expectedColsCount && !$this->validateExpectedColsCount($allRows, $expectedColsCount)) {
            throw new Exception(__CLASS__ . ' couldn\'t import the CSV file. It is expecting ' . $expectedColsCount .
            ' columns for the import and but csv file got less or more then that.');
        }

        //Filter rows
        $this->_rows = $this->removeUnusedRows($allRows, $startRow);
    }

    /**
     * Will compare epxected count of columns with imported columns of CSV file.
     * @param array $rows
     * @param integer $total
     * @return boolean
     */
    private function validateExpectedColsCount($rows, $total) {
        foreach ($rows as $line) {
            if (count($line) != $total) {
                return false;
            }
        }
        return true;
    }

    /**
     * Will set rows reading the CSV file.
     * @param string $filename
     * @param integer $start
     * @return array
     */
    private function getAllRows($filename) {
        $allRows = [];
        if (($fp = fopen($filename, 'r')) !== FALSE) {
            while (($line = fgetcsv($fp, 0, ";")) !== FALSE) {
                array_push($allRows, $line);
            }
        }
        return $allRows;
    }

    /**
     * Will remove unused rows by start row index.
     * @param array $rows
     * @param integer $start
     * @return array
     */
    private function removeUnusedRows($rows, $start) {
        for ($i = 0; $i < $start; $i++) {
            unset($rows[$i]);
        }
        return $rows;
    }

    /**
     * Import from CSV. This will create/save an CActiveRecord object per excel row.
     *
     * - attribute is the attribute of the CActiveRecord
     * - columnNumber is the column index in CSV. So the attribute will get it's value.
     * - value default value you can set on insert.
     *
     * $importer->import(\PRODUCT::class, [
     *      [
     *          'attribute' => 'NAME',
     *          'columnNumber' => 0,
     *          'unique' => true
     *      ],
     *      [
     *          'attribute' => 'TITLE',
     *          'columnNumber' => 16
     *      ],
     *      [
     *          'attribute' => 'PRICE',
     *          'value' => 45.5
     *      ]
     * ]);
     *
     * @param string $class CActiveRecord class name
     * @param array $configs Attribute config on how to import data.
     * @return integer Number of successful inserts
     */
    public function import($class, $configs) {
        $rows = $this->getRows();
        $countInserted = 0;
        foreach ($rows as $line) {
            /* @var $model CActiveRecord */
            $model = new $class;
            $uniqueAttributes = [];
            foreach ($configs as $config) {
                if (isset($config['attribute']) && $model->hasAttribute($config['attribute'])) {
                    $value = $this->getFinalValue($line, $config);
                    //Create array of unique attributes and the values to insert for later check
                    if (isset($config['unique']) && $config['unique']) {
                        $uniqueAttributes[$config['attribute']] = $value;
                    }
                    //Set values to the model
                    $model->setAttribute($config['attribute'], $value);
                }
            }
            //Save model if passes unique check
            if ($this->isModelUnique($class, $uniqueAttributes)) {
                $countInserted = $countInserted + $model->save();
            }
        }
        return $countInserted;
    }

    /**
     * Will return final value to import. Search excel line or return default value
     * @param array $excelLine
     * @param array $config Attribute config
     * @throws Exception
     */
    private function getFinalValue($excelLine, $config) {
        if (!isset($config['columnNumber']) && !isset($config['value'])) {
            throw new Exception("You didn't define 'columnNumber' or 'value' for the attribute.");
        }
        return isset($config['columnNumber']) ? $excelLine[$config['columnNumber']] : $config['value'];
    }

    /**
     * Will class for unique before creating. TODO. use exists.
     * @param CActiveRecord $class
     * @param array $attributes
     * @return boolean
     */
    private function isModelUnique($class, $attributes) {
        if (empty($attributes)) {
            return true;
        }
        $object = $class::model()->findByAttributes($attributes);
        return $object == null ? true : false;
    }

}
