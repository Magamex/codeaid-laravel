<?php

namespace App\Http\Controllers;

use App\Models\Player;
use Illuminate\Http\Request;

use App\Helpers\Helpers;

class TeamController extends Controller
{
    public function process(Request $request)
    {
        Helpers::validateRequest($request);

        $requirements = $request->json()->all();
        $playersByPosition = Player::all()->groupBy('position.value');
        $selectedPlayers = [];

        foreach ($requirements as $requirement) {
            $availablePlayers = $playersByPosition->get($requirement['position'], collect());

            if ($availablePlayers->count() < $requirement['numberOfPlayers']) {
                return response()->json([
                    'error' => "Insufficient number of players for position: {$requirement['position']}"
                ], 400);
            }

            $selectedPlayersBySkill = $this->selectPlayersBySkill($availablePlayers, $requirement['mainSkill'], $requirement['numberOfPlayers']);

            if ($selectedPlayersBySkill->isEmpty()) {
                $selectedPlayersBySkill = $this->selectPlayersByHighestSkill($availablePlayers, $requirement['numberOfPlayers']);
            }

            $selectedPlayers = array_merge($selectedPlayers, $selectedPlayersBySkill->all());
        }

        $selectedPlayers = collect($selectedPlayers)->map(function ($player) {
            return [
                'name' => $player->name,
                'position' => $player->position,
                'playerSkills' => collect($player->skills)->map(function ($skill) {
                    return [
                        'skill' => $skill->skill,
                        'value' => $skill->value
                    ];
                })
            ];
        });

        return response()->json($selectedPlayers);
    }

    private function selectPlayersBySkill($players, $mainSkill, $numberOfPlayers)
    {
        return $players
            ->filter(fn($player) => $player->hasSkill($mainSkill))
            ->sortByDesc(fn($player) => $player->getSkillValue($mainSkill))
            ->take($numberOfPlayers);
    }

    private function selectPlayersByHighestSkill($players, $numberOfPlayers)
    {
        return $players
            ->sortByDesc(fn($player) => $player->getHighestSkillValue())
            ->take($numberOfPlayers);
    }
}
