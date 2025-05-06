<?php

declare(strict_types=1);

namespace App\Application\Validation;

use App\Application\DTO\QueryParamsDTO;
use App\Application\DTO\RequestDTOInterface;
use Carbon\Carbon;

trait RequestUtilTrait
{
    const int OPTION_FIELD_REQUIRED = 0;
    const int OPTION_FIELD_TYPE = 1;

    /**
     * @param array $rules
     * @param string $requestDTOInterfaceDto
     * @return RequestDTOInterface
     */
    public function validate(array $rules, string $requestDTOInterfaceDto) : RequestDTOInterface
    {
        $input = json_decode(file_get_contents('php://input'), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->jsonInvalidRequestResponse(['request' => json_last_error_msg()]);
        }

        if (!is_subclass_of($requestDTOInterfaceDto, RequestDTOInterface::class)) {
            throw new \LogicException("Dto Class invalid");
        }

        $validated = [];
        $errors = [];

        foreach ($rules as $field => $options) {

            $fieldValue = $input[$field] ?? null;

            if ($options[self::OPTION_FIELD_REQUIRED] === 'required' && empty($fieldValue)) {
                $errors[] = [$field => "The field is required."];
            }

            if (empty($fieldValue)) {
                continue;
            }

            if ($options[self::OPTION_FIELD_TYPE] == 'date') {
                try {
                    $value = Carbon::make($fieldValue);

                    if ($value === false) {
                        $errors[] = [$field => "The field is invalid. Use YYYY-MM-DD format."];
                        continue;
                    }
                    $validated[$field] = $value;
                } catch (\Exception) {
                    $errors[] = [$field => "The field is invalid. Use YYYY-MM-DD format."];
                }
            }

            if ($options[self::OPTION_FIELD_TYPE] == 'string') {
                $validated[$field] = $fieldValue;
            }

            //todo add to other types
        }

        if (count($errors) > 0) {
            $this->jsonInvalidRequestResponse($errors);
        }


        return $requestDTOInterfaceDto::createFromRequestData($validated);
    }

    /**
     * @param array $params
     * @return QueryParamsDTO
     */
    private function getQueryParams(array $params) : QueryParamsDTO
    {
        return QueryParamsDTO::createFromRequest($params);
    }

    /**
     * @param mixed $data
     * @return string
     */
    private function jsonResponseCreated(mixed $data): string
    {
        return $this->jsonResponse($data, 201);
    }

    /**
     * @param mixed $data
     * @param int $statusCode
     * @return string
     */
    private function jsonResponse(mixed $data, int $statusCode = 200): string
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        return json_encode($data);
    }

    /**
     * @param string $message
     * @return string
     */
    private function jsonResponseNotFound(string $message): string
    {
        return $this->jsonResponse([
                "message" => $message,
            ], 404);

    }

    /**
     * @param array $errors
     * @return string
     */
    private function jsonInvalidRequestResponse(array $errors): string
    {
        return $this->jsonResponse([
            "message" => "There are some validation errors",
            'errors' => $errors], 422);
    }

    /**
     * @param \Exception $e
     * @param string|null $friendlyMessage
     * @param int $code
     * @return string
     */
    private function jsonErrorResponse(\Exception $e, ?string $friendlyMessage = null, int $code = 500): string
    {
        error_log($e->getMessage() . "\n" . $e->getTraceAsString());

        return $this->jsonResponse(
            [
                "message" => $friendlyMessage,
                "errors" => [$e->getMessage()],
                "trace" => APP_DEBUG ? $e->getTraceAsString() : null,
            ],
            $code);
    }
}