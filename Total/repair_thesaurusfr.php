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

		$sql = "select *
                from thesaurusfr                
                where i_leftidx >= 5
                and i_level >=2
                order by i_leftidx
                limit 10";


		$rqt = $pdo->prepare($sql);
		$rqt->execute();

		$topics = $rqt->fetchAll(PDO::FETCH_OBJ);

		$lft = 5;
		$rgt = $lft+1;
		$lvl = 0;

		$tabs = array();


		foreach ($topics as $topic) {
			print( str_repeat("\t", $topic->i_level-2 )."lvl:".$topic->i_level." label:". utf8_encode($topic->s_label)." lft:".$lft." rgt:".$rgt."\n");


			if ($topic->i_level > $lvl){
				$lvl = $topic->i_level;

				if (array_key_exists($lvl-1, $tabs)){
					$lft = $tabs[$lvl-1]['rgt'];
					$rgt = $lft +1;
				}

				$tabs[$lvl] = array(
					'id'=> $topic->i_autocode,
					'name'=>$topic->s_label,
					'lft'=>$lft,
					'rgt'=>$rgt
				);

				for ($i = $lvl-1; $i >=2; $i--){
					$tabs[$i]['rgt'] = $tabs[$i+1]['rgt']+1;

					$sql = "update thesaurusfr_restore set i_rightidx = :rgt
                    where i_autocode = :i_autocode";
					$upd = $pdo->prepare($sql);
					$upd->bindValue(':i_autocode', $tabs[$i]['id'], PDO::PARAM_INT);
					$upd->bindValue(':rgt', $tabs[$i]['rgt'], PDO::PARAM_INT);
					$upd->execute();
				}
			}elseif($topic->i_level == $lvl){
				$lvl = $topic->i_level;

				$lft = $tabs[$lvl]['rgt'] +1;
				$rgt = $lft +1;


				$tabs[$lvl] = array(
					'id'=> $topic->i_autocode,
					'name'=>$topic->s_label,
					'lft'=>$lft,
					'rgt'=>$rgt
				);

				for ($i = $lvl-1; $i >=2; $i--){
					$tabs[$i]['rgt'] = $tabs[$i+1]['rgt']+1;

					$sql = "update thesaurusfr_restore set i_rightidx = :rgt
                    where i_autocode = :i_autocode";
					$upd = $pdo->prepare($sql);
					$upd->bindValue(':i_autocode', $tabs[$i]['id'], PDO::PARAM_INT);
					$upd->bindValue(':rgt', $tabs[$i]['rgt'], PDO::PARAM_INT);
					$upd->execute();
				}
			}else{
				$lvl = $topic->i_level;
				unset($tabs[$lvl+1]);


				$lft = $tabs[$lvl]['rgt'] +1;
				$rgt = $lft +1;


				$tabs[$lvl] = array(
					'id'=> $topic->i_autocode,
					'name'=>$topic->s_label,
					'lft'=>$lft,
					'rgt'=>$rgt
				);

				for ($i = $lvl-1; $i >=2; $i--){
					$tabs[$i]['rgt'] = $tabs[$i+1]['rgt']+1;

					$sql = "update thesaurusfr_restore set i_rightidx = :rgt
                    where i_autocode = :i_autocode";
					$upd = $pdo->prepare($sql);
					$upd->bindValue(':i_autocode', $tabs[$i]['id'], PDO::PARAM_INT);
					$upd->bindValue(':rgt', $tabs[$i]['rgt'], PDO::PARAM_INT);
					$upd->execute();
				}
			}

			//            print_r($tabs);


			$sql = "insert into thesaurusfr_restore(i_autocode, i_level, i_leftidx, i_rightidx, s_label)
                VALUES (:i_autocode, :i_level, :i_leftidx, :i_rightidx, :s_label)";

			$ins = $pdo->prepare($sql);
			$ins->bindValue(':i_autocode', $topic->i_autocode, PDO::PARAM_INT);
			$ins->bindValue(':i_level', $topic->i_level, PDO::PARAM_INT);
			$ins->bindValue(':i_leftidx', $lft, PDO::PARAM_INT);
			$ins->bindValue(':i_rightidx', $rgt, PDO::PARAM_INT);
			$ins->bindValue(':s_label', $topic->s_label, PDO::PARAM_STR);
			$ins->execute();

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