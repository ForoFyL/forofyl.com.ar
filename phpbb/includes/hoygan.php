<?php

function hoygan($str, $rnd = false){
	$texto = $str;
	$rnd = ($rnd !== false) ? true : false;
	mt_srand(strlen($texto));

	$tmp_seed = explode(' ', microtime());
	$seed = (int) $tmp_seed[0] + ((int) $tmp_seed[1]);

	//Función anónima para gestión de números random y no-tan-random.
	//La declaro acá y así porque es sólo de uso interno y no quiero
	//polucionar ni librerías ni namespaces.
	$semirand = function($max = 100, $min = 1) use ($rnd, $texto, $seed){
		if ($rnd === true) {
			mt_srand(mt_rand(1,$seed));
		}
		$ret = (int)mt_rand($min, $max);
		return $ret;
	};

	//Función anónima (idem razón anterior) para gestionar epígrafes.
	$epigrafe = function() use ($semirand){
		$devolver = "";
		$tmp = $semirand(10);
		if ($tmp == 1){
			$devolver = " GRASIES DE ANTEBRASO";
		}
		if ($tmp == 2){
			$devolver = " GRASIAS DE ANTEMANO";
		}
		if ($tmp == 3){
			$devolver = " GRACIES DE ENTREPIERNA";
		}
		if ($tmp == 4){
			$devolver = " GRASAS DE MANO PERSON POR LAS MOLESTIAS";
		}
		if ($tmp == 5){
			$devolver = " PERSDON X LAS MOILESRIA";
		}
		if ($tmp == 6){
			$devolver = " GRASIA PERDONM";
		}
		if ($tmp == 7){
			$devolver = " PERDOM GRAISAS";
		}
		if ($tmp == 8){
			$devolver = " GROSAS DE NADA";
		}
		if ($tmp == 9){
			$devolver = " PERDON POR LAS DISCULPAS";
		}
		if ($tmp == 10){
			$devolver = " GRASIAS ASTA LUEBO";
		}
		return $devolver;
	};


	// CRITERIOS DE HOYGANIFICACIÓN:

	// 1) Mayúsculas
	$texto = strtoupper($texto);

	$tmp1 = explode(" ", $texto);
	for ($i = 0; $i < count($tmp1); $i++){
		$palabra = $tmp1[$i];
		//Trabajo palabras separadas, y letra por letra.
		for ( $i2 = 0; $i2 < strlen($palabra); $i2++){
			$inicial = ($i2 == 0) ? substr($palabra,0,1) : "";
			// 2) Las palabras que empiecen con vocal, tienen un 40% de pasar a empezar con H
			if ($inicial == "A" || $inicial == "E" || $inicial == "I" || $inicial == "O" || $inicial == "U"){
				if ($semirand() > 60){
					$palabra = "H" . $palabra;
				}
			}

			$letra = substr($palabra,$i2,1);
			//3) Hay un 12% de chances de que las letras se cambien de lugar
			if ($semirand() < 12){
				$inicio  = substr($palabra, 0, $i2);
				$fin     = substr($palabra, $i2+1, strlen($palabra) - $i2);
				$palabra = substr($inicio,  0, strlen($inicio) -1) . $letra . substr($inicio, -1) . $fin;
			}

		}

		//4) "por" se reemplaza por "x". 50% chances
		if ($semirand() > 50){
			str_replace("POR","X",$palabra);
		}

		//5) "que" se reemplaza por "k". 50% chances
		if ($semirand() > 50){
			str_replace("QUE","K",$palabra);
			str_replace("QUÉ","K",$palabra);
			str_replace("QUI","KI",$palabra);
		}

		//6) "c" se reemplaza por "k". 50% chances
		if ($semirand() > 50){
			str_replace("CA","KA",$palabra);
			str_replace("CU","KU",$palabra);
		}

		//7) Se agrega "jajaja", con extensión arbitraria entre 4 y 10 caracteres, con 4% chances
		if ($semirand() > 96){
			$extension = $semirand(10,4);
			if ($extension > 10) { $extension = 10; }
			$palabra .= " ";
			for ($i2 = 0; $i2 < $extension; $i2++){
				$palabra .= ($i2 % 2 == 0) ? "J" : "A";
			}
		}

		$tmp1[$i] = $palabra;
	}
	$texto = implode(" ",$tmp1);

	//8) se agrega la palabra HOYGAN al comienzo. 75% chances.
	if ($semirand() > 25){
		$texto = "HOYGAN " . $texto;
	}

	//9) se agrega la palabra URGENTE al comienzo. 5% chances.
	if ($semirand() > 95){
		$texto = "URGENTE " . $texto;
	}

	//10) entre una y el 10% de letras NO están en mayúsculas;
	$cuantas = $semirand( strlen($texto) * 0.1);
	for ($i = 0; $i < $cuantas; $i++){
		$i2 = $semirand(strlen($texto));
		if ($i2 > strlen($texto) - 1) { $i2 = strlen($texto) -1; }
		$letra  = strtolower(substr($texto,$i2,1));
		$inicio = substr($texto,0,$i2);
		$fin    = substr($texto,$i2+1,strlen($texto) - $i2);
		$texto  = $inicio . $letra . $fin;
	}

	//11) Se agrega epígrafe, con 10% de chances
	if ($semirand() > 90){
		$texto .= $epigrafe();
	}

	//12) Signos de puntuación se exageran
	$cuantas = $semirand(10);
	$ex = "";
	for ($i = 0; $i < $cuantas; $i++){
		$ex .= "!";
	}
	str_replace(".",$ex,$texto);

	$cuantas = $semirand(10);
	$inte = "";
	for ($i = 0; $i < $cuantas; $i++){
		$inte .= ($i % $semirand(2,1) ) == 0 ? "!" : "?";
	}
	str_replace("?",$inte,$texto);

	//13) se agregan exclamaciones al azar al final de la expresión, con 10% chances
	if ($semirand() > 90){
		$cuantas = $semirand(25);
		$inte = "";
		for ($i = 0; $i < $cuantas; $i++){
			$inte .= ($i % $semirand(5) == 0) ? "?" : "!";
		}
		$texto .= $inte;
	}

	return $texto;
}
