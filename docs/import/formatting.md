# Custom formatting values

By default Laravel Excel uses PHPExcel's default value binder to intelligently format a cells value when reading it. You may override this behavior by passing in your own value binder to suit your specific needs. Value binders must implement PHPExcel_Cell_IValueBinder and have a bindValue method. They may also extend PHPExcel_Cell_DefaultValueBinder to return the default behavior.
    
    use PHPExcel_Cell;
    use PHPExcel_Cell_DataType;
    use PHPExcel_Cell_IValueBinder;
    use PHPExcel_Cell_DefaultValueBinder;

    class MyValueBinder extends PHPExcel_Cell_DefaultValueBinder implements PHPExcel_Cell_IValueBinder
    {
        public function bindValue(PHPExcel_Cell $cell, $value = null)
        {
            if (is_numeric($value))
            {
                $cell->setValueExplicit($value, PHPExcel_Cell_DataType::TYPE_NUMERIC);

                return true;
            }
            
            // else return default behavior
            return parent::bindValue($cell, $value);
        }
    }

    $myValueBinder = new MyValueBinder;

    Excel::setValueBinder($myValueBinder)->load('file.xls', function($reader) {

        // reader methods

    });

Available PHPExcel_Cell_DataType's are TYPE_STRING, TYPE_FORMULA, TYPE_NUMERIC, TYPE_BOOL, TYPE_NULL, TYPE_INLINE and TYPE_ERROR

To reset the value binder back to default and/or before calling Laravel Excel after setting a custom value binder you need to call the resetValueBinder method.

    Excel::resetValueBinder();