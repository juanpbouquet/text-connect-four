<?php
require_once('connectfour.php');

$salt = $_REQUEST['salt'];
$player = $_REQUEST['player'];
$function = $_REQUEST['function'];

$game = new ConnectFour($salt);
$response=array('success'=> 1);

switch($function) {
	case 'dropcoin':
		$column = $_REQUEST['column'];
		if($player == $game->getNextPlayer()) {	
			$game->dropCoin($player, $column);
			
		} else {
			$response = array('error'=>'Not your turn. Please wait.');
		}
		break;

	case 'update':
		$lastupdate = $_REQUEST['lastupdate'];
		$lastmove = $game->getLastUpdate();

		while($lastupdate >= $lastmove) {
			sleep(1);
			$game->reloadData();
			$lastmove = $game->getLastUpdate();
		}

		$lastmove = $game->getLastMove();
		$coords = explode(',',$lastmove);

		$response = array('success'=>1, 'nextplayer'=>$game->getNextPlayer(),'last_update'=>$game->getLastUpdate(), 'updated_column'=>$game->getLastMove(), 'updated_value'=>$game->getCellStatus($coords[0], $coords[1]));

		if($winner = $game->checkWinner()) {
			$response['winner'] = $winner;
		}

		break;	
}

header('Content-Type: application/json');
echo json_encode($response);
?>