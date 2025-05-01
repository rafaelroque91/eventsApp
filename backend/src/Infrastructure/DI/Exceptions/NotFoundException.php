<?php

namespace App\Infrastructure\DI\Exceptions;
class NotFoundException extends \Exception implements \Psr\Container\NotFoundExceptionInterface
{
}