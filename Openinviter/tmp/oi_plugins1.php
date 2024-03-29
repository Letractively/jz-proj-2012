<?php
$plugins=array(
'email'=>array('aol'=>array('name'=>"AOL",
'version'=>"1.5.4",
'description'=>"Get the contacts from an AOL account",
'base_version'=>"1.9.0",
'type'=>"email",
'check_url'=>"http://webmail.aol.com",
'requirement'=>"email",
'allowed_domains'=>array('0'=>"/(aol.com)/i"),
'imported_details'=>array('0'=>"nickname",
'1'=>"email_1",
'2'=>"email_2",
'3'=>"phone_mobile",
'4'=>"phone_home",
'5'=>"phone_work",
'6'=>"pager",
'7'=>"fax_work",
'8'=>"last_name")),
'gmail'=>array('name'=>"GMail",
'version'=>"1.4.8",
'description'=>"Get the contacts from a GMail account",
'base_version'=>"1.6.3",
'type'=>"email",
'check_url'=>"http://google.com",
'requirement'=>"email",
'allowed_domains'=>false,
'detected_domains'=>array('0'=>"/(gmail.com)/i",
'1'=>"/(googlemail.com)/i"),
'imported_details'=>array('0'=>"first_name",
'1'=>"email_1",
'2'=>"email_2",
'3'=>"email_3",
'4'=>"organization",
'5'=>"phone_mobile",
'6'=>"phone_home",
'7'=>"fax",
'8'=>"pager",
'9'=>"address_home",
'10'=>"address_work")),
'hotmail'=>array('name'=>"Live/Hotmail",
'version'=>"1.6.6",
'description'=>"Get the contacts from a Windows Live/Hotmail account",
'base_version'=>"1.8.0",
'type'=>"email",
'check_url'=>"http://login.live.com/login.srf?id=2",
'requirement'=>"email",
'allowed_domains'=>array('0'=>"/(hotmail)/i",
'1'=>"/(live)/i",
'2'=>"/(msn)/i",
'3'=>"/(chaishop)/i"),
'imported_details'=>array('0'=>"first_name",
'1'=>"email_1")),
'yahoo'=>array('name'=>"Yahoo!",
'version'=>"1.5.4",
'description'=>"Get the contacts from a Yahoo! account",
'base_version'=>"1.8.0",
'type'=>"email",
'check_url'=>"http://mail.yahoo.com",
'requirement'=>"email",
'allowed_domains'=>array('0'=>"/(yahoo)/i",
'1'=>"/(ymail)/i",
'2'=>"/(rocketmail)/i"),
'imported_details'=>array('0'=>"first_name",
'1'=>"email_1")))
);
?>