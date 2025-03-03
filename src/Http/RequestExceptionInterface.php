<?php

namespace RoachPHP\Http;

interface RequestExceptionInterface
{
    public function getRequest(): Request;

    public function getReason(): \Throwable;
}
