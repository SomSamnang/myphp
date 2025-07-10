<?php
function formatKhDateTime($datetime) {
  $dateTime = new DateTime($datetime, new DateTimeZone("UTC")); // assume DB is in UTC
  $dateTime->setTimezone(new DateTimeZone("Asia/Phnom_Penh"));  // convert to Phnom Penh

  $date = $dateTime->format("d-F-Y");
  $time = $dateTime->format("g:i A");

  return [$date, $time];
}
?>