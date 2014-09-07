<?php 
class ConnectFour {

	const DIRECTION_UP = 1;
	const DIRECTION_RIGHT = 2;
	const DIRECTION_DIAGUP = 3;
	const DIRECTION_DIAGDOWN = 4;

	protected $_board = array();
	protected $_cols = 7;
	protected $_rows = 6;
	protected $_salt = null;
	protected $_lastmove;
	protected $_lastupdate;
	protected $_nextplayer = 1;

	protected $_path = '/tmp/';

	protected $_filename = false;

	public function __construct($salt = false) {

		if(!$salt) {
			$this->_salt = md5(microtime());
		} else {
			$this->_salt = $salt;
		}

		$this->_filename = $this->_salt.'.txt';

		if (file_exists($this->_path . $this->_filename)) {
			$this->reloadData();
		} else {
			$this->_board = $this->initBoard();
			$this->save();
		}
	}

	public function reloadData() {	
			$json = file_get_contents($this->_path . $this->_filename);
			$data = json_decode($json,true);
			$this->_board = $data['board'];
			$this->_nextplayer = $data['next'];
			$this->_lastmove = $data['lastmove'];
			$this->_lastupdate = $data['lastupdate'];
	}

	public function getLastUpdate() {
		return $this->_lastupdate;
	}

	public function getLastMove() {
		return $this->_lastmove;
	}

	public function getNextPlayer() {
		return $this->_nextplayer;
	}

	public function getSalt() {
		return $this->_salt;
	}

	public function save() {
		$file = fopen($this->_path . $this->_filename, "w");
		fwrite($file, json_encode(array('board'=>$this->_board, 
										'next'=> $this->_nextplayer,
										'lastmove'=>$this->_lastmove,
										'lastupdate'=>time())
										));
		fclose($file);
	}

	public function initBoard($cols = 7, $rows = 6) {
		$board = array();
		for($r=0;$r<$rows;$r++) {
			for($c=0;$c<$cols;$c++) {
				$board[$c][$r] = 0;
			}
		}

		return $board;
	}

	public function dropCoin($player, $column) {
		if($player == $this->_nextplayer) {
			$this->_nextplayer = ($player==1)?2:1;
			$row = $this->getAvailableSlot($column);
			$this->_board[$column][$row] = $player;
			$this->_lastmove = $column.','.$row;
			$this->save();
		}
	}

	public function getAvailableSlot($column) {
		return min(array_keys($this->_board[$column], min($this->_board[$column])));
	}

	public function getBoard() {
		return $this->_board;
	}

	public function checkWinner() {
		foreach($this->_board as $column => $rows) {
			foreach($rows as $row => $playerCoin) {
				if($playerCoin) {	
					if($this->checkAdjacent($column, $row, self::DIRECTION_UP) == 4 ||
						$this->checkAdjacent($column, $row, self::DIRECTION_RIGHT) == 4 ||
						$this->checkAdjacent($column, $row, self::DIRECTION_DIAGUP) == 4 ||
						$this->checkAdjacent($column, $row, self::DIRECTION_DIAGDOWN) == 4) {

						return $playerCoin;
				}
			}
		}
	}
		return false;
}

public function getCellStatus($column, $row) {
	return $this->_board[$column][$row];
}

public function checkAdjacent($column, $row, $direction = false, $matchcount = 1) {
	$currentPlayer = $this->getCellStatus($column, $row);

	if($currentPlayer) {
		if(!isset($this->_board[$column][$row])) {
			return $matchcount;
		}

		if($direction) {
			switch($direction) {
				case self::DIRECTION_UP:
				$row++;
				break;
				case self::DIRECTION_RIGHT:
				$column++;
				break;
				case self::DIRECTION_DIAGUP:
				$row++;
				$column++;
				break;
				case self::DIRECTION_DIAGDOWN:
				$row--;
				$column--;
				break;
			}


			if($this->getCellStatus($column, $row) == $currentPlayer) {
				$matchcount++;
				return $this->checkAdjacent($column, $row, $direction, $matchcount);
			} else {
				return $matchcount;
			}
		} 
	}
}
}
