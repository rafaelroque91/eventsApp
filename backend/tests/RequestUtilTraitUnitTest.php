<?php

namespace Tests\Unit\App\Http\Controllers;

use App\Application\DTO\EventDTO;
use App\Application\Validation\RequestUtilTrait;
use Carbon\Carbon;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class RequestUtilTraitUnitTest extends TestCase
{
    protected $requestUtilTrait;

    protected function setUp(): void
    {
        parent::setUp();

        //instance the Trait
        $this->requestUtilTrait = new class {
            use RequestUtilTrait;
        };
    }

    #[DataProvider('validateCasesProviderSuccess')]
    public function testValidateSuccess($input, $rules): void
    {
        $eventDto = $this->requestUtilTrait->validate($input,
                  $rules, EventDTO::class);

        $this->assertEquals($eventDto->title, $input['title']?? null);
        $this->assertEquals($eventDto->description, $input['description'] ?? null);
        $this->assertEquals($eventDto->startDate, Carbon::make($input['startDate']?? null));
        $this->assertEquals($eventDto->endDate, Carbon::make($input['endDate']?? null));
    }

    public static function validateCasesProviderSuccess(): array
    {
        return [
            "test_all_fields" => [
                'input' => [
                    'title' => 'user123',
                    'description' => 'description',
                    'startDate' => '2025/04/01',
                    'endDate' => '2025/04/02'
                ],
                'rules' => [
                    'title' => ['required','string'],
                    'description' => ['required','string'],
                    'startDate' => ['optional','date'],
                    'endDate' => ['optional','date']
                ]
            ],
            "test_no_nullable_fields_null" => [
                'input' => [
                    'title' => 'user1234',
                ],
                'rules' => [
                    'title' => ['required','string'],
                    'description' => ['optional','string'],
                    'startDate' => ['optional','date'],
                    'endDate' => ['optional','date']
                ]
            ],
        ];
    }

    #[DataProvider('validateCasesProviderError')]
    public function testValidateError($input, $rules): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->requestUtilTrait->validate($input,
            $rules, EventDTO::class);
    }

    public static function validateCasesProviderError(): array
    {
        return [
            "test_description_null" => [
                'input' => [
                    'title' => 'user123',
                    'description' => null,
                ],
                'rules' => [
                    'title' => ['required','string'],
                    'description' => ['required','string'],
                    'startDate' => ['optional','date'],
                    'endDate' => ['optional','date']
                ]
            ],
            "test_date_null" => [
                'input' => [
                    'title' => 'user123',
                    'description' => "test",
                ],
                'rules' => [
                    'title' => ['required','string'],
                    'description' => ['required','string'],
                    'startDate' => ['required','date'],
                    'endDate' => ['optional','date']
                ]
            ],
        ];
    }
}