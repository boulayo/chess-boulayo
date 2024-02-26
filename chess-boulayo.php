<?php

function colToNumber($c){switch ($c){case "a": return 1; case "b": return 2; case "c": return 3; case "d": return 4; case "e": return 5; case "f": return 6; case "g": return 7; case "h": return 8; default: return 0;}}
function numToColumn($n){switch ($n){case 1: return "a"; case 2: return "b"; case 3: return "c"; case 4: return "d"; case 5: return "e"; case 6: return "f"; case 7: return "g"; case 8: return "h"; default: return 0;}}
function writeException($code, $parameter=""){
	switch ($code){
		case 1: return "The move ".$parameter." is badly written.";
		case 2: return "The move ".$parameter." is impossible.";
		case 3: return "The move ".$parameter." is ambiguous.";
		case 10: return "There is no opponent's piece in ".$parameter.".";
		case 11: return "Square ".$parameter." is not empty.";
		case 50: return "Error move ".$parameter.". If a pawn arrive to the other side it must have a promotion.";
		case 60: return "There not exists a single move from position ".$parameter[0]." to position ".$parameter[1];
		case 70: return "Move not possible: King is in check.";
		case 71: return "The move ".$parameter." does not report the check";
		case 72: return "The move ".$parameter." does report an inexistent check";
		case 80: return "Castling in this position is not possible.";
		case 81: return "Castling in this position is not possible. There are pieces in the middle.";
		case 90: return "The FEN record has a wrong number of parts.";
		case 91: return "The FEN record is not valid.";
		case 99: return "Error in the construction of the board.";
	}
}

function retrieveMoveData($move){
	$moveData = array();
	$match1 = preg_match("/^([O-]+)([+#]?)([!?]*)$/", $move, $moveData);
	if ($match1){
		if ($moveData[1]!="O-O" && $moveData[1]!="O-O-O"){throw new Exception(writeException(1, $move));}
		return array("move"=>$move, "castling"=>$moveData[1], "piece"=>"K", "start"=>10, "capture"=>"", "end"=>10, "promotion"=>"", "check"=>$moveData[2]);
	}
	$match2 = preg_match("/^([PNBRQK]?)([a-h]?[1-8]?)([x]?)([a-h][1-8])(?:=([NBRQ]))?([+#]?)([!?]*)$/", $move, $moveData);
	if (!$match2){throw new Exception(writeException(1, $move));}
	if (!isset($moveData[1], $moveData[2], $moveData[3], $moveData[4], $moveData[5], $moveData[6], $moveData[7])){throw new Exception("aaa".writeException(1, $move));}
	if ($moveData[1]==""){$moveData[1]="P";}
	if ($moveData[5]!="" && $moveData["1"]!="P"){throw new Exception(writeException(1, $move));}
	return array("move"=>$move, "castling"=>"", "piece"=>$moveData[1], "start"=>$moveData[2], "capture"=>$moveData[3], "end"=>$moveData[4], "promotion"=>$moveData[5], "check"=>$moveData[6]);
}

