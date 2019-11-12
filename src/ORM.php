<?php
/**
 * This file is part of UnisenderORM.
 *
 * 2019 (c) Ramil Aliyakberov (RAMe0) <r@me0.biz>
 *
 * For the full copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 */


namespace rame0\UniORM;


use rame0\UniORM\Classes\Exceptions\ORMException;
use rame0\UniORM\Classes\RequestException;

/**
 * Class ORM
 * @package rame0\UniORM
 *
 * @method object exclude(array $params) The method excludes the contact’s email or phone number from one or several lists.
 * @method object unsubscribe(array $params) The method unsubscribes the contact email or phone number from one or several
 * lists.
 * @method object importContacts(array $params) It is a method of bulk import of contacts.
 * @method object getTotalContactsCount(array $params) The method returns the contacts database size by the user login.
 * @method object getContactCount(array $params) Get contact count in list.
 * @method object createEmailMessage(array $params) It is a method to create an email without sending it.
 * @method object createSmsMessage(array $params) It is a method to create SMS messages without sending them.
 * @method object createCampaign(array $params) This method is used to schedule or immediately start sending email
 * or SMS messages.
 * @method object getActualMessageVersion(array $params) The method returns the id of the relevant version of
 * the specified letter.
 * @method object checkSms(array $params) It returns a string — the SMS sending status.
 * @method object sendTestEmail(array $params) It is a method to send a test email message.
 * @method object checkEmail(array $params) The method allows you to check the delivery status of emails sent
 * using the sendEmail method.
 * @method object updateOptInEmail(array $params) Each campaign list has the attached text of the invitation
 * to subscribe and confirm the email that is sent to the contact to confirm the campaign. The text of the letter
 * can be changed using the updateOptInEmail method.
 * @method object getWebVersion(array $params) It is a method to get the link to the web version of the letter.
 * @method object deleteMessage(array $params) It is a method to delete a message.
 * @method object createEmailTemplate(array $params) It is a method to create an email template for a mass campaign.
 * @method object updateEmailTemplate(array $params) It is a method to edit email templates for a mass campaign.
 * @method object deleteTemplate(array $params) It is a method to delete a template.
 * @method object getTemplate(array $params) The method returns information about the specified template.
 * @method object getTemplates(array $params = []) This method is used to get the list of templates created
 * both through the UniSender personal account and through the API.
 * @method object listTemplates(array $params = []) This method is used to get the list of templates created both
 * through the UniSender personal account and through the API.
 * @method object getCampaignCommonStats(array $params) The method returns statistics similar to «Campaigns».
 * @method object getVisitedLinks(array $params) Get a report on the links visited by users in the specified email campaign.
 * @method object getCampaigns(array $params = array()) It is a method to get the list of all available campaigns.
 * @method object getCampaignStatus(array $params) Find out the status of the campaign created using the createCampaign method.
 * @method object getMessages(array $params = []) This method is used to get the list of letters created both
 * through the UniSender personal account and through the API.
 * @method object getMessage(array $params) It is a method to get information about SMS or email message.
 * @method object listMessages(array $params) This method is used to get the list of messages created both through
 * the UniSender personal account and through the API. The method works like getMessages, the difference of
 * listMessages is that the letter body and attachments are not returned, while the user login is returned. To get the
 * body and attachments, use the getMessage method.
 * @method object getFields() It is a method to get the list of user fields.
 * @method object createField(array $params) It is a method to create a new user field, the value of which can be set for
 * each recipient, and then it can be substituted in the letter.
 * @method object updateField(array $params) It is a method to change user field parameters.
 * @method object deleteField(array $params) It is a method to delete a user field.
 * @method object getTags() It is a method to get list of all tags.
 * @method object deleteTag(array $params) It is a method to delete a user tag.
 * @method object isContactInLists(array $params) Checks whether contact is in list.
 * @method object getContactFieldValues(array $params) Get additional fields values for a contact.
 *
 * @method object sendSms(array $params) It is a method for easy sending the one SMS to one or several recipients.
 * @method object sendEmail(array $params) It is a method to send a single individual email without personalization and
 * with limited possibilities to obtain statistics. To send transactional letters, use the
 * UniOne — the transactional letter service from UniSender. https://www.unisender.com/en/features/unione/
 */
