<?php
function getSizeUser($self) {
    $u = unpack('H2h1/H2h2/H2h3/H2h4/H2h5/H2h6/H2h7/H2h8', substr($self->data_recv, 0, 8)); 
    $command = hexdec($u['h2'].$u['h1']);
    if ($command == CMD_PREPARE_DATA) {
        $u = unpack('H2h1/H2h2/H2h3/H2h4', substr($self->data_recv, 8, 4));
        return hexdec($u['h4'].$u['h3'].$u['h2'].$u['h1']);
    }
    return false;
}

function zksetuser($self, $uid, $userid, $name, $password, $role) {
    $command = CMD_SET_USER;
    $command_string = pack('axaa8a28aa7xa8a16', chr($uid), chr($role), $password, $name, chr(1), '', $userid, '');
    $buf = $self->createHeader($command, 0, $self->session_id, hexdec(unpack('H2h8/H2h7', substr($self->data_recv, 0, 8))['h8']), $command_string);
    socket_sendto($self->zkclient, $buf, strlen($buf), 0, $self->ip, $self->port);
    try {
        @socket_recvfrom($self->zkclient, $self->data_recv, 1024, 0, $self->ip, $self->port);
        $u = unpack('H2h1/H2h2/H2h3/H2h4/H2h5/H2h6', substr($self->data_recv, 0, 8));
        $self->session_id = hexdec($u['h6'].$u['h5']);
        return substr($self->data_recv, 8);
    } catch (ErrorException $e) {
        return false;
    } catch (Exception $e) {
        return false;
    }
}

function zkgetuser($self) {
    $command = CMD_USERTEMP_RRQ;
    $command_string = chr(5);
    $buf = $self->createHeader($command, 0, $self->session_id, hexdec(unpack('H2h8/H2h7', substr($self->data_recv, 0, 8))['h8']), $command_string);
    socket_sendto($self->zkclient, $buf, strlen($buf), 0, $self->ip, $self->port);

    try {
        @socket_recvfrom($self->zkclient, $self->data_recv, 1024, 0, $self->ip, $self->port);
        if (getSizeUser($self)) {
            $bytes = getSizeUser($self);
            while ($bytes > 0) {
                @socket_recvfrom($self->zkclient, $data_recv, 1032, 0, $self->ip, $self->port);
                $self->userdata[] = $data_recv;
                $bytes -= 1024;
            }
            $u = unpack('H2h6/H2h5', substr($self->data_recv, 0, 8));
            $self->session_id = hexdec($u['h6'].$u['h5']);
            @socket_recvfrom($self->zkclient, $data_recv, 1024, 0, $self->ip, $self->port);
        }

        $users = [];
        if (count($self->userdata) > 0) {
            for ($x = 0; $x < count($self->userdata); $x++) {
                if ($x > 0) $self->userdata[$x] = substr($self->userdata[$x], 8);
            }
            $userdata = substr(implode('', $self->userdata), 11);
            while (strlen($userdata) > 72) {
                $u = unpack('H144', substr($userdata, 0, 72));
                $u1 = hexdec(substr($u[1], 2, 2));
                $u2 = hexdec(substr($u[1], 4, 2));
                $uid = $u1 + ($u2 * 256);
                $cardno = hexdec(substr($u[1], 78, 2).substr($u[1], 76, 2).substr($u[1], 74, 2).substr($u[1], 72, 2));
                $role = hexdec(substr($u[1], 4, 4));
                $password = explode(chr(0), hex2bin(substr($u[1], 8, 16)), 2)[0];
                $name = explode(chr(0), hex2bin(substr($u[1], 24, 74)), 3)[0];
                $userid = explode(chr(0), hex2bin(substr($u[1], 98, 72)), 2)[0];
                $name = mb_convert_encoding($name, 'UTF-8', 'ISO-8859-1');
                $cardno = str_pad($cardno, 11, '0', STR_PAD_LEFT);
                if ($name == "") $name = $uid;
                $users[$uid] = [$userid, $name, $cardno, $uid, intval($role), $password];
                $userdata = substr($userdata, 72);
            }
        }
        return $users;
    } catch (ErrorException $e) {
        return false;
    } catch (Exception $e) {
        return false;
    }
}

function zkgetfp($self) {
    $command = chr(227).chr(17).chr(18);
    $command_string = chr(80).chr(16).chr(3).chr(253).chr(132).chr(64).chr(0).chr(0);
    $buf = $self->createHeader($command, 0, $self->session_id, hexdec(unpack('H2h8/H2h7', substr($self->data_recv, 0, 8))['h8']), $command_string);
    socket_sendto($self->zkclient, $buf, strlen($buf), 0, $self->ip, $self->port);
    try {
        @socket_recvfrom($self->zkclient, $self->data_recv, 1024, 0, $self->ip, $self->port);
        return '';
    } catch (ErrorException $e) {
        return false;
    } catch (Exception $e) {
        return false;
    }
}

function zkclearuser($self) {
    $command = CMD_CLEAR_DATA;
    $buf = $self->createHeader($command, 0, $self->session_id, hexdec(unpack('H2h8/H2h7', substr($self->data_recv, 0, 8))['h8']), '');
    socket_sendto($self->zkclient, $buf, strlen($buf), 0, $self->ip, $self->port);
    try {
        @socket_recvfrom($self->zkclient, $self->data_recv, 1024, 0, $self->ip, $self->port);
        $u = unpack('H2h6/H2h5', substr($self->data_recv, 0, 8));
        $self->session_id = hexdec($u['h6'].$u['h5']);
        return substr($self->data_recv, 8);
    } catch (ErrorException $e) {
        return false;
    } catch (Exception $e) {
        return false;
    }
}
function zkclearadmin($self) {
    $command = CMD_CLEAR_ADMIN;
    $buf = $self->createHeader($command, 0, $self->session_id,
        hexdec(unpack('H2h8/H2h7', substr($self->data_recv, 0, 8))['h8']), '');
    socket_sendto($self->zkclient, $buf, strlen($buf), 0, $self->ip, $self->port);

    try {
        @socket_recvfrom($self->zkclient, $self->data_recv, 1024, 0, $self->ip, $self->port);
        $u = unpack('H2h6/H2h5', substr($self->data_recv, 0, 8));
        $self->session_id = hexdec($u['h6'].$u['h5']);
        return substr($self->data_recv, 8);
    } catch (ErrorException $e) {
        return false;
    } catch (Exception $e) {
        return false;
    }
}

function zkenrolluser($self, $userid) {
    $command = CMD_STARTENROLL;
    $command_string = pack("a*", $userid);
    $buf = $self->createHeader($command, 0, $self->session_id,
        hexdec(unpack('H2h8/H2h7', substr($self->data_recv, 0, 8))['h8']), $command_string);
    socket_sendto($self->zkclient, $buf, strlen($buf), 0, $self->ip, $self->port);

    try {
        @socket_recvfrom($self->zkclient, $self->data_recv, 1024, 0, $self->ip, $self->port);
        $u = unpack('H2h6/H2h5', substr($self->data_recv, 0, 8));
        $self->session_id = hexdec($u['h6'].$u['h5']);
        return substr($self->data_recv, 8);
    } catch (ErrorException $e) {
        return false;
    } catch (Exception $e) {
        return false;
    }
}
?>