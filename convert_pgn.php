<?php

require_once("Game.php");
require_once("parse_pgn.php");

$a = microtime(1);
pgnToCsvPartite("C:\\xampp\\htdocs\\chess-boulayo\\files_pgn\\lichess_db_standard_rated_2024-01.pgn","C:\\xampp\\htdocs\\chess-boulayo\\files_csv");
$b = microtime(1);
echo $b-$a;

function pgnToCsvPartite($filePath, $destDir, $id=1){
    $fileName = basename($filePath);
    $fileDate = str_replace("-","",substr($fileName,26,7));
    $handle = fopen($filePath, "r");

    $f1 = fopen($destDir."/partite_".$fileName, 'w');
    if ($f1===false){die("Error opening the file ".$destDir."/partite_".$fileName);}

    $game = new Game();
    $haveMoves = false;

    while (($line = fgets($handle))!==false){
        $line = trim($line);
        if (empty($line)){continue;}
        if (strpos($line,'[')!==0){$game->setMoves($game->getMoves() ? $game->getMoves() . " " . $line : $line);}
        else{
            if ($game->haveMoves()){
                fputcsv($f1, array($id, $fileDate, $game->getIdType(), $game->getWhite(), $game->getBlack(), $game->getTimeControl(), $game->getIdResult(), $game->getIdTermination(), $game->getDateTime(), $game->getWhiteElo(), $game->getBlackElo(), cleanLine($game->getMoves())));
                
                $game = new Game();
                $id++;
            }

            if (strpos($line, " ")===false){throw new \Exception("Invalid metadata: " . $line);} //OGNI RIGA PARSABILE DI UN PGN HA SPAZI IN MEZZO
            list($key, $val) = explode(' ', $line, 2);
            $key = strtolower(trim($key, '['));
            $val = trim($val, '"]');
            
            switch ($key) {
                case 'event': $game->setEvent($val); break;
                case 'utcdate': $game->setDate($val); break;
                case 'utctime': $game->setTime($val); break;
                case 'white': $game->setWhite($val); break;
                case 'black': $game->setBlack($val); break;
                case 'whiteelo': $game->setWhiteElo($val); break;
                case 'blackelo': $game->setBlackElo($val); break;
                case 'result': $game->setResult($val); break;
                case 'timecontrol': $game->setTimeControl($val); break;
                case 'termination': $game->setTermination($val); break;
                default: break;
            }
        }
    }

    fputcsv($f1, array($id, $fileDate, $game->getIdType(), $game->getWhite(), $game->getBlack(), $game->getTimeControl(), $game->getIdResult(), $game->getIdTermination(), $game->getDateTime(), $game->getWhiteElo(), $game->getBlackElo(), cleanLine($game->getMoves())));
    
    fclose($f1);
    fclose($handle);
}





?>