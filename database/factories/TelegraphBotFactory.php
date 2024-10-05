<?php

namespace OXTechnology\Telegram\Database\Factories;

use DefStudio\Telegraph\Models\TelegraphBot;
use Illuminate\Database\Eloquent\Factories\Factory;

class TelegraphBotFactory extends Factory
{
    protected $model = Bot::class;

    /**
     * @inheritDoc
     */
    public function definition(): array
    {
        return [
            'token' => $this->faker->uuid,
            'name' => $this->faker->word,
        ];
    }
}
