<?php
namespace PatrykNamyslak\FocalCore;

use Exception;
use InvalidArgumentException;
use PatrykNamyslak\FocalCore\Contracts\Env\EnvBlueprintInterface;
use PatrykNamyslak\FocalCore\Support\DotEnvTemplate;
use PatrykNamyslak\PAuth\Blueprints\Http\Response;

/**
 * The core of the Focal Framework
 */
class Kernel{
    /** 
     * The root of the app, should NOT end in a trailing slash 
     * @var string
    */
    protected string $basePath;

    private static ?self $instance = NULL;

    protected bool $booted = false;

    /** This is where booted services will be held, such as Routers and facades */
    protected array $services = [];

    /**
     * Enums that hold required Environment variables and their rules, used for .env generation and dynamic rule checking!
     * @var array
     */
    protected static array $envBlueprints = 
    [
        // This is the core environment variables required for the core
        \PatrykNamyslak\FocalCore\Wiring\EnvBlueprint::class,
    ];


    public function __construct(string $basePath){
        $this->basePath = rtrim($basePath, "/");
        self::$instance = $this;
    }

    public static function getInstance(): ?Kernel{
        return self::$instance;
    }

    /**
     * Bind `BOOTED` (Instantised) services such as:
     * * A Router
     * * Service Proivders
     * * Controllers
     * * Facades
     * * Database Control Interfaces
     * @param mixed $serviceName The name that will be used as a `key` in the $services property
     * @param object $service
     * @return static
     */
    public function bind(string $serviceName, object $service): static{
        $this->services[$serviceName] = $service;
        return $this;
    }

    public function get(string $service): object{
        if (!$this->has($service)){
            throw new InvalidArgumentException("Service {$service} is not registered in the App Kernel");
        }
        return $this->services[$service];
    }

    /**
     * Check whether a service is registered / binded
     * @param string $service
     * @return bool
     */
    public function has(string $service): bool{
        return isset($this->services[$service]);
    }

    /**
     * Bootstrap the app
     * @return void
     */
    public function bootstrap(): void{
        $this->bootServices(); // TBA
        $this->loadVariablesFromEnvFile();
        $this->booted = true;
    }


    protected function bootServices(){
        // TO BE IMPLEMENTED
    }

    /**
     * Loads the variables from env and makes sure all of the variables in the provided env blueprints are there and the rules() are met
     * @return void
     */
    public function loadVariablesFromEnvFile(): void{
        if (!file_exists($this->basePath . "/.env")){
            throw new Exception("{$this->basePath}/.env does not exist, Please run " . 'Kernel::generateEnv() in an object instance to generate an .env template.');
        }
        $this->get("envLoader")->load();
        foreach(self::$envBlueprints as $blueprint){
            $blueprint::load(envLoader: $this->get("envLoader"));
        }
    }


    /**
     * Generates a env file by using the provided Env Blueprints
     * @return void
     */
    public function generateEnv(): void{
        DotEnvTemplate::generate(directory: $this->basePath, blueprints: self::$envBlueprints);
    }

    /**
     * Bind custom Env variables to the application
     * @param string $blueprint Must be the fully qualified name of a class that implements the `EnvBlueprintInterface`
     * @throws InvalidArgumentException
     * @return static
     */
    public function bindEnvBlueprint(string $blueprint){
        if (!is_subclass_of(object_or_class: $blueprint, class: EnvBlueprintInterface::class)){
            throw new InvalidArgumentException('$blueprint must implement ' . EnvBlueprintInterface::class);
        }
        self::$envBlueprints[] = $blueprint;
        return $this;
    }


    public function inProduction(): bool{
        return env("production");
    }
    public function isInProduction(): bool{
        return $this->inProduction();
    }
    public function inDevelopment(): bool{
        return !env("production");
    }
    public function isInDevelopment(): bool{
        return $this->inDevelopment();
    }

    public function handle(PatrykNamyslak\Pouter\Blueprints\Http\Request|Focal\Core\Router\Blueprints\Http\Request $request){
        $this->get(service: 'router')->handleIncomingRequest($request);
    }
    public function terminate(PatrykNamyslak\Pouter\Blueprints\Http\Request|Focal\Core\Router\Blueprints\Http\Request $request, Response $response){}
}