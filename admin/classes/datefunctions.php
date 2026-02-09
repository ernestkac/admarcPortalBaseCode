<?php

function periodToDisplayDateMonth($period)
{
    // Try to create DateTime from expected format (Ym)
    $dateTime = DateTime::createFromFormat('!Ym', $period, new DateTimeZone('UTC'));

    // If creation failed, create DateTime using today's date in the same format
    if (!$dateTime instanceof DateTime) {
        $today = new DateTime('now', new DateTimeZone('UTC'));
        $dateTime = DateTime::createFromFormat('!Ym', $today->format('Ym'), new DateTimeZone('UTC'));
    }

    $dateTime->modify('-9 months');
    return $dateTime->format('M Y');
}

function dateMonthToPreriod($month)
{
    // Try to create DateTime from input
    try {
        $dateTime = new DateTime($month, new DateTimeZone('UTC'));
    } catch (Exception $e) {
        // Fallback to today if invalid
        $dateTime = new DateTime('now', new DateTimeZone('UTC'));
    }

    if (!$dateTime instanceof DateTime) {
        $dateTime = new DateTime('now', new DateTimeZone('UTC'));
    }

    $dateTime->modify('+9 months');
    return $dateTime->format('Ym');
}

?>
