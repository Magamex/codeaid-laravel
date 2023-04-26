<?php

// /////////////////////////////////////////////////////////////////////////////
// PLEASE DO NOT RENAME OR REMOVE ANY OF THE CODE BELOW.
// YOU CAN ADD YOUR CODE TO THIS FILE TO EXTEND THE FEATURES TO USE THEM IN YOUR WORK.
// /////////////////////////////////////////////////////////////////////////////


namespace App\Http\Controllers;

use App\Models\Player;
use App\Models\PlayerSkill;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;

use App\Helpers\Helpers;

class PlayerController extends Controller
{
    public function index()
    {
        $players = Player::all(['id','name','position']);
        return response()->json($players);
    }

    public function show($id)
    {
        $player = Player::with('skills')->find($id);

        if (!$player) {
            return response()->json([
                'message' => 'Player not found'
            ], 404);
        }

        return response()->json([
            'id' => $player->id,
            'name' => $player->name,
            'position' => $player->position,
            'playerSkills' => $player->skills
        ]);
    }

    public function store(Request $request)
    {
        try {
            Helpers::validateRequest($request);

            $player = Player::create($request->only(['name', 'position']));
            $playerSkills = collect($request->input('playerSkills'))->map(function ($skillData) use ($player) {
                return new PlayerSkill([
                    'skill' => $skillData['skill'],
                    'value' => $skillData['value'],
                    'player_id' => $player->id,
                ]);
            });
            $player->skills()->saveMany($playerSkills);

            return response()->json([
                'id' => $player->id,
                'name' => $player->name,
                'position' => $player->position,
                'playerSkills' => $player->skills
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Server error'], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $player = Player::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Player not found'
            ], 404);
        }

        Helpers::validateRequest($request);

        $playerData = $request->only(['name', 'position']);
        $player->update($playerData);

        if ($request->has('playerSkills')) {
            $playerSkills = $request->input('playerSkills');
            $skills = collect($playerSkills)->map(fn ($skill) => new PlayerSkill($skill));
            $player->skills()->delete();
            $player->skills()->createMany($skills->toArray());
        }

        $player->refresh();

        return response()->json([
            'id' => $player->id,
            'name' => $player->name,
            'position' => $player->position,
            'playerSkills' => $player->skills
        ]);
    }

    public function destroy(Request $request, $id)
    {
        try {
            $player = Player::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Player not found'
            ], 404);
        }

        $player->delete();

        return response()->json([
            'message' => 'Player deleted successfully'
        ]);
    }
}
