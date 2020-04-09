<?php
    $page_text = file_get_contents('page-patterns/feedback.htm');
    date_default_timezone_set('Europe/Minsk');
    $date = date('Y', time());
    $page_text = str_replace('{YEAR}', $date, $page_text);

    $PAGE_NAMES = array('index' => 'Главная',
                        'cubes' => 'Виды кубиков',
                        'solution' => 'Как собрать',
                        'records' => 'Рекорды',
                        'feedback' => 'Обратная связь');
    $var_pattern = '/\{\s*VAR=(?:(?!\{VAR=|\"\})[\s\S])*\"\s*\}/';
    for ($i = 0; $i < count($PAGE_NAMES); $i++) {
        preg_match($var_pattern, $page_text, $matched_var_pattern);
        $var_name = substr($matched_var_pattern[0], strpos($matched_var_pattern[0], '"') + 1);
        $var_name = substr($var_name, 0, strpos($var_name, '"'));
        $page_text = str_replace($matched_var_pattern[0], $PAGE_NAMES[$var_name], $page_text);
    }

    echo $page_text;
?>