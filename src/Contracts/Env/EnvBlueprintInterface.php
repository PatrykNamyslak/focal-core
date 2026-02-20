<?php
namespace PatrykNamyslak\FocalCore\Contracts\Env;

use Dotenv\Dotenv;

/**
 * Env Blueprint for creating env blueprints, these can be used in the Kernel to generate a `.env` file and also to set rules for your custom environment variables.
 */
interface EnvBlueprintInterface{
    /**
     * Define rules for the env loader
     * * Returns `NULL` when there are no rulesets for the environment variable
     * @return string|null
     */
    public function rules(): ?string;
    public static function load(Dotenv $envLoader);
    public static function parseRules(string $rules): array;
}