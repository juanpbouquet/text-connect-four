<?php
	$salt = (isset($_REQUEST['salt']))?$_REQUEST['salt']:substr(md5(microtime()),0,5);

	if(!isset($_REQUEST['player'])) {
		$_REQUEST['player'] = 1;
	}

	if(!isset($_REQUEST['salt'])) {
		header('Location: ?salt='.$salt.'&player='.$_REQUEST['player']);
	}

	require_once('connectfour.php');
	$game = new ConnectFour($salt);
?>
<script src="https://code.jquery.com/jquery-2.1.1.min.js" type="text/javascript"></script>
<style>
body {
	font-family: monospace;
}

.player1 {
	color: red;
}

.player2 {
	color: yellow;
} 

a {
	text-decoration: none;
	color: #ccc;
}

a:hover {
	color: #aaa;
}
td {
	text-align: center;
}

h1,h2,h3,h4,h5,h6,p {
	text-align: center;
}

td.column:hover td {
	background: #ccc;
	cursor: pointer;
}


table {
	margin: auto;
}
</style>
<h4>Text-Mode Connect4</h4>
<p><small>Just click on a column to drop a coin.</small></p>
<h5 class="player<?php echo $_REQUEST['player']; ?>">You are Player <?php echo $_REQUEST['player']; ?></h5>

<table>
	<tr>
		<?php foreach($game->getBoard() as $id => $column): ?>
			<td data-column="<?php echo $id; ?>" class="column">
				<table>		
					<?php $column = array_reverse($column,true); ?>
					<?php foreach($column as $rid => $row): ?>
						<tr><td>
						<div data-coor="<?php echo $id.','.$rid; ?>" class="<?php echo ($row)?'player'.$row:''; ?>">
							<?php echo ($row)?'o':'x'; ?>
						</div>
						</td></tr>
					<?php endforeach; ?>
				</table>
			</td>
		<?php endforeach; ?>
	</tr>
</table>

<h6 id="yourturn"><?php echo ($_REQUEST['player']==$game->getNextPlayer())?'Your turn':'Waiting for partner...'; ?></h6>
<?php if($_REQUEST['player']==1): ?>
<h6>Send this URL to Player2 to join your game</h6>
<h6><small><?php echo $_SERVER['SERVER_NAME'] . '/?salt='.$salt.'&player=2'; ?></small></h6>
<?php endif; ?>
<?php if (!$game->checkWinner()): ?>
	
<script type="text/javascript">
var player = <?php echo json_encode($_REQUEST['player']); ?>;
var lastupdate = <?php echo json_encode(time()); ?>;
var updateData;

$(function() {

	$('td.column').click(function(e) {
		$.post('api.php', { function: 'dropcoin', player: <?php echo json_encode($_REQUEST['player']); ?>, salt: <?php echo json_encode($game->getSalt()); ?>, column: $(this).data('column') }, function(result) {
			if(result.error != undefined) {
				alert(result.error);
			}
		},'json');
		e.stopPropagation();
		e.preventDefault();
	});
	
	updateData = function() {
		$.post('api.php', { salt: <?php echo json_encode($game->getSalt()); ?>, function: 'update', lastupdate: lastupdate }, function(result) {
			console.log(result);
			if(result.success == 1) {
				$('div[data-coor="'+result.updated_column+'"]').text('o').addClass('player'+result.updated_value);
				lastupdate = result.last_update;
				if(result.nextplayer == player) {
					$('#yourturn').text('Your turn');
				} else {
					$('#yourturn').text('Waiting for partner...');
				}
				updateData();
			}

			if(result.winner != undefined) {
				if(result.winner == player) {
					$('#yourturn').text('You won!!!');
				} else {
					$('#yourturn').text('You lost!!!');
				}

			}

			if(result.error != undefined) {
				alert(result.error);
			}

		}, 'json'); 
	}

	updateData();
});
</script>
<?php else: ?>
	<h5>Game Ended. Player <?php echo $game->checkWinner(); ?> won!!!</h5>
<?php endif; ?>