function findMoveLimits($moveData, $board, $controls=1){
	$startc = 0;
	$startr = 0;
	$arrayStart = str_split($moveData["start"]);
	$arrayEnd = str_split($moveData["end"]);
	if (count($arrayStart)==2){$startc = colToNumber($arrayStart[0]); $startr = $arrayStart[1];}
	elseif (count($arrayStart)==1){if (is_numeric($arrayStart[0])){$startr = $arrayStart[0];} else{$startc = colToNumber($arrayStart[0]);}}
	$endc = colToNumber($arrayEnd[0]);
	$endr = (int) $arrayEnd[1];
	
	if ($moveData["castling"]!=""){
		$startc = $board[$board["color"]."King"][0];
		$startr = $board[$board["color"]."King"][1];
		$endc = ($moveData["castling"]=="O-O") ? 7 : 3;
		$endr = ($board["isblack"]) ? 8 : 1;
		if ($controls==1 && $board[$startc][$startr]!=(($board["isblack"]) ? "k" : "K")){throw new Exception(writeException(2, $moveData["move"]));}
	}
	elseif ($moveData["piece"]=="P"){
		$startr = ($board["isblack"]) ? $endr+1 : $endr-1;
		$p = ($board["isblack"]) ? "p" : "P";
		if ($moveData["capture"]==""){
			$pawnStartRow = ($board["isblack"]) ? 7 : 2;
			$startc = colToNumber(substr($moveData["end"], 0, 1));
			if ($board[$startc][$startr]=="*" && $endr==($board["isblack"] ? 5 : 4) && $board[$startc][$pawnStartRow]==$p){$startr = $pawnStartRow;}
		}
		
		if ($controls==1){
			if ($board[$startc][$startr]!=$p){throw new Exception(writeException(2, $moveData["move"]));}
			if ($moveData["capture"]=="x"){
				if ($startc==0){throw new Exception(writeException(2, $moveData["move"]));}
				if ($endc!=$startc-1 && $endc!=$startc+1){throw new Exception(writeException(2, $moveData["move"]));}
			}
		}
	}
	elseif ($moveData["piece"]=="K"){
		$kingCount = 0;
		$p = ($board["isblack"]) ? "k" : "K";
		$endcN = $endc+1; $endcP = $endc-1; $endrN = $endr+1; $endrP = $endr-1;
		if (isset($board[$endcP][$endrN]) && $board[$endcP][$endrN]==$p){$kingCount++; $startc=$endcP; $startr = $endrN;}
		if (isset($board[$endc][$endrN]) && $board[$endc][$endrN]==$p){$kingCount++; $startc=$endc; $startr = $endrN;}
		if (isset($board[$endcN][$endrN]) && $board[$endcN][$endrN]==$p){$kingCount++; $startc=$endcN; $startr = $endrN;}
		if (isset($board[$endcP][$endr]) && $board[$endcP][$endr]==$p){$kingCount++; $startc=$endcP; $startr = $endr;}
		if (isset($board[$endcN][$endr]) && $board[$endcN][$endr]==$p){$kingCount++; $startc=$endcN; $startr = $endr;}
		if (isset($board[$endcP][$endrP]) && $board[$endcP][$endrP]==$p){$kingCount++; $startc=$endcP; $startr = $endrP;}
		if (isset($board[$endc][$endrP]) && $board[$endc][$endrP]==$p){$kingCount++; $startc=$endc; $startr = $endrP;}
		if (isset($board[$endcN][$endrP]) && $board[$endcN][$endrP]==$p){$kingCount++; $startc=$endcN; $startr = $endrP;}
		if ($kingCount!=1){throw new Exception(writeException(2, $moveData["move"]));}
	}
	elseif ($moveData["piece"]=="Q"){
		$suitableQs = array();
		$p = ($board["isblack"]) ? "q" : "Q";
		for ($i=1; $i<8; $i++){$tmpCol = $endc-$i; if (isset($board[$tmpCol][$endr])){if ($board[$tmpCol][$endr]==$p){$suitableQs[] = array($tmpCol, $endr); break;} elseif ($board[$tmpCol][$endr]!="*"){break;}} else{break;}}
		for ($i=1; $i<8; $i++){$tmpCol = $endc+$i; if (isset($board[$tmpCol][$endr])){if ($board[$tmpCol][$endr]==$p){$suitableQs[] = array($tmpCol, $endr); break;} elseif ($board[$tmpCol][$endr]!="*"){break;}} else{break;}}
		for ($i=1; $i<8; $i++){$tmpRow = $endr+$i; if (isset($board[$endc][$tmpRow])){if ($board[$endc][$tmpRow]==$p){$suitableQs[] = array($endc, $tmpRow); break;} elseif ($board[$endc][$tmpRow]!="*"){break;}} else{break;}}
		for ($i=1; $i<8; $i++){$tmpRow = $endr-$i; if (isset($board[$endc][$tmpRow])){if ($board[$endc][$tmpRow]==$p){$suitableQs[] = array($endc, $tmpRow); break;} elseif ($board[$endc][$tmpRow]!="*"){break;}} else{break;}}
		for ($i=1; $i<8; $i++){$tmpCol = $endc-$i; $tmpRow = $endr+$i; if (isset($board[$tmpCol][$tmpRow])){if ($board[$tmpCol][$tmpRow]==$p){$suitableQs[] = array($tmpCol, $tmpRow); break;} elseif ($board[$tmpCol][$tmpRow]!="*"){break;}} else{break;}}
		for ($i=1; $i<8; $i++){$tmpCol = $endc+$i; $tmpRow = $endr+$i; if (isset($board[$tmpCol][$tmpRow])){if ($board[$tmpCol][$tmpRow]==$p){$suitableQs[] = array($tmpCol, $tmpRow); break;} elseif ($board[$tmpCol][$tmpRow]!="*"){break;}} else{break;}}
		for ($i=1; $i<8; $i++){$tmpCol = $endc-$i; $tmpRow = $endr-$i; if (isset($board[$tmpCol][$tmpRow])){if ($board[$tmpCol][$tmpRow]==$p){$suitableQs[] = array($tmpCol, $tmpRow); break;} elseif ($board[$tmpCol][$tmpRow]!="*"){break;}} else{break;}}
		for ($i=1; $i<8; $i++){$tmpCol = $endc+$i; $tmpRow = $endr-$i; if (isset($board[$tmpCol][$tmpRow])){if ($board[$tmpCol][$tmpRow]==$p){$suitableQs[] = array($tmpCol, $tmpRow); break;} elseif ($board[$tmpCol][$tmpRow]!="*"){break;}} else{break;}}
		
		$compatibleQs = array();
		foreach ($suitableQs as $k=>$v){
			if (tryMoveToSeeIfCheck($board, $v, array($endc, $endr))){
				if ($startc==0 && $startr==0){$compatibleQs[] = $v;}
				elseif ($startc!=0 && $startr==0 && $v[0]==$startc){$compatibleQs[] = $v;}
				elseif ($startc==0 && $startr!=0 && $v[1]==$startr){$compatibleQs[] = $v;}
				elseif ($startc!=0 && $startr!=0 && $v[0]==$startc && $v[1]==$startr){$compatibleQs[] = $v;}
			}
		}
		if (count($compatibleQs)<1){throw new Exception(writeException(2, $moveData["move"]));}
		elseif (count($compatibleQs)>1){throw new Exception(writeException(3, $moveData["move"]));}
		else{$startc = $compatibleQs[0][0]; $startr = $compatibleQs[0][1];}
	}
	elseif ($moveData["piece"]=="B"){
		$suitableBs = array();
		$p = ($board["isblack"]) ? "b" : "B";
		for ($i=1; $i<8; $i++){$tmpCol = $endc-$i; $tmpRow = $endr+$i; if (isset($board[$tmpCol][$tmpRow])){if ($board[$tmpCol][$tmpRow]==$p){$suitableBs[] = array($tmpCol, $tmpRow); break;} elseif ($board[$tmpCol][$tmpRow]!="*"){break;}} else{break;}}
		for ($i=1; $i<8; $i++){$tmpCol = $endc+$i; $tmpRow = $endr+$i; if (isset($board[$tmpCol][$tmpRow])){if ($board[$tmpCol][$tmpRow]==$p){$suitableBs[] = array($tmpCol, $tmpRow); break;} elseif ($board[$tmpCol][$tmpRow]!="*"){break;}} else{break;}}
		for ($i=1; $i<8; $i++){$tmpCol = $endc-$i; $tmpRow = $endr-$i; if (isset($board[$tmpCol][$tmpRow])){if ($board[$tmpCol][$tmpRow]==$p){$suitableBs[] = array($tmpCol, $tmpRow); break;} elseif ($board[$tmpCol][$tmpRow]!="*"){break;}} else{break;}}
		for ($i=1; $i<8; $i++){$tmpCol = $endc+$i; $tmpRow = $endr-$i; if (isset($board[$tmpCol][$tmpRow])){if ($board[$tmpCol][$tmpRow]==$p){$suitableBs[] = array($tmpCol, $tmpRow); break;} elseif ($board[$tmpCol][$tmpRow]!="*"){break;}} else{break;}}
	
		$compatibleBs = array();
		foreach ($suitableBs as $k=>$v){
			if (tryMoveToSeeIfCheck($board, $v, array($endc, $endr))){
				if ($startc==0 && $startr==0){$compatibleBs[] = $v;}
				elseif ($startc!=0 && $startr==0 && $v[0]==$startc){$compatibleBs[] = $v;}
				elseif ($startc==0 && $startr!=0 && $v[1]==$startr){$compatibleBs[] = $v;}
				elseif ($startc!=0 && $startr!=0 && $v[0]==$startc && $v[1]==$startr){$compatibleBs[] = $v;}
			}
		}
		if (count($compatibleBs)<1){throw new Exception(writeException(2, $moveData["move"]));}
		elseif (count($compatibleBs)>1){throw new Exception(writeException(3, $moveData["move"]));}
		else{$startc = $compatibleBs[0][0]; $startr = $compatibleBs[0][1];}
	}
	elseif ($moveData["piece"]=="R"){
		$suitableRs = array();
		$p = ($board["isblack"]) ? "r" : "R";
		for ($i=1; $i<8; $i++){$tmpCol = $endc-$i; if (isset($board[$tmpCol][$endr])){if ($board[$tmpCol][$endr]==$p){$suitableRs[] = array($tmpCol, $endr); break;} elseif ($board[$tmpCol][$endr]!="*"){break;}} else{break;}}
		for ($i=1; $i<8; $i++){$tmpCol = $endc+$i; if (isset($board[$tmpCol][$endr])){if ($board[$tmpCol][$endr]==$p){$suitableRs[] = array($tmpCol, $endr); break;} elseif ($board[$tmpCol][$endr]!="*"){break;}} else{break;}}
		for ($i=1; $i<8; $i++){$tmpRow = $endr+$i; if (isset($board[$endc][$tmpRow])){if ($board[$endc][$tmpRow]==$p){$suitableRs[] = array($endc, $tmpRow); break;} elseif ($board[$endc][$tmpRow]!="*"){break;}} else{break;}}
		for ($i=1; $i<8; $i++){$tmpRow = $endr-$i; if (isset($board[$endc][$tmpRow])){if ($board[$endc][$tmpRow]==$p){$suitableRs[] = array($endc, $tmpRow); break;} elseif ($board[$endc][$tmpRow]!="*"){break;}} else{break;}}

		$compatibleRs = array();
		foreach ($suitableRs as $k=>$v){
			if (tryMoveToSeeIfCheck($board, $v, array($endc, $endr))){
				if ($startc==0 && $startr==0){$compatibleRs[] = $v;}
				elseif ($startc!=0 && $startr==0 && $v[0]==$startc){$compatibleRs[] = $v;}
				elseif ($startc==0 && $startr!=0 && $v[1]==$startr){$compatibleRs[] = $v;}
				elseif ($startc!=0 && $startr!=0 && $v[0]==$startc && $v[1]==$startr){$compatibleRs[] = $v;}
			}
		}
		if (count($compatibleRs)<1){throw new Exception(writeException(2, $moveData["move"]));}
		elseif (count($compatibleRs)>1){throw new Exception(writeException(3, $moveData["move"]));}
		else{$startc = $compatibleRs[0][0]; $startr = $compatibleRs[0][1];}
	}
	elseif ($moveData["piece"]=="N"){
		$suitableNs = array();
		$p = ($board["isblack"]) ? "n" : "N";
		$endcN = $endc+1; $endcNN = $endc+2; $endcP = $endc-1; $endcPP = $endc-2; $endrN = $endr+1; $endrNN = $endr+2; $endrP = $endr-1; $endrPP = $endr-2;
		if (isset($board[$endcPP][$endrN]) && $board[$endcPP][$endrN]==$p){$suitableNs[] = array($endcPP, $endrN);}
		if (isset($board[$endcP][$endrNN]) && $board[$endcP][$endrNN]==$p){$suitableNs[] = array($endcP, $endrNN);}
		if (isset($board[$endcN][$endrNN]) && $board[$endcN][$endrNN]==$p){$suitableNs[] = array($endcN, $endrNN);}
		if (isset($board[$endcNN][$endrN]) && $board[$endcNN][$endrN]==$p){$suitableNs[] = array($endcNN, $endrN);}
		if (isset($board[$endcPP][$endrP]) && $board[$endcPP][$endrP]==$p){$suitableNs[] = array($endcPP, $endrP);}
		if (isset($board[$endcP][$endrPP]) && $board[$endcP][$endrPP]==$p){$suitableNs[] = array($endcP, $endrPP);}
		if (isset($board[$endcN][$endrPP]) && $board[$endcN][$endrPP]==$p){$suitableNs[] = array($endcN, $endrPP);}
		if (isset($board[$endcNN][$endrP]) && $board[$endcNN][$endrP]==$p){$suitableNs[] = array($endcNN, $endrP);}
		
		$compatibleNs = array();
		foreach ($suitableNs as $k=>$v){
			if (tryMoveToSeeIfCheck($board, $v, array($endc, $endr))){
				if ($startc==0 && $startr==0){$compatibleNs[] = $v;}
				elseif ($startc!=0 && $startr==0 && $v[0]==$startc){$compatibleNs[] = $v;}
				elseif ($startc==0 && $startr!=0 && $v[1]==$startr){$compatibleNs[] = $v;}
				elseif ($startc!=0 && $startr!=0 && $v[0]==$startc && $v[1]==$startr){$compatibleNs[] = $v;}
			}
		}
		
		if (count($compatibleNs)<1){throw new Exception(writeException(2, $moveData["move"]));}
		elseif (count($compatibleNs)>1){throw new Exception(writeException(3, $moveData["move"]));}
		else{$startc = $compatibleNs[0][0]; $startr = $compatibleNs[0][1];}
	}
	return array("col1"=>$startc, "row1"=>$startr, "col2"=>$endc, "row2"=>$endr);
}

