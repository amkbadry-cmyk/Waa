<?php
$ch = curl_init('http://localhost/api/wa.php');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    'action' => 'send',
    'phone' => '+201029306733',
    'message' => 'مرحبا بك في نظام واتساب'
]));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
echo curl_exec($ch);
curl_close($ch);
?>