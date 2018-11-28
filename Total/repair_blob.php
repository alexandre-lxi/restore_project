<?php

function threat()
{
	$VALEUR_hote = '127.0.0.1';
	$VALEUR_port = '3306';
	$VALEUR_nom_bd = 'total-refontedam';
	$VALEUR_user = 'alaidin';
	$VALEUR_mot_de_passe = 'alaidin';

	try {
		$pdo = new PDO(
			'mysql:host='.$VALEUR_hote.';port='.$VALEUR_port.';dbname='.$VALEUR_nom_bd,
			$VALEUR_user,
			$VALEUR_mot_de_passe
		);

		$sql = "select co.i_autocode id, co.*, cq.*
				from container  co
          join conversion_queue cq on cq.i_containercode = co.i_autocode 
				where s_reference = 'blob'
				and dt_created > '2018-10-15'";


		$rqt = $pdo->prepare($sql);
		$rqt->execute();

		$conts = $rqt->fetchAll(PDO::FETCH_OBJ);
		$oridir = '/var/www/projects/total-1410-refontedam/back/account/pictures/oridir/';

		foreach ($conts as $cont) {
			print($cont->id."\n" );
			$id = $cont->id;
			$fileext = pathinfo($cont->s_filetoprocess, PATHINFO_EXTENSION);
			$blobfname = $oridir.$id.'.blob';
			$newfname =  $oridir.$id.'.'.$fileext;

			print("\t".'Fileext: '.$fileext."\n");
			print("\t".'Blob: '.$blobfname."\n");
			print("\t".'New: '.$newfname."\n");

			if (file_exists($blobfname)){
				print("\t".'Move file: '.$blobfname."\n");
				//exec('mv '.$blobfname.' '.$newfname);
			}else{
				print("\t".'NO FILE TO MOVE'."\n");
			}


			//            exec('mv ')


			//            $sql = "insert into topic0_restore(i_autocode, i_level, i_leftidx, i_rightidx, s_label)
			//                VALUES (:i_autocode, :i_level, :i_leftidx, :i_rightidx, :s_label)";
			//
			//            $ins = $pdo->prepare($sql);
			//            $ins->bindValue(':i_autocode', $topic->i_autocode, PDO::PARAM_INT);
			//            $ins->bindValue(':i_level', $topic->i_level, PDO::PARAM_INT);
			//            $ins->bindValue(':i_leftidx', $lft, PDO::PARAM_INT);
			//            $ins->bindValue(':i_rightidx', $rgt, PDO::PARAM_INT);
			//            $ins->bindValue(':s_label', $topic->s_label, PDO::PARAM_STR);
			//            $ins->execute();

			//            print_r($pdo->errorInfo());
		}


	} catch (PDOException $Exception) {
		// PHP Fatal Error. Second Argument Has To Be An Integer, But PDOException::getCode Returns A
		// String.
		echo $Exception->getMessage().' : '.$Exception->getCode();
		die;
	}
}

threat();