function tryMoveToSeeIfCheck($board, $squareStart, $squareEnd){//IT IS USED TO CONTROL IF YOU PUT YOURSELF IN CHECK ILLEGALLY
	$movedPiece = $board[$squareStart[0]][$squareStart[1]];
	$board[$squareStart[0]][$squareStart[1]] = "*";
	$board[$squareEnd[0]][$squareEnd[1]] = $movedPiece;
	if (isSquareThreated($board, $board[$board["color"]."King"][0], $board[$board["color"]."King"][1])){return 0;} return 1;
}

function isSquareThreated($board, $col, $row){
	$case1 = array("q","r"); $case2 = array("b","q"); $case3 = "p"; $case4 = "n"; $case5 = "k";
	if ($board["isblack"]){$case1 = array("Q","R"); $case2 = array("B","Q"); $case3 = "P"; $case4 = "N"; $case5 = "K";}
	
	for ($i=1; $i<8; $i++){$tmpCol = $col-$i; if (isset($board[$tmpCol][$row])){if (in_array($board[$tmpCol][$row], $case1)){return 1;} elseif ($board[$tmpCol][$row]!="*"){break;}} else{break;}}
	for ($i=1; $i<8; $i++){$tmpCol = $col+$i; if (isset($board[$tmpCol][$row])){if (in_array($board[$tmpCol][$row], $case1)){return 1;} elseif ($board[$tmpCol][$row]!="*"){break;}} else{break;}}
	for ($i=1; $i<8; $i++){$tmpRow = $row+$i; if (isset($board[$col][$tmpRow])){if (in_array($board[$col][$tmpRow], $case1)){return 1;} elseif ($board[$col][$tmpRow]!="*"){break;}} else{break;}}
	for ($i=1; $i<8; $i++){$tmpRow = $row-$i; if (isset($board[$col][$tmpRow])){if (in_array($board[$col][$tmpRow], $case1)){return 1;} elseif ($board[$col][$tmpRow]!="*"){break;}} else{break;}}
	for ($i=1; $i<8; $i++){$tmpCol = $col-$i; $tmpRow = $row+$i; if (isset($board[$tmpCol][$tmpRow])){if (in_array($board[$tmpCol][$tmpRow], $case2)){return 1;} elseif ($board[$tmpCol][$tmpRow]!="*"){break;}} else{break;}}
	for ($i=1; $i<8; $i++){$tmpCol = $col+$i; $tmpRow = $row+$i; if (isset($board[$tmpCol][$tmpRow])){if (in_array($board[$tmpCol][$tmpRow], $case2)){return 1;} elseif ($board[$tmpCol][$tmpRow]!="*"){break;}} else{break;}}
	for ($i=1; $i<8; $i++){$tmpCol = $col-$i; $tmpRow = $row-$i; if (isset($board[$tmpCol][$tmpRow])){if (in_array($board[$tmpCol][$tmpRow], $case2)){return 1;} elseif ($board[$tmpCol][$tmpRow]!="*"){break;}} else{break;}}
	for ($i=1; $i<8; $i++){$tmpCol = $col+$i; $tmpRow = $row-$i; if (isset($board[$tmpCol][$tmpRow])){if (in_array($board[$tmpCol][$tmpRow], $case2)){return 1;} elseif ($board[$tmpCol][$tmpRow]!="*"){break;}} else{break;}}
	
	$colN = $col+1; $colP = $col-1; $rowN = $row+1; $rowP = $row-1;
	if ($board["isblack"]){if ((isset($board[$colP][$rowP]) && $board[$colP][$rowP]==$case3) || (isset($board[$colN][$rowP]) && $board[$colN][$rowP]==$case3)){return 1;}}
	if (!$board["isblack"]){if ((isset($board[$colP][$rowN]) && $board[$colP][$rowN]==$case3) || (isset($board[$colN][$rowN]) && $board[$colN][$rowN]==$case3)){return 1;}}
	
	$colNN = $col+2; $colPP = $col-2; $rowNN = $row+2; $rowPP = $row-2;
	if (isset($board[$colPP][$rowN]) && $board[$colPP][$rowN]==$case4){return 1;}
	if (isset($board[$colP][$rowNN]) && $board[$colP][$rowNN]==$case4){return 1;}
	if (isset($board[$colN][$rowNN]) && $board[$colN][$rowNN]==$case4){return 1;}
	if (isset($board[$colNN][$rowN]) && $board[$colNN][$rowN]==$case4){return 1;}
	if (isset($board[$colPP][$rowP]) && $board[$colPP][$rowP]==$case4){return 1;}
	if (isset($board[$colP][$rowPP]) && $board[$colP][$rowPP]==$case4){return 1;}
	if (isset($board[$colN][$rowPP]) && $board[$colN][$rowPP]==$case4){return 1;}
	if (isset($board[$colNN][$rowP]) && $board[$colNN][$rowP]==$case4){return 1;}
	
	if (isset($board[$colP][$rowN]) && $board[$colP][$rowN]==$case5){return 1;}
	if (isset($board[$col][$rowN]) && $board[$col][$rowN]==$case5){return 1;}
	if (isset($board[$colN][$rowN]) && $board[$colN][$rowN]==$case5){return 1;}
	if (isset($board[$colP][$row]) && $board[$colP][$row]==$case5){return 1;}
	if (isset($board[$colN][$row]) && $board[$colN][$row]==$case5){return 1;}
	if (isset($board[$colP][$rowP]) && $board[$colP][$rowP]==$case5){return 1;}
	if (isset($board[$col][$rowP]) && $board[$col][$rowP]==$case5){return 1;}
	if (isset($board[$colN][$rowP]) && $board[$colN][$rowP]==$case5){return 1;}
	return 0;
}

