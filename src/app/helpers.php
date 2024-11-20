<?php

if (!function_exists('formatted_datetime')) {
    /**
     * フォーマット済み日時を返す
     *
     * @param \Carbon\Carbon $datetime
     * @return string
     */
    function formatted_datetime($datetime) 
    {
        return $datetime->format('Y-m-d');
    }
}