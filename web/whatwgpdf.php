<?php
/* Written by Igor Zhbanov <izh1979@gmail.com>
 *
 * This script serializes generation of PDF documents because it's CPU-heavy
 * process. So it's allowed to run only one instance at a time.
 *
 * The script send emails on:
 * - Starting of PDF generation,
 * - On errors,
 * - When another instance of makepdf is already running.
 *
 * The letter about PDF generation status is sent by makepdf. */

$mail_to = "John Doe <j.doe@example.com>";
$mail_from = "Example hosting whatwgpdf.php <pdf-generator@example.com>";
$mail_subject_prefix = "Example hosting whatwg.php";

$LOG_DIR = "/var/log/makepdf";
$LOG_FILE = "$LOG_DIR/html5-whatwg.log";

$run_user = "makepdf";			 /* User to run makepdf as */
$run_command = "/usr/local/bin/makepdf"; /* Command for generating PDF */
$server_timezone = "Europe/Moscow";

/* ------------------------------------------------------------------------ */

function
send_mail($mail_status, $mail_body)
{
	global $mail_to, $mail_from, $mail_subject_prefix;

	$headers = "From: $mail_from\r\n".
		   "MIME-Version: 1.0\r\n".
		   "Date: ". date("r"). "\r\n".
		   "Content-type: text/plain; charset=UTF-8\r\n";
	mail($mail_to, "$mail_subject_prefix: $mail_status", $mail_body,
	     $headers);
}

/* ------------------------------------------------------------------------ */

header("Content-type: text/plain");
date_default_timezone_set($server_timezone);
putenv("TERM=linux");

if (($fh_log = @fopen($LOG_FILE, "r")) === false) {
	$mail_body = "Can't open log.\n";
	echo "ERROR: $mail_body";
	send_mail("Internal error (1)", $mail_body);
	exit(1);
}

if (!flock($fh_log, LOCK_EX | LOCK_NB)) {
	$mail_body = "Already running.\n";
	echo "ERROR: $mail_body";
	send_mail("Error", $mail_body);
	fclose($fh_log);
	exit(1);
}

/* There is a race condition between testing a lock in parent,
 * releasing it and reacquiring the lock in child. But it's
 * not critical. */
flock($fh_log, LOCK_UN);
fclose($fh_log);

$pid = pcntl_fork();
if (!$pid) { /* Child */
	if (pcntl_fork() == 0) {
		posix_setsid();
		pcntl_exec("/usr/bin/sudo",
			   array("-u", $run_user, $run_command));
	}

	posix_kill(posix_getpid(), SIGKILL);
} else if ($pid > 0) {
	$mail_body = "OK: Task started";
	echo "$mail_body.\n";
	send_mail($mail_body, "$mail_body.\n");
	pcntl_waitpid($pid, $status, WNOHANG);
} else {
	$mail_body = "Can't fork\n";
	echo "ERROR: $mail_body";
	send_mail("Internal error (2)", $mail_body);
}

?>