function moveFromBoard($board, $move, $controls=1){
	$moveData = retrieveMoveData($move);
	$moveLimits = findMoveLimits($moveData, $board, $controls);
	$endSquare = $board[$moveLimits["col2"]][$moveLimits["row2"]];
	$castlingVariation = 0;
	$board["isblack"] = ($board["color"]=="b");
	$isKingCastle = ($moveData["castling"]=="O-O");
	$rowCastling = ($board["isblack"]) ? 8 : 1;
	$destinationKing = ($isKingCastle) ? 7 : 3;
	
	if ($moveData["castling"]!=""){
		if ($controls=1){
			if (($isKingCastle && $board["castlingK".$board["color"]]=="") || (!$isKingCastle && $board["castlingQ".$board["color"]]=="")){throw new Exception(writeException(80));}
			for ($i=1; $i<8; $i++){//Check Squares between the King and the rock
				$tmpCol = ($isKingCastle) ? $board[$board["color"]."King"][0]+$i : $board[$board["color"]."King"][0]-$i;
				if ($tmpCol==$board[$board["color"]."RockSide".(($isKingCastle) ? "K" : "Q")][0]){break;}
				if (isset($board[$tmpCol][$rowCastling])){if ($board[$tmpCol][$rowCastling]!="*"){throw new Exception(writeException(81));}} else{throw new Exception(writeException(80));}
			}
			$tmpCol = $board[$board["color"]."King"][0];
			for ($i=0; $i<8; $i++){//Check Squares from the King to its destination
				$tmpCol = ($tmpCol<$destinationKing) ? $board[$board["color"]."King"][0]+$i : $board[$board["color"]."King"][0]-$i;
				if (!isset($board[$tmpCol][$rowCastling])){throw new Exception(writeException(80));}
				if (($i!=0 && $board[$tmpCol][$rowCastling]!="*") || isSquareThreated($board, $tmpCol, $rowCastling)){throw new Exception(writeException(81));}
				if ($tmpCol==$destinationKing){break;}
			}
		}
		$board["castlingK".$board["color"]] = "";
		$board["castlingQ".$board["color"]] = "";
		$castlingVariation=1;
	}
	elseif ($moveData["piece"]=="P"){
		if ($controls==1){
			if (($moveLimits["row2"]==1 || $moveLimits["row2"]==8) && $moveData["promotion"]==""){throw new Exception(writeException(50, $moveData["move"]));}
			if ($moveData["capture"]=="x" && $endSquare==(($board["isblack"]) ? strtolower($endSquare) : strtoupper($endSquare)) && $board["enpassant"]!=$moveData["end"]){throw new Exception(writeException(10, $moveData["end"]));}
			elseif ($moveData["capture"]!="x" && $board[$moveLimits["col2"]][$moveLimits["row2"]]!="*"){throw new Exception(writeException(11, $moveData["end"]));}
		}
		
		if ($moveData["capture"]=="x"){
			if ($board["enpassant"]==$moveData["end"]){$board[$moveLimits["col2"]][$moveLimits["row2"]+(($board["isblack"]) ? 1 : -1)] = "*";}
			$board["enpassant"] = "-";
		}
		elseif (abs($moveLimits["row1"]-$moveLimits["row2"])==2){
			if (isset($board[$moveLimits["col2"]-1][$moveLimits["row2"]]) && $board[$moveLimits["col2"]-1][$moveLimits["row2"]]==($board["isblack"] ? "P" : "p")){$board["enpassant"] = substr($moveData["end"],0,1).(($moveLimits["row1"]+$moveLimits["row2"])/2);}
			elseif (isset($board[$moveLimits["col2"]+1][$moveLimits["row2"]]) && $board[$moveLimits["col2"]+1][$moveLimits["row2"]]==($board["isblack"] ? "P" : "p")){$board["enpassant"] = substr($moveData["end"],0,1).(($moveLimits["row1"]+$moveLimits["row2"])/2);}
			else{$board["enpassant"] = "-";}
		}
		else {$board["enpassant"] = "-";}
	}
	elseif ($moveData["piece"]=="K"){
		if ($controls==1){
			if (isSquareThreated($board, $moveLimits["col2"], $moveLimits["row2"])){throw new Exception(writeException(70));}
			if ($moveData["capture"]=="x" && $endSquare==($board["isblack"] ? strtolower($endSquare) : strtoupper($endSquare))){throw new Exception(writeException(10, $moveData["end"]));}
			elseif ($endSquare!=($board["isblack"] ? strtoupper($endSquare) : strtolower($endSquare))){throw new Exception(writeException(11, $moveData["end"]));}
		}
		$board[$board["color"]."King"] = array($moveLimits["col2"], $moveLimits["row2"]);
		$board["castlingK".$board["color"]] = "";
		$board["castlingQ".$board["color"]] = "";
		$castlingVariation=1;
	}
	elseif ($moveData["piece"]=="Q" || $moveData["piece"]=="B" || $moveData["piece"]=="R" || $moveData["piece"]=="N"){
		if ($controls==1){
			if ($moveData["capture"]=="x" && $endSquare==($board["isblack"] ? strtolower($endSquare) : strtoupper($endSquare))){throw new Exception(writeException(10, $moveData["end"]));}
			elseif ($endSquare!=($board["isblack"] ? strtoupper($endSquare) : strtolower($endSquare))){throw new Exception(writeException(11, $moveData["end"]));}
		}
		
		if ($moveData["piece"]=="R"){
			if ($moveLimits["col1"]==$board[$board["color"]."RockSideK"][0] && $moveLimits["row1"]==$rowCastling){$board["castlingK".$board["color"]] = ""; $castlingVariation = 1;} //CONTROLLA
			elseif ($moveLimits["col1"]==$board[$board["color"]."RockSideQ"][0] && $moveLimits["row1"]==$rowCastling){$board["castlingQ".$board["color"]] = ""; $castlingVariation = 1;}
		}
	}
	
	if ($moveData["castling"]==""){
		$movedPiece = $board[$moveLimits["col1"]][$moveLimits["row1"]];
		$board[$moveLimits["col1"]][$moveLimits["row1"]] = "*";
		$board[$moveLimits["col2"]][$moveLimits["row2"]] = $movedPiece;
	}
	else{
		$board[$board[$board["color"]."King"][0]][$rowCastling] = "*";
		$board[($isKingCastle) ? 7 : 3][$rowCastling] = ($board["isblack"]) ? "k" : "K";
		$board[$board[$board["color"]."RockSide".(($isKingCastle) ? "K" : "Q")][0]][$rowCastling] = "*";
		$board[($isKingCastle) ? 6 : 4][$rowCastling] = ($board["isblack"]) ? "r" : "R";
		$board[$board["color"]."King"] = array($destinationKing, $rowCastling);
	}
	
	if ($moveData["promotion"]!=""){$board[$moveLimits["col2"]][$moveLimits["row2"]] = ($board["isblack"]) ? strtolower($moveData["promotion"]) : $moveData["promotion"];}
	
	if ($controls==1){
		if (isSquareThreated($board, $board[$board["color"]."King"][0], $board[$board["color"]."King"][1])){throw new Exception(writeException(70));} //Is your king in check after your move?
		
		//Check if the check is real or there is an unreported check
		$squareKingOpponent = $board[(($board["isblack"]) ? "w" : "b")."King"];
		$board["color"] = (($board["isblack"]) ? "w" : "b"); $board["isblack"] = ($board["isblack"]) ? 0 : 1; //inverting the viewpoint
		$checkKingOpponent = isSquareThreated($board, $squareKingOpponent[0], $squareKingOpponent[1]); 
		$board["color"] = ((!$board["isblack"]) ? "w" : "b"); $board["isblack"] = ($board["isblack"]) ? 0 : 1; //reinverting the viewpoint
		if ($moveData["check"]=="" && $checkKingOpponent){throw new Exception(writeException(71, $moveData["move"]));}
		if ($moveData["check"]!="" && !$checkKingOpponent){throw new Exception(writeException(72, $moveData["move"]));}
	}
	
	if ($castlingVariation==1){
		$board["castling"] = $board["castlingKw"].$board["castlingQw"].$board["castlingKb"].$board["castlingQb"];
		if ($board["castling"]==""){$board["castling"] = "-";}
	}
	$board["color"] = (($board["isblack"]) ? "w" : "b"); $board["isblack"] = ($board["isblack"]) ? 0 : 1;
	if ($moveData["piece"]!="P"){$board["enpassant"] = "-";}
	$board["check"] = $moveData["check"];
	$board["moves"] .= $move." ";
	$board["fen"] = writePositionFromBoard($board);
	return $board;
}

