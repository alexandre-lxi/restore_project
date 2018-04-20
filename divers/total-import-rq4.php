<?php

function _createMedia($fname, $coid)
{
    $params = array(
        'gid' => 17,
        'uid' => 71,
        'f'   => $fname
    );
    $defaults = array(
        CURLOPT_URL            => 'http://mediatecms.total.com/app/modules/upload/action/listenftpfolder_man.php',
        CURLOPT_POST           => true,
        CURLOPT_HEADER         => false,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POSTFIELDS     => $params,
    );
    $ch = curl_init();
    curl_setopt_array($ch, $defaults);
    $output = curl_exec($ch);
    curl_close($ch);

    $mediaid = trim($output);

    $params = array(
        'f'            => 'containerlinkedforeign2flash',
        'action'       => 'additem',
        'code'         => $mediaid,
        'module'       => 'topic0',
        'user'         => '71',
        'addorreplace' => 'add',
        'value'        => $coid
    );
    $defaults = array(
        CURLOPT_URL            => 'http://mediatecms.total.com/app/modules/manage/action/interface.php',
        CURLOPT_POST           => true,
        CURLOPT_HEADER         => false,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POSTFIELDS     => $params,
    );
    $ch = curl_init();
    curl_setopt_array($ch, $defaults);
    $output = curl_exec($ch);
    curl_close($ch);
}

function _createContainer($parentid, $value)
{
    $params=array(
        'f'=>'gentree2flash',
    'action'=>'insertnewchild',
    'lang'=>'fr',
    'parent'=>$parentid,
    'module'=>'topic0',
    'user'=>'e2c420928d4bf8ce0ff2ec19b371514',
    'value'=>$value
    );
    $defaults = array(
        CURLOPT_URL => 'http://mediatecms.total.com/app/modules/manage/action/interface.php',
        CURLOPT_POST => true,
        CURLOPT_HEADER => false,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POSTFIELDS => $params,
    );
    $ch = curl_init();
    curl_setopt_array($ch, $defaults);
    $output = curl_exec($ch);
    curl_close($ch);

    $pos = strpos($output, 'm_code');
    $code = substr($output,$pos+7,strlen($output)-$pos-7-1);

    return $code;
}

function _readDir($dirsource, $initdir, $parentid)
{
    $files = scandir($dirsource);

    $nb = 0;

    foreach ($files as $file) {
        if ($file == '.') continue;
        if ($file == '..') continue;

        if (!file_exists($dirsource.$file))
            continue;

        if (!is_dir($dirsource.$file)) {
            $fname = substr($dirsource.$file, strlen($initdir));

            //_createMedia($fname,$parentid);

            echo "Create media:\n".
                "\tName:".$fname."\n".
                "\tcoid:".$parentid."\n";
        } else {
            if ($file !== 'processed') {

                //$newid = _createContainer($parentid, $file);

                $newid = $parentid+1;

                echo "Create container:\n".
                    "\tName:".$file."\n".
                    "\tParent:".$parentid."\n".
                    "\tNewID:".$newid."\n";

                _readDir($dirsource.$file.'/', $initdir, $newid);
            }
        }

        $nb++;
        if ($nb>5) {
            break;
        }
    }
}


_readDir(
    '/var/www/projects/total-1410-refontedam/back/account/ftpupload/71_dir/ROBOTQUARTZ-4',
    '/var/www/projects/total-1410-refontedam/back/account/ftpupload/71_dir/',
    7937
);

