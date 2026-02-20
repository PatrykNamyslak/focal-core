<?php
namespace PatrykNamyslak\FocalCore\Support;

use InvalidArgumentException;
use PatrykNamyslak\FocalCore\Contracts\Env\EnvBlueprintInterface;
use PatrykNamyslak\PAuth\Enums\EnvironmentVariable;


/**
 * Generate an .env file template by running `DotEnvTemplate::generate()`
 */
abstract class DotEnvTemplate{
    /**
     * Generate a .env file template at the desired directory.
     * * A file called `.generatedEnv` will be created if there is an existing .env file in the specified `$directory`, this is intentional to prevent any data loss.
     * @param string $directory The target directory the .env file should be generated in, I.E `$_SERVER["DOCUMENT_ROOT"]`
     * @param EnvBlueprintInterface[] $blueprints An array of classes that implement the EnvBlueprintInterface.
     * @return void
     */
    public static function generate(string $directory, array $blueprints): void{
        // Append a slash at the end of the $direcory just in case the directory provided does not end with one
        if ($directory[strlen($directory) - 1] !== "/"){
            $directory .= "/";
        }
        $envVariableNames = [];
        foreach($blueprints as $blueprint){
            if (!is_subclass_of(object_or_class: $blueprint, class: EnvBlueprintInterface::class)){
                throw new InvalidArgumentException('All of the classes in $blueprints must implement ' . EnvBlueprintInterface::class);
            }
            // Merge the existing variables and remove duplicates
            $envVariableNames = array_unique(array_merge($envVariableNames, array_column(array: $blueprint::cases(), column_key: "value")));
        }
        
        // Append an equals symbol for each variable and add a new line
        $env = implode("\n", array_map(fn ($item) => $item . '=""', $envVariableNames));
        $envFileName = match(true){
            file_exists($directory . ".env") => ".generatedEnv",
            default => ".env",
        };
        $fullEnvPath = $directory . $envFileName;
        $file = fopen($fullEnvPath, "w");
        fwrite($file, $env);
        fclose($file);
    }
}