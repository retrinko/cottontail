<?php


namespace Retrinko\CottonTail\Message;


interface MessageInterface extends OriginalMessageWrapperInterface
{
    const PROPERTY_CONTENT_TYPE   = 'content_type';
    const PROPERTY_CORRELATION_ID = 'correlation_id';
    const PROPERTY_REPLY_TO       = 'reply_to';
    const PROPERTY_EXPIRATION     = 'expiration';
    const PROPERTY_TIMESTAMP      = 'timestamp';
    const PROPERTY_USER_ID        = 'user_id';
    const PROPERTY_APP_ID         = 'app_id';
    const PROPERTY_TYPE           = 'type';

    const TYPE_RPC_REQUEST  = 'rpc-request';
    const TYPE_RPC_RESPONSE = 'rpc-response';
    const TYPE_BASIC        = 'basic';

    const CONTENT_TYPE_JSON       = 'application/json';
    const CONTENT_TYPE_PHP        = 'application/php/serialize/base64_encode';
    const CONTENT_TYPE_PLAIN_TEXT = 'text/plain';

    /**
     * @param string $name
     * @param string $default
     *
     * @return string
     */
    public function getProperty($name, $default = '');

    /**
     * @param string $name
     * @param string $value
     *
     * @return $this
     */
    public function setProperty($name, $value);

    /**
     * @return int
     */
    public function getTimestamp();

    /**
     * @return string
     */
    public function getType();

    /**
     * @return string
     */
    public function getContentType();

    /**
     * @return array
     */
    public function getRequiredProperties();

    /**
     * @return string
     */
    public function getBody();

    /**
     * @return array
     */
    public function getProperties();

    /**
     * @param string $name
     *
     * @return bool
     */
    public function hasProperty($name);

    /**
     * @return string
     */
    public function getAppId();

    /**
     * @return string
     */
    public function getCorrelationId();

    /**
     * @return int miliseconds
     */
    public function getExpiration();

    /**
     * @return string
     */
    public function getReplyTo();

    /**
     * @return string
     */
    public function getUserId();

    /**
     * @return mixed
     * @throws \Retrinko\Serializer\Exceptions\Exception
     */
    public function getPayload();


}