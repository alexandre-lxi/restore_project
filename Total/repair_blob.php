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
				and dt_created > '2018-10-15'
				and co.i_autocode = 70593
				limit 1";


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
			$ref = $id.'.'.$fileext;

			print("\t\t".'Fileext: '.$fileext."\n");
			print("\t\t".'Blob: '.$blobfname."\n");
			print("\t\t".'New: '.$newfname."\n");
			print("\t\t".'Ref: '.$ref."\n");

			if (file_exists($blobfname)){
				print("\t".'Move file: '.$blobfname."\n");
//				exec('mv '.$blobfname.' '.$newfname);

				if (file_exists($newfname)){
					print("\t".'Update Database file: '.$blobfname."\n");
					$sql = "update container set s_reference = :ref where i_autocode = :code";

					$ins = $pdo->prepare($sql);
					$ins->bindValue(':code', $cont->id, PDO::PARAM_INT);
					$ins->bindValue(':ref', $ref, PDO::PARAM_STR);
					$ins->execute();

					$sql = "update image_file set s_path = :fpath, s_filename = :fname, s_fileformat = :ext where i_foreigncode = :code";

					$ins = $pdo->prepare($sql);
					$ins->bindValue(':code', $cont->id, PDO::PARAM_INT);
					$ins->bindValue(':fpath', $newfname, PDO::PARAM_STR);
					$ins->bindValue(':fname', $ref, PDO::PARAM_STR);
					$ins->bindValue(':ext', '.'.$fileext, PDO::PARAM_STR);
					$ins->execute();
				}

			}else{
				print("\t".'NO FILE TO MOVE'."\n");
			}


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