<?php

class Nemesis extends SantoriniPower
{
	public function __construct($game, $playerId)
	{
		parent::__construct($game, $playerId);
		$this->id    = NEMESIS;
		$this->name  = clienttranslate('Nemesis');
		$this->title = clienttranslate('Goddess of Retribution');
		$this->text  = [
			clienttranslate("[End of Your Turn:] If none of an opponent's Workers neighbor yours, you may force both of your Workers to spaces occupied by two of an opponent's Workers, and vice versa."),
			clienttranslate("[REVISED POWER]"),
		];
		$this->playerCount = [2, 3, 4];
		$this->golden  = false;
		$this->orderAid = 42;

		$this->implemented = true;
	}

	/* * */

	public function computePowerData()
	{
		// Nemesis must have exactly 2 workers
		$workers = $this->game->board->getPlacedActiveWorkers();
		if (count($workers) != 2) {
			return false;
		}

		$opponents = $this->game->playerManager->getOpponentsIds();
		foreach ($opponents as $opp) {
			// Opponent must have at least 2 workers
			$oppWorkers = $this->game->board->getPlacedWorkers($opp);
			$count = count($oppWorkers);
			if ($count < 2) {
				continue;
			}

			// None can neighbor Nemesis
			Utils::FilterWorkers($oppWorkers, function ($oppWorker) use ($workers) {
				return !$this->game->board->isNeighbour($workers[0], $oppWorker) && !$this->game->board->isNeighbour($workers[1], $oppWorker);
			});
			if (count($oppWorkers) != $count) {
				continue;
			}

			foreach ($workers as &$worker) {
				if (!array_key_exists('works', $worker)) {
					$worker['works'] = [];
				}
				foreach ($oppWorkers as $oppWorker) {
					$worker['works'][] = $oppWorker;
				}
			}
		}
		Utils::cleanWorkers($workers);
		if (empty($workers)) {
			return false;
		}

		$this->game->log->addAction('powerData', [], $workers, $this->playerId);
		return true;
	}

	public function stateAfterBuild()
	{
		return $this->computePowerData() ? 'power' : null;
	}

	public function argUsePower(&$arg)
	{
		$arg['power'] = $this->id;
		$arg['power_name'] = $this->name;
		$arg['skippable'] = true;

		$arg['workers'] = $this->game->log->getLastAction('powerData', $this->playerId);
		$used = $this->game->log->getLastAction('usePowerNemesis', $this->playerId);
		if ($used != null) {
			// Second swap is required, filter same opponent
			$arg['skippable'] = false;
			Utils::filterWorks($arg, function ($space, $worker) use ($used) {
				return $worker['id'] != $used['nemesisWorkerId'] && $space['id'] != $used['oppWorkerId'] && $space['player_id'] == $used['oppId'];
			});
		}
	}

	public function usePower($action)
	{
		// Get info about workers 
		$worker = $this->game->board->getPiece($action[0]);
		$oppWorker = $this->game->board->getPiece($action[1]['id']);

		// Only the first swap counts for stats
		$isFirst = $this->game->log->getLastAction('usePowerNemesis', $this->playerId) == null;
		$stats = $isFirst ? [[$this->playerId, 'usePower']] : [];
		$this->game->log->addAction('usePowerNemesis', $stats, [
			'nemesisWorkerId' => $worker['id'],
			'oppWorkerId' => $oppWorker['id'],
			'oppId' => $oppWorker['player_id'],
		]);

		$mySpace = $this->game->board->getCoords($worker);
		$oppSpace = $this->game->board->getCoords($oppWorker);

		$this->game->board->setPieceAt($worker, $oppSpace);
		$this->game->log->addForce($worker, $oppSpace);
		$this->game->board->setPieceAt($oppWorker, $mySpace);
		$this->game->log->addForce($oppWorker, $mySpace);

		// Notify force
		$this->game->notifyAllPlayers('workerMovedInstant', $this->game->msg['powerForce'], [
			'i18n' => ['power_name', 'level_name'],
			'piece' => $worker,
			'space' => $oppSpace,
			'power_name' => $this->getName(),
			'player_name' => $this->game->getActivePlayerName(),
			'player_name2' => $this->game->getActivePlayerName(),
			'level_name' => $this->game->levelNames[intval($oppSpace['z'])],
			'coords' => $this->game->board->getMsgCoords($worker, $oppSpace),
		]);

		$this->game->notifyAllPlayers('workerMoved', $this->game->msg['powerForce'], [
			'i18n' => ['power_name', 'level_name'],
			'piece' => $oppWorker,
			'space' => $mySpace,
			'power_name' => $this->getName(),
			'player_name' => $this->game->getActivePlayerName(),
			'player_name2' => $this->game->playerManager->getPlayer($oppWorker['player_id'])->getName(),
			'level_name' => $this->game->levelNames[intval($mySpace['z'])],
			'coords' => $this->game->board->getMsgCoords($oppWorker, $mySpace),
		]);

		if ($isFirst) {
			$nextArg = [];
			$this->argUsePower($nextArg);
			if (count($nextArg['workers'][0]['works']) == 1) {
				// If only one option for second swap, do it automatically
				$nextAction = [$nextArg['workers'][0]['id'], $nextArg['workers'][0]['works'][0]];
				$this->usePower($nextAction);
			}
		}
	}

	public function stateAfterUsePower()
	{
		return count($this->game->log->getLastActions(['usePowerNemesis'])) == 2 ? 'endturn' : 'power';
	}

	public function stateAfterSkipPower()
	{
		return 'endturn';
	}
}
