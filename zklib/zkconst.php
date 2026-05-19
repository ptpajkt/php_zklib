<?php
define('USHRT_MAX', 65535);

// Command constants
define('CMD_CONNECT', 1000);
define('CMD_EXIT', 1001);
define('CMD_ENABLEDEVICE', 1002);
define('CMD_DISABLEDEVICE', 1003);
define('CMD_RESTART', 1004);
define('CMD_POWEROFF', 1005);
define('CMD_SLEEP', 1006);
define('CMD_RESUME', 1007);
define('CMD_CAPTUREFINGER', 1009);
define('CMD_TEST_TEMP', 1011);
define('CMD_CAPTUREIMAGE', 1012);
define('CMD_REFRESHDATA', 1013);
define('CMD_REFRESHOPTION', 1014);
define('CMD_TESTVOICE', 1017);
define('CMD_VERSION', 1100);
define('CMD_CHANGE_SPEED', 1101);
define('CMD_AUTH', 1102);
define('CMD_PREPARE_DATA', 1500);
define('CMD_DATA', 1501);
define('CMD_FREE_DATA', 1502);
define('CMD_DB_RRQ', 7);
define('CMD_DB_WRQ', 8);
define('CMD_USERTEMP_RRQ', 9);
define('CMD_USERTEMP_WRQ', 10);
define('CMD_OPTIONS_RRQ', 11);
define('CMD_OPTIONS_WRQ', 12);
define('CMD_ATTLOG_RRQ', 13);
define('CMD_CLEAR_DATA', 14);
define('CMD_CLEAR_ATTLOG', 15);
define('CMD_DELETE_USER', 18);
define('CMD_DELETE_USERTEMP', 19);
define('CMD_CLEAR_ADMIN', 20);
define('CMD_USERGRP_RRQ', 21);
define('CMD_USERGRP_WRQ', 22);
define('CMD_USERTZ_RRQ', 23);
define('CMD_USERTZ_WRQ', 24);
define('CMD_GRPTZ_RRQ', 25);
define('CMD_GRPTZ_WRQ', 26);
define('CMD_TZ_RRQ', 27);
define('CMD_TZ_WRQ', 27);
define('CMD_ULG_RRQ', 29);
define('CMD_ULG_WRQ', 30);
define('CMD_UNLOCK', 31);
define('CMD_CLEAR_ACC', 32);
define('CMD_CLEAR_OPLOG', 33);
define('CMD_OPLOG_RRQ', 34);
define('CMD_GET_FREE_SIZES', 50);
define('CMD_ENABLE_CLOCK', 57);
define('CMD_STARTVERIFY', 60);
define('CMD_STARTENROLL', 61);
define('CMD_CANCELCAPTURE', 62);
define('CMD_STATE_RRQ', 64);
define('CMD_WRITE_LCD', 66);
define('CMD_CLEAR_LCD', 67);
define('CMD_GET_PINWIDTH', 69);
define('CMD_SMS_WRQ', 70);
define('CMD_SMS_RRQ', 71);
define('CMD_DELETE_SMS', 72);
define('CMD_UDATA_WRQ', 73);
define('CMD_DELETE_UDATA', 74);
define('CMD_DOORSTATE_RRQ', 75);
define('CMD_WRITE_MIFARE', 76);
define('CMD_EMPTY_MIFARE', 78);
define('CMD_GET_TIME', 201);
define('CMD_SET_TIME', 202);
define('CMD_REG_EVENT', 500);

// Event flags
define('EF_ATTLOG', 1);
define('EF_FINGER', 1 << 1);
define('EF_ENROLLUSER', 1 << 2);
define('EF_ENROLLFINGER', 1 << 3);
define('EF_BUTTON', 1 << 4);
define('EF_UNLOCK', 1 << 5);
define('EF_VERIFY', 1 << 7);
define('EF_FPFTR', 1 << 8);
define('EF_ALARM', 1 << 9);

// Acknowledgement codes
define('CMD_ACK_OK', 2000);
define('CMD_ACK_ERROR', 2001);
define('CMD_ACK_DATA', 2002);
define('CMD_ACK_RETRY', 2003);
define('CMD_ACK_REPEAT', 2004);
define('CMD_ACK_UNAUTH', 2005);
define('CMD_ACK_UNKNOWN', 0xffff);
define('CMD_ACK_ERROR_CMD', 0xfffd);
define('CMD_ACK_ERROR_INIT', 0xfffc);
define('CMD_ACK_ERROR_DATA', 0xfffb);

define('CMD_DEVICE', 11);
define('CMD_SET_USER', 8);
define('LEVEL_USER', 0);
define('LEVEL_ADMIN', 14);

// Encode/decode time
if (!function_exists('encode_time')) {
    function encode_time(DateTime $t) {
        $year = (int)$t->format('Y');
        $month = (int)$t->format('n');
        $day = (int)$t->format('j');
        $hour = (int)$t->format('G');
        $minute = (int)$t->format('i');
        $second = (int)$t->format('s');
        return (($year % 100) * 12 * 31 + (($month - 1) * 31) + $day - 1) *
               (24 * 60 * 60) + ($hour * 60 + $minute) * 60 + $second;
    }
}

if (!function_exists('decode_time')) {
    function decode_time($t) {
        $second = $t % 60; $t = intdiv($t, 60);
        $minute = $t % 60; $t = intdiv($t, 60);
        $hour = $t % 24;   $t = intdiv($t, 24);
        $day = $t % 31 + 1; $t = intdiv($t, 31);
        $month = $t % 12 + 1; $t = intdiv($t, 12);
        $year = (int)floor($t + 2000);
        return date("Y-m-d H:i:s", strtotime("$year-$month-$day $hour:$minute:$second"));
    }
}
?>
