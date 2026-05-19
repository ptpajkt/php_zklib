<?php
function getSizeAttendance($self) {
    $u = unpack('H2h1/H2h2/H2h3/H2h4/H2h5/H2h6/H2h7/H2h8', substr($self->data_recv, 0, 8)); 
    $command = hexdec($u['h2'].$u['h1']);
    
    if ($command == CMD_PREPARE_DATA) {
        $u = unpack('H2h1/H2h2/H2h3/H2h4', substr($self->data_recv, 8, 4));
        $size = hexdec($u['h4'].$u['h3'].$u['h2'].$u['h1']);
        return $size;
    } else {
        return false;
    }
}

if (!function_exists('reverseHex')) {
    function reverseHex($hexstr) {
        $tmp = '';
        for ($i = strlen($hexstr); $i >= 0; $i--) {
            $tmp .= substr($hexstr, $i, 2);
            $i--;
        }
        return $tmp;
    }
}

function zkgetattendance($self) {
    $command = CMD_ATTLOG_RRQ;
    $command_string = '';
    $chksum = 0;
    $session_id = $self->session_id;
    
    $u = unpack('H2h1/H2h2/H2h3/H2h4/H2h5/H2h6/H2h7/H2h8', substr($self->data_recv, 0, 8));
    $reply_id = hexdec($u['h8'].$u['h7']);

    $buf = $self->createHeader($command, $chksum, $session_id, $reply_id, $command_string);
    socket_sendto($self->zkclient, $buf, strlen($buf), 0, $self->ip, $self->port);
    
    @socket_recvfrom($self->zkclient, $self->data_recv, 1024, 0, $self->ip, $self->port);
    
    if (getSizeAttendance($self)) {
        $bytes = getSizeAttendance($self);
        while ($bytes > 0) {
            @socket_recvfrom($self->zkclient, $data_recv, 1032, 0, $self->ip, $self->port);
            $self->attendancedata[] = $data_recv;
            $bytes -= 1024;
        }
        $self->session_id = hexdec($u['h6'].$u['h5']);
        @socket_recvfrom($self->zkclient, $data_recv, 1024, 0, $self->ip, $self->port);
    }
    
    $attendance = [];  
    if (count($self->attendancedata) > 0) {
        for ($x = 0; $x < count($self->attendancedata); $x++) {
            if ($x > 0) {
                $self->attendancedata[$x] = substr($self->attendancedata[$x], 8);
            }
        }
        
        $attendancedata = implode('', $self->attendancedata);
        $attendancedata = substr($attendancedata, 10);
        
        while (strlen($attendancedata) > 40) {
            $u = unpack('H78', substr($attendancedata, 0, 39));
            
            $uid = trim(substr($attendancedata, 4, 14), "\x0");
            $id = intval(str_replace("\0", '', hex2bin(substr($u[1], 6, 8))));
            $state = hexdec(substr($u[1], 56, 2));
            $timestamp = decode_time(hexdec(reverseHex(substr($u[1], 58, 8))));
            
            $attendance[] = [$uid, $id, $state, $timestamp];
            $attendancedata = substr($attendancedata, 40);
        }
    }
        
    return $attendance;
}

function zkclearattendance($self) {
    $command = CMD_CLEAR_ATTLOG;
    $command_string = '';
    $chksum = 0;
    $session_id = $self->session_id;
    
    $u = unpack('H2h1/H2h2/H2h3/H2h4/H2h5/H2h6/H2h7/H2h8', substr($self->data_recv, 0, 8));
    $reply_id = hexdec($u['h8'].$u['h7']);

    $buf = $self->createHeader($command, $chksum, $session_id, $reply_id, $command_string);
    socket_sendto($self->zkclient, $buf, strlen($buf), 0, $self->ip, $self->port);
    
    try {
        @socket_recvfrom($self->zkclient, $self->data_recv, 1024, 0, $self->ip, $self->port);
        $u = unpack('H2h1/H2h2/H2h3/H2h4/H2h5/H2h6', substr($self->data_recv, 0, 8));
        $self->session_id = hexdec($u['h6'].$u['h5']);
        return substr($self->data_recv, 8);
    } catch (Exception $e) {
        return false;
    }
}
?>
---

⚡ Ringkas:  
- `reverseHex` sekarang dicek dengan `!function_exists`.