class ORM
{
    // Orm Instance
    private static $instance = null;

    private static $API_Key;
    private static $encoding = 'UTF-8';
    private static $isUTF8 = true;
    private static $retryCount = 4;
    private static $timeout = null;
    private static $compression = false;
    private static $platform = null;
    private static $apiHost = '';
    private static $throwOnError = false;

    /**
     * @var array Request error log
     */
    private static $request_error_log = [];

    /**
     * @var array Re
     */
    private static $request_warn_log = [];

    /**
     * Configure ORM
     * @param string $API_Key Your Unisender API key
     * @param string $lang Api language. All text responses will be returned in this language
     * @param string $encoding Specify encoding. UTF-8 is default
     * @param int $retryCount Retry count. Default is 4
     * @param int $timeout Connection timeout in seconds. Default 1. Use 0 to wait indefinitely.
     * @param bool $compression Use compression. Check that ext-bz2 is installed if you want to use compression
     * @param string|null $platform Your platform name
     */
    public static function config(string $API_Key, string $lang = 'ru', string $encoding = 'UTF-8', int $retryCount = 4, int $timeout = 1, bool $compression = false, string $platform = null): void
    {
        self::$API_Key = $API_Key;
        self::$encoding = $encoding;
        self::$retryCount = $retryCount;
        self::$timeout = $timeout;
        self::$compression = $compression;
        self::$platform = $platform;

        if (strtoupper(self::$encoding) !== 'UTF-8') {
            self::$isUTF8 = false;
        }

        self::$apiHost = "https://api.unisender.com/$lang/api/";
    }


    /**
     * Get Unisender API instance
     * @return ORM
     */
    public static function getInstance(): ORM
    {
        if (empty(self::$instance)) {
            self::$instance = new static();
        }

        return self::$instance;
    }

    /**
     * Set to TRUE if you want to throw if request error occurred instead of logging it
     * @param bool $throwOnError
     */
    public static function setThrowOnError(bool $throwOnError): void
    {
        self::$throwOnError = $throwOnError;
    }

    /**
     * @return array
     */
    public static function getRequestErrorLog(): array
    {
        return self::$request_error_log;
    }

    /**
     * @return array
     */
    public static function getRequestWarnLog(): array
    {
        return self::$request_warn_log;
    }

    /**
     * It is a method to get the list of all available campaign lists.
     * @return array|null
     * @throws RequestException
     */
    public function getLists(): ?array
    {
        return $this->makeRequest('getLists');
    }

    /**
     * It is a method to create a new contact list.
     * @param string $title
     * @param string $before_subscribe_url
     * @param string $after_subscribe_url
     * @return int
     * @throws ORMException
     * @throws RequestException
     */
    public function createList(string $title, string $before_subscribe_url = null, string $after_subscribe_url = null): int
    {
        if (empty($title)) throw new ORMException('Title have to be set');

        $params = array_filter(compact('title', 'before_subscribe_url', 'after_subscribe_url'));

        $result = $this->makeRequest("createList", $params);

        if (!empty($result) && !empty($result['id'])) {
            return (int)$result['id'];
        } else {
            throw new ORMException('List creation failed');
        }
    }

    /**
     * It is a method to change campaign list properties.
     * @param int $list_id
     * @param string $title
     * @param string $before_subscribe_url
     * @param string $after_subscribe_url
     * @return bool
     * @throws ORMException
     * @throws RequestException
     */
    public function updateList(int $list_id, string $title, string $before_subscribe_url = null, string $after_subscribe_url = null): bool
    {
        if ($list_id < 1) throw new ORMException('ID of list have to be set');
        if (empty($title)) throw new ORMException('Title have to be set');

        $params = array_filter(compact('list_id', 'title', 'before_subscribe_url', 'after_subscribe_url'));

        $this->makeRequest('updateList', $params);

        return true;
    }

