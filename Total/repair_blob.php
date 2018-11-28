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
				and co.i_autocode = 70591
				order by 1 desc
				limit 2";


		$rqt = $pdo->prepare($sql);
		$rqt->execute();

		$conts = $rqt->fetchAll(PDO::FETCH_OBJ);
		$oridir = '/var/www/projects/total-1410-refontedam/back/account/pictures/oridir/';
		$webdir = '/var/www/projects/total-1410-refontedam/back/account/pictures/webdir/';
		$thumbdir = '/var/www/projects/total-1410-refontedam/back/account/pictures/thumbdir/';
		$tmpdir = '/var/www/projects/total-1410-refontedam/back/account/pictures/tmp/';

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
				exec('mv '.$blobfname.' '.$newfname);
			}else{
				print("\t".'NO FILE TO MOVE'."\n");
			}

			if (file_exists($newfname)){
				print("\t".'Convert file: '.$newfname."\n");

				if ($fileext == 'mp4'){

					$convert 	= "ffmpeg -i \"".$newfname."\" -ss 10 -vframes 1 -s 1920x1080 \"".$tmpdir.$id.'.jpg'."\"";
					print("\t\t".'Convert : '.$convert."\n");
					exec($convert);

					$convert 	= 'convert '.$tmpdir.$id.'.jpg'.' -resize 640x640 -quality 95 '.$webdir.$id.'.jpg';
					print("\t\t".'Convert : '.$convert."\n");
					exec($convert);

					$convert 	= 'convert '.$tmpdir.$id.'.jpg'.' -resize 280x280 -quality 95 '.$thumbdir.$id.'.jpg';
					print("\t\t".'Convert : '.$convert."\n");
					exec($convert);
				}elseif($fileext == 'zip') {
					copy("/var/www/projects/total-1410-refontedam/back/ico/zip.jpg", $webdir.$id.'.jpg');
					copy("/var/www/projects/total-1410-refontedam/back/ico/zip.jpg", $thumbdir.$id.'.jpg');
				}
				else{
						$convert = 'convert '.$newfname.' -resize 640x640 -quality 95 '.$webdir.$id.'.jpg';
						print("\t\t".'Convert : '.$convert."\n");
						exec($convert);


						$convert = 'convert '.$newfname.' -resize 280x280 -quality 95 '.$thumbdir.$id.'.jpg';
						print("\t\t".'Convert : '.$convert."\n");
						exec($convert);
					}
				}


				print("\t".'Update Database file: '.$ref."\n");
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