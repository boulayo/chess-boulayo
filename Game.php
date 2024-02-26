<?php

class Game{
	protected $moves;
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
	public function getMovesArray(){return explode(' ', $this->moves);}
	public function setMoves($moves){$moves = trim($moves); $this->moves = $moves;}
	public function getEvent(){return $this->event;}
	public function setEvent($event){$this->event = $event;}
	public function getDate(){return $this->date;}
	public function setDate($date){$this->date = $date;}
	public function getTime(){return $this->time;}
	public function setTime($time){$this->time = $time;}
	public function setResult($result){$this->result = ($result === '?' ? null : $result);}
	public function getResult(){return $this->result;}
	public function getWhite(){return $this->white;}
	public function setWhite($white){$this->white = $white;}
	public function getBlack(){return $this->black;}
	public function setBlack($black){$this->black = $black;}
	public function setWhiteElo($whiteElo){$this->whiteElo = $whiteElo === '?' ? null : $whiteElo;}
	public function getWhiteElo(){return $this->whiteElo;}
	public function setBlackElo($blackElo){$this->blackElo = $blackElo === '?' ? null : $blackElo;}
	public function getBlackElo(){return $this->blackElo;}
	public function setTimeControl($timecontrol){$this->timecontrol = $timecontrol === '?' ? null : $timecontrol;}
	public function getTimeControl(){return $this->timecontrol;}
	public function setTermination($termination){$this->termination = $termination === '?' ? null : $termination;}
	public function getTermination(){return $this->termination;}
}

?>