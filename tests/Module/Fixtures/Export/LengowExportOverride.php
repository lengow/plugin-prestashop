<?php

class LengowExportOverride extends LengowExport
{
    public static function setAdditionalFields($fields)
    {
        $fields[] = 'test1';
        $fields[] = 'test2';
        $fields[] = 'test3';
        return $fields;
    }
}
