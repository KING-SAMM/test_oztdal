<?php 

class Validate {
    public function field($fieldValue, string $fieldType, string $pattern, string $validationMsg, array $arr): string
    {
        if(empty($fieldValue))
        {
            array_push($arr, $fieldType . " is required");
        }
        else
        {
            $fieldValue = htmlspecialchars($fieldValue);
            if(!preg_match($pattern,$fieldValue))
            {
                array_push($arr, 'Invalid: ' . $fieldType . ' - '. $validationMsg);
            }
        }
        $_SESSION['invalid_fields'] = $arr;
        return $fieldValue;
    }

    public function email($fieldValue, string $fieldType, string $validationMsg): string
    {
        if(empty($fieldValue))
        {
            $_SESSION['invalid_email'] = $fieldType . " is required"; 
        }
        else
        {
            $fieldValue = htmlspecialchars($fieldValue);
            if(!filter_var($fieldValue,FILTER_VALIDATE_EMAIL))
            {
                $_SESSION['invalid_email'] = 'Invalid: ' . $fieldType . ' - '. $validationMsg; 
            }
        }
        return $fieldValue;
    }

    public function repField($fieldValue, string $fieldType, $pattern, string $validationMsg, array $arr): string
    {
        if(empty($fieldValue))
        {
            array_push($arr, $fieldType . " is required");
        }
        else
        {
            $fieldValue = htmlspecialchars($fieldValue);
            if(!preg_match($pattern,$fieldValue))
            {
                array_push($arr, 'Invalid: ' . $fieldType . ' - '. $validationMsg);
            }
        }
        $_SESSION['invalid_fields'] = $arr;
        return $fieldValue;
    }
}