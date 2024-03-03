<?php

function parseLine($line){
	$cleanedLine = null;
    $annotations = null;
	$annotationDepth = 0;
	foreach (str_split($line) as $char){
		if ($char=="{" || $char=="(") {$annotationDepth++;}
		if ($annotationDepth==0){$cleanedLine .= $char;} else{$annotations .= $char;}
		if ($char=="}" || $char==")") {$annotationDepth--;}
	}
	return array($cleanedLine, $annotations);
}

function justEvaluations($annotations){
    $annotations = str_ireplace("}{", "}||{", $annotations);
    $annotations = str_ireplace("] [", "}!!{", $annotations);
    $annotations = str_ireplace("{", "", $annotations);
    $annotations = str_ireplace("}", "", $annotations);
    $annotations = str_ireplace("[", "", $annotations);
    $annotations = str_ireplace("]", "", $annotations);
    $annotations = explode("||", $annotations);
    foreach ($annotations as $k=>$v){$annotations[$k] = explode("!!", $v);}
    for ($i=0; $i<count($annotations); $i++) { 
        if (strpos($annotations[$i][0], "eval")===false){$annotations[$i][0] = "";}
        if (isset($annotations[$i][1])){
            if (strpos($annotations[$i][1], "eval")!==false){$annotations[$i][0] = $annotations[$i][1];}
            unset($annotations[$i][1]);
        }
		$annotations[$i][0] = str_replace(array("%","eval"," "), array("","",""), $annotations[$i][0]);
    }
    return $annotations;
}


function cleanLine($line, $compress=0, $numMoves=150){
	$parsedLine = parseLine($line);
	$line = $parsedLine[0];
	$annotations = $parsedLine[1];
	
	$evaluations = array();
	if (strpos($annotations, "eval")!==false){$evaluations = justEvaluations($annotations);}
	
	$line = str_replace("..", "", $line); //TOLGO I DUE PUNTINI PRIMA DELLE MOSSE DEL NERO
	$line = preg_replace("/(1-0|0-1|1\/2-1\/2|\*)$/", "", $line); //TOLGO IL RISULTATO FINALE
	$line = str_replace("!","",str_replace("?","",$line)); //TOLGO LA CRITICA
	$line = preg_replace('/\d+\.\s/', '', $line); //TOLGO I NUMERI DI SEMIMOSSA
	$line = trim(preg_replace('/\s{2,}/', ' ', $line)); //RIMUOVI SPAZI IN ECCESSO
	$halfmoves = explode(" ", $line);
	if (count($evaluations)>=count($halfmoves)-1 && count($halfmoves)>1){$line = ""; foreach ($halfmoves as $k=>$v){if ($compress==1 && $k>=$numMoves){break;} $line .= $v." {".(isset($evaluations[$k][0]) ? trim($evaluations[$k][0]) : "")."} ";}}
	else{$line = ""; foreach ($halfmoves as $k=>$v){if ($compress==1 && $k>=$numMoves){break;} $line .= $v." ";}}
	return trim($line);
}

?>
