<?php
    function conditional_replace($conditional_expression, $comparition_result) {
        $false_part_pattern = '/ELSE.*\}(?:(?!\{ELSE.*\}|\{ENDIF\})[\s\S])*\{ENDIF/';
        if (strpos($conditional_expression, "ELSE") != 0) {
            $true_part_pattern = '/IF.*\}(?:(?!\{IF.*\}|\{ELSE\})[\s\S])*\{ELSE/';
            if ($comparition_result) {
                preg_match($true_part_pattern, $conditional_expression, $matched_true_part);
                $true_part = substr($matched_true_part[0], strpos($matched_true_part[0], '}') + 1);
                $true_part = substr($true_part, 0, strpos($true_part, '{'));
                return $true_part;
            }
            else {
                preg_match($false_part_pattern, $conditional_expression, $matched_false_part);
                $false_part = substr($matched_false_part[0], strpos($matched_false_part[0], '}') + 1);
                $false_part = substr($false_part, 0, strpos($false_part, '{'));
                return $false_part;
            }
        }
        else {
            $true_part_pattern = '/IF.*\}(?:(?!\{IF.*\}|\{ELSE\})[\s\S])*\{ENDIF/';
            if ($comparition_result) {
                preg_match($true_part_pattern, $conditional_expression, $matched_true_part);
                $true_part = substr($matched_true_part[0], strpos($matched_true_part[0], '}') + 1);
                $true_part = substr($true_part, 0, strpos($true_part, '{'));
                return $true_part;
            }
            else {
                $false_part = "";
                return $false_part;
            }
        }
    }

    //Получение текста страницы
    $page_text = file_get_contents('page-patterns/index.htm');

    //Обработка {YEAR}
    date_default_timezone_set('Europe/Minsk');
    $date = date('Y', time());
    $page_text = str_replace('{YEAR}', $date, $page_text);
    
    //Обработка substitution_file_path
    $file_substitution_pattern = '/\{\s*FILE=(?:(?!\{FILE=|\"\})[\s\S])*\"\s*\}/';
    preg_match($file_substitution_pattern, $page_text, $matched_file_pattern);
    $substitution_file_path = substr($matched_file_pattern[0], strpos($matched_file_pattern[0], '"') + 1);
    $substitution_file_path = substr($substitution_file_path, 0, strpos($substitution_file_path, '"'));
    $text_to_paste = file_get_contents($substitution_file_path);
    $page_text = str_replace($matched_file_pattern[0], $text_to_paste, $page_text);

    //Обработка BD
    $bd_path_pattern = '/\{\s*DB=(?:(?!\{DB=|\"\})[\s\S])*\"\s*\}/';
    preg_match($bd_path_pattern, $page_text, $matched_bd_pattern);
    $bd_path = substr($matched_bd_pattern[0], strpos($matched_bd_pattern[0], '"') + 1);
    $bd_path = substr($bd_path, 0, strpos($bd_path, '"'));
    $bd_paste_value = file_get_contents($bd_path);
    $bd_paste_value = $bd_paste_value + 1;
    file_put_contents($bd_path, $bd_paste_value);
    $page_text = str_replace($matched_bd_pattern[0], $bd_paste_value, $page_text);

    //Обработка условных конструкций
    $conditional_expression_pattern = '/\{IF(?:(?!\{IF|ENDIF\})[\s\S])*ENDIF\}/';
    preg_match($conditional_expression_pattern, $page_text, $matched_condition_pattern);
    
    $condition_pattern = "/\{IF\s*\"([[:alnum:]]*)\"\s*([==|>|<]*)\s\"([[:alnum:]]*)\"\}/";
    preg_match($condition_pattern, $matched_condition_pattern[0], $condition_parts);

    switch ($condition_parts[2]) {
        case '==':
            $result = conditional_replace($matched_condition_pattern[0], $condition_parts[1] == $condition_parts[3]);
            break;
        case '>=':
            $result = conditional_replace($matched_condition_pattern[0], $condition_parts[1] >= $condition_parts[3]);
            break;
        case '<=':
            $result = conditional_replace($matched_condition_pattern[0], $condition_parts[1] <= $condition_parts[3]);
            break;    
        case '<':
            $result = conditional_replace($matched_condition_pattern[0], $condition_parts[1] < $condition_parts[3]);
            break;
        case '>':
            $result = conditional_replace($matched_condition_pattern[0], $condition_parts[1] > $condition_parts[3]);
            break;
    }
    $page_text = str_replace($matched_condition_pattern[0], $result, $page_text);
 
    //echo $matched_condition_pattern[0];

    //Обработка VAR
    $PAGE_NAMES = array('index' => 'Главная',
                        'cubes' => 'Виды кубиков',
                        'solution' => 'Как собрать',
                        'records' => 'Рекорды',
                        'feedback' => 'Обратная связь');   //VAR 0
    $var_pattern = '/\{\s*VAR=(?:(?!\{VAR=|\"\})[\s\S])*\"\s*\}/';
    for ($i = 0; $i < count($PAGE_NAMES); $i++) {
        preg_match($var_pattern, $page_text, $matched_var_pattern);
        $var_name = substr($matched_var_pattern[0], strpos($matched_var_pattern[0], '"') + 1);
        $var_name = substr($var_name, 0, strpos($var_name, '"'));
        $page_text = str_replace($matched_var_pattern[0], $PAGE_NAMES[$var_name], $page_text);
    }
    echo $page_text;
?>