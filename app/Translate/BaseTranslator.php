<?php

namespace App\Translate;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\RequestOptions;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

abstract class BaseTranslator
{
    protected bool $wasSuccessful = false;
    protected bool $hasError = false;
    private bool $enableBalancer = false;
    private bool $echo = false;
    protected string $lastError = '';
    protected string $originalText = '';
    protected string $translatedText = '';
    protected string $requestContent = '';
    protected string $targetUrl;
    protected string $name = '';

    protected \Exception|ClientException|GuzzleException $crawlException;


    public function __construct(bool $enableBalancer = false, bool $echo = false) {
        $this->enableBalancer = $enableBalancer;
        $this->echo = $echo;
    }

    abstract function translate(string $originalText) : string;

    public function getTargetUrl(): string {
        return $this->targetUrl;
    }


    protected function sendRequest($postParams) : bool {

        try {
            $client = new Client();

            $options = [
                'timeout' => 10,
                'verify' => false,
                'headers' => [
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:104.0) Gecko/20100101 Firefox/105.0',
                    'Content-Type' => 'application/x-www-form-urlencoded'
                ],
                RequestOptions::FORM_PARAMS => $postParams // post params
            ];

            if ($this->enableBalancer) {
                $blType = rand(1, 100) <= 100 ? 'web_proxy' : 'https_proxy';
                $balancer = balancer()->getNextBalancer($blType, 'translate with ' . $this->name);
                $balancer->configGuzzleClient($this->targetUrl, $options);
                if($this->echo){
                    echo sprintf("Balancer used for translate is: %s<br>\n", $balancer->getBalancerInfo());
                }
            }

            $response = $client->request('POST', $this->targetUrl, $options);

            $this->requestContent = $response->getBody()->getContents();

            if ($response->getStatusCode() == 200) {
                $this->wasSuccessful = true;
                return true;
            }
            else {
                $this->lastError = "Invalid response from server: " . $response->getStatusCode();
                return false;
            }
        }
        catch (\Exception | ClientException | GuzzleException $exception) {
            if ($this->echo) {
                echo sprintf("Error on guzzle : %s<br>\n", Str::limit(htmlentities($exception->getMessage()), 200));
            }

            $this->crawlException = $exception;
            $this->lastError = $exception->getMessage();

            return false;
        } finally {
            if ($this->echo) {
                echo sprintf("%s url requested. success: %s<br>\n", Str::limit($this->targetUrl, 100),
                    $this->wasSuccessful ? 'YES' : 'NO => ' . Str::limit($this->requestContent, 50));
            }
        }
    }

    protected function reset() {
        $this->wasSuccessful = false;
        $this->hasError = false;
        $this->lastError = '';
        $this->originalText = '';
        $this->translatedText = '';
    }

    public function wasSuccessful(): bool {
        return $this->wasSuccessful;
    }

    public function hasError(): bool {
        return $this->hasError;
    }

    public function getLastError(): string {
        return $this->lastError;
    }

    public function getOriginalText(): string {
        return $this->originalText;
    }

    public function getTranslatedText(): string {
        return $this->translatedText;
    }

    public function getRequestContent(): string {
        return $this->requestContent;
    }

    public function getName(): string {
        return $this->name;
    }

    public function getCrawlException(): ClientException|\Exception | GuzzleException {
        return $this->crawlException;
    }

}
