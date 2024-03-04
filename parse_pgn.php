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

function parseLineLight($line){
	$cleanedLine = null;
	$annotationDepth = 0;
	foreach (str_split($line) as $char){
		if ($char=="{" || $char=="(") {$annotationDepth++;}
		if ($annotationDepth==0){$cleanedLine .= $char;}
		if ($char=="}" || $char==")") {$annotationDepth--;}
	}
	return $cleanedLine;
}

function justEvaluations($annotations){
	$annotations = str_replace(array("}{","] [","{","}","[","]"),array("}||{","}!!{","","","",""),$annotations);
    $annotations = explode("||", $annotations);
    foreach ($annotations as $k=>$v){$annotations[$k] = explode("!!", $v);}
    
	$evaluations = array();
	for ($i=0; $i<count($annotations); $i++) {foreach ($annotations[$i] as $k=>$v){if (strpos($v, "eval")!==false){$evaluations[$i] = str_replace(array("%","eval"," "),"",$v); break;} $evaluations[$i] = "";}}
    return $evaluations;
}


function cleanLine($line, $compress=0, $numMoves=150){
	$evaluations = array();
	if (strpos($line, "eval")===false){$line = parseLineLight($line);}
	else{
		$parsedLine = parseLine($line);
		$line = $parsedLine[0];
		$evaluations = justEvaluations($parsedLine[1]);
	}

	$line = str_replace(array("..","?","!"),"",$line); //RIMUOVO I DUE PUNTINI PRIMA DELLE MOSSE DEL NERO E LA CRITICA
	$line = preg_replace(array("/(1-0|0-1|1\/2-1\/2|\*)$/","/\d+\.\s/"), "", $line); //TOLGO IL RISULTATO FINALE E I NUMERI DI SEMIMOSSA
	$line = trim(preg_replace("/\s{2,}/", " ", $line)); //RIMUOVO GLI SPAZI IN ECCESSO
	$halfmoves = explode(" ", $line);
	if (count($evaluations)>=count($halfmoves)-1 && count($halfmoves)>1){$line = ""; foreach ($halfmoves as $k=>$v){if ($compress==1 && $k>=$numMoves){break;} $line .= $v." {".(isset($evaluations[$k]) ? trim($evaluations[$k]) : "")."} ";}}
	else{$line = ""; foreach ($halfmoves as $k=>$v){if ($compress==1 && $k>=$numMoves){break;} $line .= $v." ";}}
	return trim($line);
}

?>
