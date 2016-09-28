<?php

namespace insign\LaravelDecaptcha;

use Illuminate\Contracts\Foundation\Application;

/**
 * Class to recognize captcha
 *
 * Class Captcha
 */
class LaravelDecaptcha implements LaravelDecaptchaInterface
{
   /**
    * Service which will load the captcha
    * @var string
    */
   public $domain = "2captcha.com";
   /**
    * The path to the temporary storage captcha folder (if necessary will transfer link to captcha)
    * @var string
    */
   public $pathTmp = 'captcha';
   /**
    * Your API key
    * @var string
    */
   private $apiKey;
   /**
    * false(commenting OFF), true(commenting ON)
    * @var bool
    */
   public $isVerbose = TRUE;
   /**
    * Timeout to recheck result of captcha
    * @var int
    */
   public $requestTimeout = 5;
   /**
    * Maximum response time
    * @var int
    */
   public $maxTimeout = 120;
   /**
    * 0 OR 1 - The captcha has two or more words
    * @var int
    */
   public $isPhrase = 0;
   /**
    * 0 OR 1 - Register response is important
    * @var int
    */
   public $isRegSense = 0;
   /**
    * 0 OR 1 OR 2 OR 3 - 0 = option is not enabled (default) 1 = captcha is only numbers 2 = captcha is only numbers 3 = Captcha consists either only of numbers or only of letters.
    * @var int
    */
   public $isNumeric = 0;
   /**
    * 0 if not restricted, otherwise the response indicates the minimum length
    * @var int
    */
   public $minLen = 0;
   /**
    * 0 if not restricted, otherwise the response indicates the maximum length
    * @var int
    */
   public $maxLen = 0;
   /**
    * 0 OR 1 OR 2 0 = option is not enabled (default) 1 = captcha in cyrillic 2 = captcha in latin
    * @var int
    */
   public $language = 0;
   /**
    * error
    * @var null|string
    */
   private $error = NULL;
   /**
    * result
    * @var null|string
    */
   private $result = NULL;

   /**
    * errors descriptions
    * @var array
    */
   private $errors = [
       'ERROR_NO_SLOT_AVAILABLE'        => 'No free workers at the moment, try again later or promote your maximum bid here',
       'ERROR_ZERO_CAPTCHA_FILESIZE'    => 'The size of captcha that you load is less than 100 bytes',
       'ERROR_TOO_BIG_CAPTCHA_FILESIZE' => 'Your captcha is larger than 100 kilobytes',
       'ERROR_ZERO_BALANCE'             => 'zero or negative balance',
       'ERROR_IP_NOT_ALLOWED'           => 'request from this IP address with the current key is rejected. Please see the section on access control IP ',
       'ERROR_CAPTCHA_UNSOLVABLE'       => 'Could not solve captcha',
       'ERROR_BAD_DUPLICATES'           => 'The function 100% recognition did not work, and because of attempts to limit',
       'ERROR_NO_SUCH_METHOD'           => 'You need to send the method parameter in your request to the API, refer to the documentation',
       'ERROR_IMAGE_TYPE_NOT_SUPPORTED' => 'Unable to determine the type of captcha file, accepted only a JPG, GIF, PNG',
       'ERROR_KEY_DOES_NOT_EXIST'       => 'We used a non-existent key',
       'ERROR_WRONG_USER_KEY'           => 'Invalid format setting key, must be 32 characters',
       'ERROR_WRONG_ID_FORMAT'          => 'Invalid format ID captcha. ID must contain only digits',
       'ERROR_WRONG_FILE_EXTENSION'     => 'Your captcha is incorrect extension, allowed extensions jpg, jpeg, gif, png',
   ];

   private $captcha_id;

   /**
    * Class constructor.
    *
    * @param \Illuminate\Contracts\Foundation\Application $app The Laravel Application.
    */
   public function __construct(Application $app)
   {
      $this->app = $app;

      $this->apiKey = config('decaptcha.key');
      $this->domain = config('decaptcha.domain', $this->domain);
      $this->pathTmp = config('decaptcha.tmp', $this->pathTmp);
   }

