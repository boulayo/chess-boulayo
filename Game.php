<?php

class Game{
	protected $moves;
	protected $event;
	protected $date;
	protected $time;
	protected $white;
	protected $black;
	protected $result;
	protected $whiteElo;
	protected $blackElo;
	protected $timecontrol;
	protected $termination;

	public function getMoves(){return $this->moves;}
	public function setMoves($moves){$moves = trim($moves); $this->moves = $moves;}
	public function getEvent(){return $this->event;}
	public function setEvent($event){$this->event = $event;}
	public function getDate(){return $this->date;}
	public function setDate($date){$this->date = $date;}
	public function getTime(){return $this->time;}
	public function setTime($time){$this->time = $time;}
	public function getResult(){return $this->result;}
	public function setResult($result){$this->result = ($result === '?' ? null : $result);}
	public function getWhite(){return $this->white;}
	public function setWhite($white){$this->white = $white;}
	public function getBlack(){return $this->black;}
	public function setBlack($black){$this->black = $black;}
	public function getWhiteElo(){return $this->whiteElo;}
	public function setWhiteElo($whiteElo){$this->whiteElo = $whiteElo === '?' ? 0 : (int) $whiteElo;}
	public function getBlackElo(){return $this->blackElo;}
	public function setBlackElo($blackElo){$this->blackElo = $blackElo === '?' ? 0 : (int) $blackElo;}
	public function getTimeControl(){return $this->timecontrol;}
	public function setTimeControl($timecontrol){$this->timecontrol = $timecontrol === '?' ? null : $timecontrol;}
	public function getTermination(){return $this->termination;}
	public function setTermination($termination){$this->termination = $termination === '?' ? null : $termination;}

	public function haveMoves(){return isset($this->moves) ? true : false;}
	public function getDateTime(){return str_replace('.', '-', $this->date)." ".$this->time;}
	public function getIdResult(){return ($this->result=="0-1") ? 0 : (($this->result=="1-0") ? 2 : 1);}
	public function getIdType(){
		$type = strtolower(addslashes((strpos($this->event,"http")>0) ? substr($this->event, 0, strpos($this->event,"http")) : $this->event)); //NON RICORDO PERCHÉ FACCIO QUESTO CONTROLLO
		$idtype = 0;
		if (strpos($type, "ultrabullet")!==false){$idtype = 1;}
		elseif (strpos($type, "bullet")!==false){$idtype = 2;}
		elseif (strpos($type, "blitz")!==false){$idtype = 3;}
		elseif (strpos($type, "rapid")!==false){$idtype = 4;}
		elseif (strpos($type, "standard")!==false){$idtype = 5;}
		elseif (strpos($type, "classical")!==false){$idtype = 6;}
		elseif (strpos($type, "correspondence")!==false){$idtype = 7;}
		elseif (strpos($type, "master")!==false){$idtype = 8;}
		return $idtype;
	}
	public function getIdTermination(){
		$termination = $this->termination;
		$idtermination = 0;
		if ($termination=="Normal"){$idtermination=1;}
		elseif ($termination=="Time forfeit"){$idtermination=2;}
		elseif ($termination=="Rules infraction"){$idtermination=3;}
		elseif ($termination=="Abandoned"){$idtermination=4;}
		elseif ($termination=="Unterminated"){$idtermination=96;}
		else{$idtermination=99;}
		return $idtermination;
	}

}

?>