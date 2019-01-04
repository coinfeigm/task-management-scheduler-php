<?php
require_once "deadlineWithHoliday.php";

function sanitizeDate($date, $isWBS)
{
    $invalidFormat = "Date format is invalid.";

    if ($date != "") {
        $test_arr = explode('-', $date);
        if (count($test_arr) == 3) {

            // Check if valid number
            for ($i = 0; $i < count($test_arr); $i++) {
                if (!(int)($test_arr[$i])) {
                    return $invalidFormat;
                }
            }

            // Check if valid date
            if (!checkdate($test_arr[1], $test_arr[2], $test_arr[0])) {
                return $invalidFormat;
            }

            // Check for year length
            if (strlen($test_arr[0]) != 4 || intval($test_arr[0]) < 1970) {
                return $invalidFormat;
            }

            // Check for month length
            if (strlen($test_arr[1]) != 2) {
                return $invalidFormat;
            }

            // Check for date length
            if (strlen($test_arr[2]) != 2) {
                return $invalidFormat;
            }

            // Check if weekend
            if (isWeekend($date) && !$isWBS) {
                return "Date is a weekend.";
            }

            // Check if holiday
            if (isHoliday(strtotime($date)) && !$isWBS) {
                return "Date is a holiday.";
            }

        } else {
            return $invalidFormat;
        }
    }
    return "";
}

function getDateArray($date) {
    $test_arr = explode('-', $date);
    return $test_arr;
}

function isEmpty($text) {
    return $text == "";
}

function containsValidChars($text) {
    // 000A      : new line
    // 0020-007E : alphabet, numbers and common punctuations
    // 2160-216B : roman numerals
    // 3000-303F : punctuation
    // 3040-309F : hiragana
    // 30A0-30FF : katakana
    // FF00-FFEF : Full-width roman + half-width katakana
    // 4E00-9FAF : Common and uncommon kanji

    $validChars = "/^[\x{2160}-\x{216B}\x{000A}\x{0020}-\x{007A}\x{3000}-\x{303F}\x{3040}-\x{309F}" .
        "\x{30A0}-\x{30FF}\x{FF00}-\x{FFEF}\x{4E00}-\x{9FAF}\x{2E80}-\x{2FD5}]+$/u";

    return preg_match($validChars, $text);
}

function exceedsMaxLength($text, $maxLength) {
    return (strlen($text) > $maxLength);
}