   public function setApiKey($apiKey)
   {
      if (is_callable($apiKey)) {
         $this->apiKey = $apiKey();
      } else {
         $this->apiKey = $apiKey;
      }
   }

   /**
    * Send captcha image
    * @param string $filename The path to a file or a link to it
    * @return bool
    */
   public function run($filename)
   {
      $this->result = NULL;
      $this->error  = NULL;
      try {
         if (strpos($filename, 'http://') !== FALSE || strpos($filename, 'https://') !== FALSE) {
            $current = file_get_contents($filename);
            if ($current) {
               $path = storage_path($this->pathTmp) . '/' . str_random();
               if (file_put_contents($path, $current)) {
                  $filename = $path;
               } else {
                  throw new \Exception("No write access to the file");
               }
            } else {
               throw new \Exception("File {$filename} is not loaded");
            }
         } elseif (!file_exists($filename)) {
            throw new \Exception("File {$filename} can not be found");
         }
         $postData = [
             'method'   => 'post',
             'key'      => $this->apiKey,
             'file'     => (version_compare(PHP_VERSION, '5.5.0') >= 0) ? new \CURLFile($filename) : '@' . $filename,
             'phrase'   => $this->isPhrase,
             'regsense' => $this->isRegSense,
             'numeric'  => $this->isNumeric,
             'min_len'  => $this->minLen,
             'max_len'  => $this->maxLen,
             'language' => $this->language,
             'soft_id'  => 882,
         ];
         $ch       = curl_init();
         curl_setopt($ch, CURLOPT_URL, "http://{$this->domain}/in.php");
         if (version_compare(PHP_VERSION, '5.5.0') >= 0 && version_compare(PHP_VERSION, '7.0') < 0) {
            curl_setopt($ch, CURLOPT_SAFE_UPLOAD, FALSE);
         }
         curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
         curl_setopt($ch, CURLOPT_TIMEOUT, 60);
         curl_setopt($ch, CURLOPT_POST, 1);
         curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
         $result = curl_exec($ch);
         if (curl_errno($ch)) {
            throw new \Exception("CURL returned an error: " . curl_error($ch));
         }
         curl_close($ch);
         $this->setError($result);
         list(, $this->captcha_id) = explode("|", $result);
         $waitTime = 0;
         sleep($this->requestTimeout);
         while (TRUE) {
            $result = file_get_contents("http://{$this->domain}/res.php?key={$this->apiKey}&action=get&id={$this->captcha_id}");
            $this->setError($result);
            if ($result == "CAPCHA_NOT_READY") {
               $waitTime += $this->requestTimeout;
               if ($waitTime > $this->maxTimeout) {
                  break;
               }
               sleep($this->requestTimeout);
            } else {
               $ex = explode('|', $result);
               if (trim($ex[ 0 ]) == 'OK') {
                  $this->result = trim($ex[ 1 ]);

                  return TRUE;
               }
            }
         }
         throw new \Exception('Time limit exceeded');
      } catch (\Exception $e) {
         $this->error = $e->getMessage();

         return FALSE;
      }
   }

   /**
    * Invalid recognized
    */
   public function notTrue()
   {
      file_get_contents("http://{$this->domain}/res.php?key={$this->apiKey}&action=reportbad&id={$this->captcha_id}");
   }

   /**
    * Result
    * @return null|string
    */
   public function result()
   {
      return $this->result;
   }

   /**
    * Error
    * @return null|string
    */
   public function error()
   {
      return $this->error;
   }

   /**
    * Check out whether there was a error
    * @param $error
    * @throws \Exception
    */
   private function setError($error)
   {
      if (strpos($error, 'ERROR') !== FALSE) {
         if (array_key_exists($error, $this->errors)) {
            throw new \Exception($this->errors[ $error ]);
         } else {
            throw new \Exception($error);
         }
      }
   }
}