function findMoveFromFens($fen1,$fen2){
	$board1 = createBoardFromPosition($fen1);
	$board2 = createBoardFromPosition($fen2);
	
	$result1 = array("fen"=>"");
	try{$result1 = movefromBoard($board1,"O-O");} catch (Exception $ex){}
	if ($result1["fen"]!="" && $result1["fen"]==$fen2){return "O-O";}
	try{$result1 = movefromBoard($board1,"O-O-O");} catch (Exception $ex){}
	if ($result1["fen"]!="" && $result1["fen"]==$fen2){return "O-O-O";}
	
	$changes = array();
	for ($i1=1; $i1<9; $i1++){for ($i2=1; $i2<9; $i2++){if ($board1[$i1][$i2]!=$board2[$i1][$i2]){$changes[] = array($i1, $i2, $board1[$i1][$i2], $board2[$i1][$i2]);}}}
	
	if (count($changes)==3){
		$move = "";
		if ($board1["color"]=="b" && $changes[0][2]=="p"){$move .= numToColumn($changes[0][0])."x";}
		elseif ($board1["color"]=="b" && $changes[1][2]=="p"){$move .= numToColumn($changes[1][0])."x";}
		elseif ($board1["color"]=="b" && $changes[2][2]=="p"){$move .= numToColumn($changes[2][0])."x";}
		elseif ($board1["color"]=="w" && $changes[0][2]=="P"){$move .= numToColumn($changes[0][0])."x";}
		elseif ($board1["color"]=="w" && $changes[1][2]=="P"){$move .= numToColumn($changes[1][0])."x";}
		elseif ($board1["color"]=="w" && $changes[2][2]=="P"){$move .= numToColumn($changes[2][0])."x";}
		
		if ($board1["color"]=="b" && $changes[0][3]=="p"){$move .= numToColumn($changes[0][0]).$changes[0][1];}
		elseif ($board1["color"]=="b" && $changes[1][3]=="p"){$move .= numToColumn($changes[1][0]).$changes[1][1];}
		elseif ($board1["color"]=="b" && $changes[2][3]=="p"){$move .= numToColumn($changes[2][0]).$changes[2][1];}
		elseif ($board1["color"]=="w" && $changes[0][3]=="P"){$move .= numToColumn($changes[0][0]).$changes[0][1];}
		elseif ($board1["color"]=="w" && $changes[1][3]=="P"){$move .= numToColumn($changes[1][0]).$changes[1][1];}
		elseif ($board1["color"]=="w" && $changes[2][3]=="P"){$move .= numToColumn($changes[2][0]).$changes[2][1];}
		if (strlen($move)==4){return $move;}
	}
	elseif (count($changes)==2){
		$move = "";
		$pieceStart = "";
		$pieceEnd = "";
		$startCol = ""; $startRow = "";
		$endCol = ""; $endRow = "";
		$capture = "";
		if ($changes[0][3]=="*"){
			$pieceStart = $changes[0][2]; $pieceEnd = $changes[1][3];
			$startCol = numToColumn($changes[0][0]); $startRow = $changes[0][1];
			$endCol = numToColumn($changes[1][0]); $endRow = $changes[1][1];
		}
		elseif ($changes[1][3]=="*"){
			$pieceStart = $changes[1][2]; $pieceEnd = $changes[0][3];
			$startCol = numToColumn($changes[1][0]); $startRow = $changes[1][1];
			$endCol = numToColumn($changes[0][0]); $endRow = $changes[0][1];
		}
		
		if ($changes[0][2]!="*" && $changes[0][3]!="*"){$capture = "x";}
		elseif ($changes[1][2]!="*" && $changes[1][3]!="*"){$capture = "x";}
		
		if ($pieceStart!="p" && $pieceStart!="P"){$move = strtoupper($pieceStart).$capture.$endCol.$endRow;}
		else{$move = $startCol.$capture.(($capture=="") ? $endRow : $endCol.$endRow); if ($pieceStart!=$pieceEnd){$move .= "=".strtoupper($pieceEnd);}}
		
		try{$result1 = movefromBoard($board1, $move);} catch (Exception $ex){}
		if ($result1["fen"]!="" && $result1["fen"]==$fen2){return $move;}
		$mossa = strtoupper($pieceStart).$startCol.$capture.$endCol.$endRow;
		try{$result1 = movefromBoard($board1, $move);} catch (Exception $ex){}
		if ($result1["fen"]!="" && $result1["fen"]==$fen2){return $move;}
		$mossa = strtoupper($pieceStart).$startRow.$capture.$endCol.$endRow;
		try{$result1 = movefromBoard($board1, $move);} catch (Exception $ex){}
		if ($result1["fen"]!="" && $result1["fen"]==$fen2){return $move;}
		$mossa = strtoupper($pieceStart).$startCol.$startRow.$capture.$endCol.$endRow;
		try{$result1 = movefromBoard($board1, $move);} catch (Exception $ex){}
		if ($result1["fen"]!="" && $result1["fen"]==$fen2){return $move;}
	}
	throw new Exception(writeException(60, array($fen1, $fen2)));
}

