<?php

require_once("Game.php");
require_once("parse_pgn.php");

function pgnToCsvPartite($filePath, $destDir){
    $fileName = basename($filePath);

    $handle = fopen($this->filePath, "r");

    $f1 = fopen($destDir."/partite_".$fileName, 'w');
    if ($f1===false){die("Error opening the file ".$destDir."/partite_".$fileName);}

    $multiLineAnnotationDepth = 0;
    $currentGame = new Game();
    $pgnBuffer = null;
    $haveMoves = false;
    while (($line = fgets($handle))!==false){
        $line = trim($line);
        if (empty($line)){continue;}
        if (strpos($line,'[')===0 && $multiLineAnnotationDepth===0){
            if ($haveMoves){
                //TRAVASA GIOCO CORRENTE

                $multiLineAnnotationDepth = 0;
                $currentGame = new Game();
                $haveMoves = false;
                $pgnBuffer = null;
            }

            if (strpos($line, ' ')===false){throw new \Exception("Invalid metadata: " . $line);}
            list($key, $val) = explode(' ', $line, 2);
            $key = strtolower(trim($key, '['));
            $val = trim($val, '"]');
            switch ($key) {
                case 'event': $currentGame->setEvent($val); break;
                case 'utcdate': $currentGame->setDate($val); break;
                case 'utctime': $currentGame->setTime($val); break;
                case 'white': $currentGame->setWhite($val); break;
                case 'black': $currentGame->setBlack($val); break;
                case 'whiteelo': $currentGame->setWhiteElo($val); break;
                case 'blackelo': $currentGame->setBlackElo($val); break;
                case 'result': $currentGame->setResult($val); break;
                case 'timecontrol': $currentGame->setTimeControl($val); break;
                case 'termination': $currentGame->setTermination($val); break;
                default: break;
            }

            $pgnBuffer .= $line . "\n";
        }
        else{
            $line = cleanline($line, 0);
		    $currentGame->setMoves($currentGame->getMoves() ? $currentGame->getMoves() . " " . $line : $line);
            
            $haveMoves = true;
            $pgnBuffer .= "\n" . $line;
        }
    }

    //TRAVASA GIOCO CORRENTE

    fclose($handle);
}





?>