<?php

namespace VMorozov\LaravelHttpClientRequestsLogger;

use GuzzleHttp\Exception\RequestException as GuzzleRequestException;
use Illuminate\Http\Client\RequestException as LaravelHttpRequestException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class HttpClientRequestsLogger
{
    private string $apiName;

    public function __construct(
        private FormDataBodyParser $formDataBodyParser
    ) {}

    public function setApiName(string $apiName): void
    {
        $this->apiName = $apiName;
    }

    public function createLoggingMiddleware(): callable
    {
        return function (callable $handler) {
            return function (RequestInterface $request, array $options) use ($handler) {
                $requestId = $this->generateRequestId();
                $requestStartTimestamp = microtime(true);

                $requestBodyContents = $this->getRequestBodyAsString($request);

                Log::debug($this->apiName.' api request start', [
                    'request_id' => $requestId,
                    'request_method' => $request->getMethod(),
                    'request_url' => (string) $request->getUri(),
                    'request_get_params_string' => $request->getUri()->getQuery(),
                    'request_body_string' => $this->getRequestBodyAsString($request),
                ]);

                return $handler($request, $options)->then(
                    function (ResponseInterface $response) use ($requestStartTimestamp, $requestId, $request) {
                        $responseBodyContents = $this->getResponseBodyAsString($response);

                        $requestEndTimestamp = microtime(true);
                        $requestDuration = round(($requestEndTimestamp - $requestStartTimestamp) * 1000);

                        Log::debug($this->apiName.' api successful response', [
                            'request_id' => $requestId,
                            'request_url' => (string) $request->getUri(),
                            'response_status_code' => $response->getStatusCode(),
                            'response_body_string' => $responseBodyContents,
                            'request_duration_ms' => $requestDuration,
                        ]);

                        return $response;
                    },
                    function (Throwable $reason) use ($requestId, $request, $requestBodyContents) {
                        $errorContext = [
                            'request_id' => $requestId,
                            'request_url' => (string) $request->getUri(),
                            'request_body_string' => $requestBodyContents,
                            'error_message' => $reason->getMessage(),
                        ];

                        if ($reason instanceof LaravelHttpRequestException && $reason->response) {
                            $illuminateResponse = $reason->response;
                            $errorContext['response_status_code'] = $illuminateResponse->status();
                            $errorContext['response_body_string'] = $illuminateResponse->body();
                        } elseif ($reason instanceof GuzzleRequestException && $reason->hasResponse()) {
                            $psrResponse = $reason->getResponse();
                            if ($psrResponse) {
                                $errorContext['response_status_code'] = $psrResponse->getStatusCode();
                                $responseBodyContents = $this->getResponseBodyAsString($psrResponse);
                                $errorContext['response_body_string'] = $responseBodyContents;
                            }
                        }

                        Log::error($this->apiName.' api request failed response', $errorContext);

                        throw $reason;
                    }
                );
            };
        };
    }

    private function generateRequestId(): string
    {
        return Str::uuid()->toString();
    }

    private function getResponseBodyAsString(ResponseInterface $response): string
    {
        $body = $response->getBody();
        $contents = $body->getContents();
        // Rewind the stream so it can be read again if needed elsewhere
        $body->rewind();

        return $this->truncateBodySizeToMaxLengthIfNeeded($contents);
    }

    private function getRequestBodyAsString(RequestInterface $request): string
    {
        $body = $request->getBody();
        $contents = $body->getContents();
        // Rewind the stream so it can be read again if needed elsewhere
        $body->rewind();

        $contentType = $request->getHeaderLine('Content-Type');

        if (str_contains(strtolower($contentType), 'application/x-www-form-urlencoded') || str_contains(strtolower($contentType), 'multipart/form-data;')) {
            $contents = json_encode($this->formDataBodyParser->parseMultipartFormData($contents));
        }

        return $this->truncateBodySizeToMaxLengthIfNeeded($contents);
    }

    private function truncateBodySizeToMaxLengthIfNeeded(string $bodyString): string
    {
        if (strlen($bodyString) > 1000) {
            $bodyString = substr($bodyString, 0, 1000).'...';
        }

        return $bodyString;
    }
}