function splitFen($fen, $controls=1){
	$fen = preg_replace("/\s+/", " ", trim($fen));
	$parts = explode(" ", $fen);
	if (count($parts)<4 || count($parts)>6){throw new Exception(writeException(91));}
	if (!in_array($parts[1], array("b","w"))){throw new Exception(writeException(91));}
	//I leave the controls on the castling and enpassant part
	return array("position"=>$parts[0], "color"=>$parts[1], "castling"=>$parts[2], "enpassant"=>$parts[3]);
}

function createBoardFromPosition($fen){
	$splittedFen = splitFen($fen);
	$position = preg_replace("/\s+/", " ", trim($splittedFen["position"]));
	$rows = explode("/", $position);
	$board = array();
	$board["wKing"] = array();
	$board["bKing"] = array();
	$board["bRockSideK"] = "";
	$board["bRockSideQ"] = "";
	$board["wRockSideK"] = "";
	$board["wRockSideQ"] = "";
	$col = 1;
	$row = 8;
	foreach ($rows as $k=>$v){
		$rowPieces = str_split($v);
		foreach ($rowPieces as $k1=>$v1){
			if (is_numeric($v1)){for ($i=0; $i<$v1; $i++){$board[$col][$row] = "*"; $col++; if ($col>8){$row = $row-1; $col = 1;}}}
			else{
				if ($v1=="K"){$board["wKing"] = array($col, $row);}
				if ($v1=="k"){$board["bKing"] = array($col, $row);}
				
				if ($v1=="R" && $board["wKing"]==array()){$board["wRockSideQ"] = array($col, $row);} elseif ($v1=="R"){$board["wRockSideK"] = array($col, $row);}
				elseif ($v1=="r" && $board["bKing"]==array()){$board["bRockSideQ"] = array($col, $row);} elseif ($v1=="r"){$board["bRockSideK"] = array($col, $row);}
				
				$board[$col][$row] = $v1;
				$col++;
				if ($col>8){$row = $row-1; $col = 1;}
			}
		}
	}
	
	if ($row!=0 || $col!=1){throw new Exception(writeException(91));}
	$board["moves"] = "";
	$board["startingfen"] = $fen;
	$board["fen"] = $fen;
	$board["color"] = $splittedFen["color"];
	$board["isblack"] = ($board["color"]=="b");
	$board["castling"] = $splittedFen["castling"];
	$board["castlingKw"] = (strpos($splittedFen["castling"], "K")===false) ? "" : "K";
	$board["castlingQw"] = (strpos($splittedFen["castling"], "Q")===false) ? "" : "Q";
	$board["castlingKb"] = (strpos($splittedFen["castling"], "k")===false) ? "" : "k";
	$board["castlingQb"] = (strpos($splittedFen["castling"], "q")===false) ? "" : "q";
	$board["enpassant"] = $splittedFen["enpassant"];
	$board["check"] = "";
	return $board;
}

