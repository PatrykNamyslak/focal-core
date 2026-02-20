<?php
namespace PatrykNamyslak\FocalCore\Wiring;


use Dotenv\Dotenv;
use PatrykNamyslak\FocalCore\Contracts\Env\EnvBlueprintInterface;
enum EnvBlueprint:string implements EnvBlueprintInterface{

    /** GOOGLE OAUTH */
    /**
     * A boolean value to determine whether or not Google Oauth is enabled
     * @var bool
     */
    case GOOGLE_OAUTH = "GOOGLE_OAUTH";
    /**
     * The google secret for OAUTH
     * @var string
     */
    case GOOGLE_OAUTH_SECRET = "GOOGLE_OAUTH_SECRET";
    /**
     * URL for Google OAuth
     * @var string
     */
    case GOOGLE_OAUTH_ENDPOINT = "GOOGLE_OAUTH_ENDPOINT";



    /** GITHUB OAUTH */
    /**
     * A boolean value to determine whether or not GITHUB Oauth is enabled
    */
    case GITHUB_OAUTH = "GITHUB_OAUTH";
    case GITHUB_OAUTH_SECRET = "GITHUB_OAUTH_SECRET";
    /**
     * URL for Github OAuth
     * @var string
     */
    case GITHUB_OAUTH_ENDPOINT = "GITHUB_OAUTH_ENDPOINT";



    case MAGIC_LINK_SECRET_LENGTH = "MAGIC_LINK_SECRET_LENGTH";
    case USER_UNIQUE_ID_LENGTH = "USER_UNIQUE_ID_LENGTH";
    case REGISTER_PAGE_PATH = "REGISTER_PAGE_PATH";
    case USERS_TABLE = "USERS_TABLE";


    // PATBASE
    case DB_DRIVER = "DB_DRIVER";
    case DB_NAME = "DB_NAME";
    case DB_HOST = "DB_HOST";
    case DB_USER = "DB_USER";
    case DB_PASS = "DB_PASS";
    case DB_PORT = "DB_PORT";
    case DB_FETCH_MODE = "DB_FETCH_MODE";
    case PATBASE_LAZYLOAD = "PATBASE_LAZYLOAD";


    public function rules(): ?string{
        return match($this){
            self::PATBASE_LAZYLOAD => "required|boolean|default:true",
            self::DB_FETCH_MODE => "required",
            self::DB_DRIVER => "required",
            default => null,
        };
    }
    public static function load(Dotenv $envLoader): void{
        foreach(self::cases() as $envVariable){
            if ($envVariable->rules()){
                $rules = self::parseRules(rules: $envVariable->rules());
                $validator = $envLoader->required(variables: $envVariable->value);
                foreach($rules["requirements"] as $rule){
                    // If its not the values rule
                    if (!str_contains($rule, "values")){
                        match($rule){
                            "boolean" => $validator->isBoolean(),
                            "int", "integer" => $validator->isInteger(),
                            default => $validator->{$rule}(),
                        };
                    }
                }
                if ($rules["values"]){
                    $validator->allowedValues(choices: $rules["values"]);
                }
            }
        }
    }

    /**
     * Parses the rules from string to an associative array
     * @param string $rules
     * @return array{default: ?string, type: ?string, values: ?array, requirements: ?array}
     */
    public static function parseRules(string $rules): array{
        // Separate the rules
        $rules = explode("|", $rules);
        // Extract the default Value for
        $defaultValue = NULL;
        // Allowed Values for Dotenv::allowedValues()
        $allowedValues = NULL;
        $expectedDataType = NULL;
        foreach($rules as $index => $rule){
            if (str_contains($rule, "default")){
                $defaultValue = explode(":", $rule)[1];
                // remove it from the $rules array
                unset($rules[$index]);
            }
            if (str_contains($rule, "values")){
                // Gets the array of allowed values, i.e values:[1,2,3] -> [1,2,3] (Still a string)
                $allowedValues = explode(":", $rule)[1];
                // Remove the square bracket from the start so [1,2,3] -> 1,2,3]
                $allowedValues = ltrim($allowedValues, "[");
                // Remove the square bracket from the end so [1,2,3] -> 1,2,3
                $allowedValues = rtrim($allowedValues, "]");
                // Turn it into an array of values by separating the string and using a comma as a delimiter, 1,2,3 -> array(1,2,3)
                $allowedValues = explode(",", $allowedValues);
                // remove it from the $rules array
                unset($rules[$index]);
            }
        }
        $rules = array_values(array: $rules);
        return [
            "default" => $defaultValue,
            "type" => $expectedDataType,
            "values" => $allowedValues,
            "requirements" => $rules,
        ];
    }
}