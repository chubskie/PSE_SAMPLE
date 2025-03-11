<?php

// Input security
function sanitizeInput($input) {
    if ($input == null) {
        return null;
    }
    
    return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, "UTF-8");
}

// For residents
function generateResidentCode($family_name, $birth_date) {
    $code = "";

    // Remove in-between spaces in family name
    $family_name = str_replace(" ", "", $family_name);

    $code = substr($family_name, 0, 3);                                     // Get the first 3 characters of family name
    $code .= "-" . date_format(date_create($birth_date), "dMY");            // Get birth date in dMY format
    $code .= "-" . str_pad(random_int(1, 99999), 5, "0", STR_PAD_LEFT);     // Generate random 5-digit number (padded with zeroes)

    return strtoupper($code);                                               // Make sure code is in uppercase
}