    /**
     * It is a method to delete a list.
     * @param int $list_id
     * @return bool
     * @throws ORMException
     * @throws RequestException
     */
    public function deleteList(int $list_id): bool
    {
        if ($list_id < 1) throw new ORMException('ID of list have to be set');

        $params = ['list_id' => $list_id];
        $this->makeRequest('deleteList', $params);
        return true;
    }


    /**
     * @param string $methodName
     * @param array $params
     *
     * @return array|null Returns response result data of NULL otherwise
     * @throws RequestException if you set ORM to throw
     */
    private function makeRequest(string $methodName, array $params = []): ?array
    {
        if (self::$platform !== '') {
            $params['platform'] = self::$platform;
        }
        if (!self::$isUTF8) {
            if (function_exists('iconv')) {
                array_walk_recursive($params, [$this, 'iconv']);
            } elseif (function_exists('mb_convert_encoding')) {
                array_walk_recursive($params, [$this, 'mb_convert_encoding']);
            }
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, self::$timeout);
        curl_setopt($ch, CURLOPT_POST, true);
        $url = self::$apiHost . $methodName . '?format=json';

        $params['api_key'] = self::$API_Key;

        if (self::$compression && extension_loaded('bz2')) {
            $url .= '&request_compression=bzip2';
            $content = bzcompress(http_build_query($params));
        } else {
            $content = http_build_query($params);
        }


        curl_setopt($ch, CURLOPT_POSTFIELDS, $content);
        curl_setopt($ch, CURLOPT_URL, $url);

        $retryCount = 0;
        self::clearLogs();
        do {
            $errno = curl_errno($ch);
            $response = trim(curl_exec($ch));
            $response_info = curl_getinfo($ch);
            if ($errno !== 0) {
                self::logRequestError($methodName . ': ' . curl_error($ch));
            } else {
                curl_close($ch);

                if (strpos($response_info['content_type'], 'application/json') !== false) {
                    $response_body = json_decode($response, true);
                } else {
                    self::logRequestError('response_info', var_export($response_info, true));
                    return null;
                }

                if ($response_info['http_code'] < 200 || $response_info['http_code'] >= 300) {
                    $data_as_string = var_export($params, true);
                    $response_as_string = var_export($response_info, true);
                    self::logRequestError("Error request method '{$methodName}'\n\nURL:\n{$url}\n\nData:\n'{$data_as_string}'\n\nResponse Info:\n{$response_as_string}'");
                    return null;
                }

                if (!empty($response_body['error'])) {
                    self::logRequestError($methodName, $response_body['code'] . ': ' . $response_body['error']);
                    return null;
                }
                if (!empty($response_body['warnings'])) {
                    foreach ($response_body['warnings'] as $warning) {
                        self::logRequestWarning($warning['warning']);
                    }
                }

                return $response_body['result'];
            }
            ++$retryCount;
        } while ($retryCount < self::$retryCount);

        return null;
    }

    /**
     * Clear request logs
     */
    private static function clearLogs(): void
    {
        self::$request_error_log = [];
        self::$request_warn_log = [];
    }

    /**
     * Add message to error log array
     * @param string $keyOrVal - will be used as message if $value not set, as key otherwise
     * @param string|null $value - error message
     * @throws RequestException if you set ORM to throw
     */
    private static function logRequestError(string $keyOrVal, string $value = null): void
    {
        if (empty($value)) {
            if (self::$throwOnError) {
                throw new RequestException($keyOrVal);
            }
            self::$request_error_log[] = $keyOrVal;
        } else {
            if (self::$throwOnError) {
                throw new RequestException($keyOrVal . ': ' . $value);
            }
            self::$request_error_log[$keyOrVal] = $value;
        }
    }

    /**
     * Add message to warnings log array
     * @param string $message - error message
     */
    private static function logRequestWarning(string $message): void
    {
        self::$request_warn_log[] = $message;
    }
}