function writePositionFromBoard($board){
	$position = "";
	for ($f=8; $f>=1; $f--){
		$emptySpaces = 0;
		$row = "";
		for ($c=1; $c<=8; $c++){
			if ($board[$c][$f]=="*"){$emptySpaces++; if ($c==8){$row .= $emptySpaces;}}
			elseif ($emptySpaces!=0){$row .= $emptySpaces.$board[$c][$f]; $emptySpaces=0;}
			elseif (isset($board[$c][$f])){$row .= $board[$c][$f];}
			else{throw new Exception(writeException(99));}
		}
		$position .= $row;
		if ($f!=1){$position .= "/";}
	}
	return $position." ".$board["color"]." ".$board["castling"]." ".$board["enpassant"];
}

function printBoard($board){
	$output = "<table>";
	for ($f=8; $f>=1; $f--){
		$output .= "<tr><td>";
		for ($c=1; $c<=8; $c++){if (!isset($board[$c][$f])){throw new Exception(writeException(99));} $output .= $board[$c][$f]." ";}
		$output .= "</td></tr>";
	}
	$output .= "</table>";
	$out = str_ireplace(" ", "</td><td>", $output);
	$out .= $board["fen"]."<br>";
	$out .= $board["startingfen"]."<br>";
	$out .= $board["moves"]."<br>";
	$out .= json_encode($board["wKing"])."<br>";
	$out .= json_encode($board["bKing"])."<br>";
	$out .= json_encode($board["bRockSideK"])."<br>";
	$out .= json_encode($board["bRockSideQ"])."<br>";
	$out .= json_encode($board["wRockSideK"])."<br>";
	$out .= json_encode($board["wRockSideQ"])."<br>";
	
	return $out;
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

function justEval($line){
	$result = null;
	$annotations = null;
	$multiLineAnnotationDepth = 0;
	foreach (str_split($line) as $char){
		if ($char=="{" || $char=="(") {$multiLineAnnotationDepth++;}
		if ($multiLineAnnotationDepth==0){$result .= $char;}
		else{$annotations .= $char;}
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

function cleanLine($line, $compress=0, $numMoves=52){
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

function playGame($moveList, $lenght=0, $startingFen="rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq -"){
	$moveList1 = cleanLine(removeAnnotations($moveList), 1, $lenght);
	$moveList2 = cleanLine($moveList, 1, $lenght);
	$annotations = justEval($moveList2);
	
	$halfmoves = explode(" ", $moveList1);
	$moveCount = count($halfmoves);
	$arrayPositions = array();
	if ($moveList1==""){return $arrayPositions;}
	$board = createBoardFromPosition($startingFen);
	for ($i=0; $i<(($lenght==0) ? $moveCount : ($moveCount<=$lenght) ? $moveCount : $lenght); $i++){
		$arrayMove = array();
		$board = moveFromBoard($board, $halfmoves[$i], 1);
		$arrayMove[] = $board["fen"];
		$arrayMove[] = $halfmoves[$i];
		$arrayMove[] = (count($annotations)>=count($halfmoves)-1) ? ((isset($annotations[$i][0]) && $annotations[$i][0]!=false) ? substr($annotations[$i][0], 6) : "") : "";
		$arrayPositions[] = $arrayMove;
	}
	return $arrayPositions;
}


?>