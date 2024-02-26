<?php

function justEval($line){
    $result = null;
    $annotations = null;
    $multiLineAnnotationDepth = 0;
    foreach (str_split($line) as $char){
        if ($char=="{" || $char=="(") {$multiLineAnnotationDepth++;}
        if ($multiLineAnnotationDepth==0){$result .= $char;} else{$annotations .= $char;}
        if ($char=="}" || $char==")") {$multiLineAnnotationDepth--;}
    }
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
    }
    return $annotations;
}

function removeAnnotations($line){
	$result = null;
	$multiLineAnnotationDepth = 0;
	foreach (str_split($line) as $char){
		if ($char=="{" || $char=="(") {$multiLineAnnotationDepth++;}
		if ($multiLineAnnotationDepth==0){$result .= $char;}
		if ($char=="}" || $char==")") {$multiLineAnnotationDepth--;}
	}
	return $result;
}

function cleanLine($line, $compress=0, $numMoves=150){
	$line1 = array();
	if (strpos($line, "eval")!==false){$line1 = justEval($line);}
	$line = removeAnnotations($line);
	$line = str_replace("..", "", $line); //TOLGO I DUE PUNTINI PRIMA DELLE MOSSE DEL NERO
	$line = preg_replace("/(1-0|0-1|1\/2-1\/2|\*)$/", "", $line); //TOLGO IL RISULTATO FINALE
	$line = str_replace("!","",str_replace("?","",$line)); //TOLGO LA CRITICA
	$line = preg_replace('/\d+\.\s/', '', $line); //TOLGO I NUMERI DI SEMIMOSSA
	$line = trim(preg_replace('/\s{2,}/', ' ', $line)); //RIMUOVI SPAZI IN ECCESSO
	$halfmoves = explode(" ", $line);
	if (count($line1)>=count($halfmoves)-1 && count($halfmoves)>1){$line = ""; foreach ($halfmoves as $k=>$v){if ($compress==1 && $k>=$numMoves){break;} $line .= $v." {".(isset($line1[$k][0]) ? trim($line1[$k][0]) : "")."} ";}}
	else{$line = ""; foreach ($halfmoves as $k=>$v){if ($compress==1 && $k>=$numMoves){break;} $line .= $v." ";}}
	return trim($line);
}

?>
