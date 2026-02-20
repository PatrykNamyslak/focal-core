<?php
namespace PatrykNamyslak\PAuth\Blueprints;

use PatrykNamyslak\Patbase\Facades\DB as DBFacade;


abstract class Repository{
    abstract protected string $table;

    /**
     * Protected column names that should not be fetched on a fetch all query
     * @var string[]
     */
    abstract protected array $guarded = [];
    
    public function __construct(DBFacade $db){}
}