<?php
namespace PatrykNamyslak\PAuth\Blueprints\Http;

use InvalidArgumentException;
use PatrykNamyslak\PAuth\Enums\HttpErrorResponseCode;
use PatrykNamyslak\PAuth\Enums\HttpResponseCode;


class Response{
    private(set) string $json{
        get{
            print_r(json_decode($this->json));
        }
    }
    private(set) ?array $successMessages = NULL;
    private(set) ?array $errorMessages = NULL;

    public function __construct(){
        session_start();
        $this->successMessages = $_SESSION["successMessages"];
        $this->errorMessages = $_SESSION["errorMessages"];
    }

    /**
     * store a message in the response object but also in the current session.
     * @param string $message
     * @return static
     */
    public function setErrorMessages(array $messages){
        $this->errorMessages = $messages;
        session_start();
        $_SESSION["errorMessages"] = $messages;
        return $this;
    }

    public function setSuccessMessages(array $messages){
        $this->successMessages = $messages;
        session_start();
        $_SESSION["successMessages"] = $messages;
        return $this;
    }

    public function isSuccessful(): bool{
        return $this->successMessages !== null and $this->successMessages !== [];
    }
    public function isUnsuccessful(): bool{
        return $this->errorMessages !== null and $this->errorMessages !== [];
    }
    public function handleErrors(?array $errors, bool $expectsHTMLResponse): void{
        if (!$errors or $errors = []){
            return;
        }
        if ($expectsHTMLResponse){
            foreach($this->getErrorMessages() as $eMsg){
                echo $eMsg . "<br>";
            }
            // The reason why there is no 400 error on the HTML/ htmx response is because HTMX automatically ignores any data sent from NON 200 responses
        }else{
            // Display the response in json format
            $this->json($errors);
            $this->outputJSONResponse(HttpErrorResponseCode::BAD_REQUEST);
        }
    }

    /**
     * Get the success messages from the current Response.
     * @return string|null
     */
    public function getSuccessMessages(): array|null{
        return $this->errorMessages;
    }

    /**
     * Get the latest success messages from the last Response.
     */
    public static function successMessages(): array|null{
        session_start();
        return $_SESSION["successMessages"];
    }

    /**
     * Get the error messages from the current Response.
     * @return array|null
     */
    public function getErrorMessages(): array|null{
        return $this->errorMessages;
    }
    /**
     * Get the latest error messages from the last Response.
     */
    public static function errorMessages(){
        session_start();
        return $_SESSION["errorMessages"];
    }

    /**
     * Returns a response in JSON format, ideal for APIs or OAuth
     * @param HttpResponseCode|HttpErrorResponseCode $httpResponseCode
     * @return never Echoed JSON alongside a given http response code
     */
    public function outputJSONResponse(HttpResponseCode|HttpErrorResponseCode $httpResponseCode): never{
        header("Content-Type: application/json; charset=utf-8");
        $messages = json_decode($this->json, 1);
        $response = json_encode([$httpResponseCode, $messages]);
        echo $response;
        Response::httpStatus($httpResponseCode);
    }


    /**
     * Store JSON in the response object, ideal for API responses or for an OAuth implementation
     * @param array|string $data
     * @throws InvalidArgumentException if the data provided is an invalid json string the exception will be thrown
     * @return static
     */
    public function json(array|string $data): static{
        if (is_string($data) and !json_validate($data)){
            throw new InvalidArgumentException('$data passed as json string is invalid. Please check the syntax by echoing');
        }
        $this->json = match(true){
            is_string($data) => $data,
            is_array($data) => json_encode($data),
        };
        return $this;
    }

    public static function httpStatus(HttpResponseCode|HttpErrorResponseCode $code): never{
        http_response_code($code->value);
        exit;
    }
}