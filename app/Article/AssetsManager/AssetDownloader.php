<?php

namespace App\Article\AssetsManager;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\File;
use WebPConvert\Loggers\BufferLogger;
use WebPConvert\WebPConvert;

// download assets and convert image assets to webp
class AssetDownloader {

    protected bool $hasError = false;
    protected string $errorMessage = '';
    private bool $enableBalancer = false;

    public function __construct(protected string $url, protected string $storePath) {}

    public function doDownloadAndConvert() : bool {

        if($this->doDownload())
            return $this->doConvert();

        return false;
    }

    public function doDownload() : bool {
        // download
        $data = $this->getRemoteFileContentsGuzzle($this->url, $error, $wasSuccessful);

        if($wasSuccessful)
            file_put_contents($this->storePath, $data);
        else {
            $this->hasError = true;
            $this->errorMessage = 'Could not download asset: ' . $error;
            return false;
        }

        return true;
    }

    public function doConvert() : bool {
        // convert to webp
        if(self::convertToWebp($this->storePath, $error, true)){
            return true;
        }
        else {
            $this->hasError = true;
            $this->errorMessage = "Error on converting to webp: " .  $error;
            return false;
        }
    }

    protected function getRemoteFileContentsGuzzle($url, &$error = '', &$wasSuccessful = false) : mixed {

        try {
            $client = new Client();

            $options = [
                'timeout' => 50,
                'verify' => false,
                'headers' => [
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:102.0) Gecko/20100101 Firefox/102.0',
                ],
            ];

            if ($this->enableBalancer) {
                $blType = rand(1, 100) <= 100 ? 'web_proxy' : 'https_proxy';
                $balancer = balancer()->getNextBalancer($blType, 'AssetDownloader');
                $balancer->configGuzzleClient($url, $options);
            }

            $response = $client->get($url);

            $data = $response->getBody()->getContents();

            if ($response->getStatusCode() == 200) {
                $wasSuccessful = true;
                return $data;
            }
            else {
                $error = "Invalid response code from server: " . $response->getStatusCode();
                $wasSuccessful = false;
                return "";
            }
        }
        catch (\Exception | ClientException | GuzzleException $exception) {
            $error = $exception->getMessage();
            $wasSuccessful = false;
            return "";
        }
    }

    // download with curl
    public function downloadRemoteFile($url, $path, &$error = '') : int {
        $data = $this->getRemoteFileContentsCurl($url, $error);
        file_put_contents($path, $data);
        return File::size($path);
    }

    public static function getRemoteFileContentsCurl($url, &$error = '') {

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

        $headers = [];
        // this function is called by curl for each header received
        curl_setopt($ch, CURLOPT_HEADERFUNCTION,
            function($curl, $header) use (&$headers)
            {
                $len = strlen($header);
                $header = explode(':', $header, 2);
                if (count($header) < 2) // ignore invalid headers
                    return $len;

                $headers[strtolower(trim($header[0]))][] = trim($header[1]);

                return $len;
            }
        );

        $data = curl_exec($ch);
        curl_close($ch);

        if($data === false)
            $error = sprintf("curl_exec failed. errno: %s, %s",  curl_errno($ch) , curl_error($ch));

        return $data;
    }

    public static function convertToWebp(string &$filePath, &$error = "", $removeOriginalFile = false): bool {
        $orgSize = File::size($filePath);
        if($orgSize==0){
            $error = sprintf('poster size zero! %s', $filePath);
            logError($error, "Error");
            return false;
        }

        $pathInfo = pathinfo($filePath);


        // we just skip png files
        if(strtolower($pathInfo['extension']) == 'png')
            return true;

        $noExtFilePath = sprintf("%s/%s", $pathInfo['dirname'], $pathInfo['filename']);

        try {
            $start = microtime(true);
            $im = imagecreatefromjpeg($noExtFilePath . ".jpg");
            imagejpeg($im, $noExtFilePath . ".jpg", 70);
            clearstatcache();
            $jpgSize = filesize($noExtFilePath . ".jpg");
            $log = new BufferLogger();

            //webp convert
            $webpPath = $noExtFilePath . ".webp";
            WebPConvert::convert($noExtFilePath . ".jpg", $webpPath, ['encoding' => 'lossy'], $log);

            $webpSize = File::size($webpPath);

            if (isDebug()) {
                File::append(storage_path() . '/logs/webp_reports.log', now()->toDateTimeString() . "\n" . $log->getText() . "\n\n");
            }

            $decreasePercent = 100 - (int)(($webpSize / $orgSize) * 100);
            $duration = (int)((microtime(true) - $start) * 1000);
            $convertLog = sprintf("%s %s org: %s, jpg: %s, webp: %s, reduced: %s%%, in %s ms\n",
                now()->toDateTimeString(), $pathInfo['filename'],
                number_format_short($orgSize, false), number_format_short($jpgSize, false),
                number_format_short($webpSize, false), $decreasePercent, number_format($duration));
            File::append(storage_path() . '/logs/webp_reports.log', $convertLog);

            // success?
            if($success = $webpSize > 0) {
                // remove old file
                if($removeOriginalFile)
                    File::delete($filePath);

                // change reference store path to .webp
                $filePath = $webpPath;
            }

        } catch (Exception $e) {
            $error = $e->getMessage();
            logException($e, "convertToWebp");
            return false;
        }

        return $success;
    }

    public static function createFolder($path) : bool {
        try {
            if ( !File::isDirectory($path)) {
                File::makeDirectory($path, 0775, true, true);
            }
            return true;
        } catch (\Exception $ex) {
            logException($ex, "createFolder");
        }

        return false;

    }

    public function hasError() : bool {
        return $this->hasError;
    }

    public function getErrorMessage() : string {
        return $this->errorMessage;
    }

    public function getStorePath() : string {
        return $this->storePath;
    }



}
