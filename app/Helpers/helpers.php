<?php

namespace App\Helpers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Enums\PlayerSkill as PlayerSkillEnum;
use App\Enums\PlayerPosition as PlayerPositionEnum;
use Illuminate\Validation\Rules\Enum;

class Helpers
{
    public static function validateRequest(Request $request)
        {
            $validator = Validator::make($request->all(), [
                '*' => 'required|array',
                '*.position' => ['required', new Enum(PlayerPositionEnum::class)],
                '*.mainSkill' => ['required', new Enum(PlayerSkillEnum::class)],
                '*.numberOfPlayers' => 'required|integer|min:1|max:11'
            ]);

            if ($validator->fails()) {
                $errors = $validator->errors();
                $firstErrorField = $errors->keys()[0];
                $firstErrorValue = $request->input($firstErrorField);
                $field = last(explode('.', $firstErrorField));

                return response()->json([
                    'message' => "Invalid value for $field: $firstErrorValue"
                ], 400);
            }
        }
}
