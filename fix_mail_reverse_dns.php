<?php
////
//
// Eesti veebimajutus http://www.ibn.ee 
//
////
// This script will parse the automatically generated mail_reverse_dns file 
// and will regenerate the file with the real PTR entries. 
// Just set the email and and add this to /usr/local/cpanel/scripts/postupcp
//
////
$file_mail_reverse_dns = "/etc/mail_reverse_dns";

$admin_email = "xxx@ibn.ee";
$email_subject = "mail_reverse_ptr notice";

$ptr_regexp = "domain name pointer ([a-zA-Z0-9].{1,})*.";
$cmd_check_ptr = "host";

$output_content = array();
$ip_list = array();
$domain_list = array();

$lines = file($file_mail_reverse_dns);

foreach ($lines as $line_num => $line)
{
    list($ip, $domain) = explode(":", $line);
    $ip_list[] = trim($ip);
    $domain_list[] = trim($domain);

    exec($cmd_check_ptr." ".trim($ip), $exec_output);
}

foreach ($exec_output as $output_num => $output)
{
    $regexp_groups = array();
    ereg($ptr_regexp, $output, $regexp_groups);
    list($response, $ptr) = $regexp_groups;
    $output_content[] = $ip_list[$output_num].": ".((0 < strlen($ptr)) ? $ptr : $domain_list[$output_num]);    
}

//Validate the output. If the nr of lines in the output == nr of lines in original file, let's continue
if(count($lines) == count($output_content))
{
    $fp = fopen($file_mail_reverse_dns, 'w');
    fwrite($fp, implode("\n", $output_content));
    fclose($fp);
}
else
{
    mail($admin_email, $email_subject, "Creating new mail_reverse_ptr file failed.\nOriginal file:\n".implode("", $lines)."\nProposed output:\n".implode("\n", $output_content));
}

?> 

