<?php
/**
 * Intelive
 * @package Intelive Claro
 * @copyright Copyright (c) 2018 Intelive Metrics Srl
 * @author Adrian Roman
 */

namespace Intelive\Claro\Helper;

use Magento\Catalog\Model\Product;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Module\ModuleListInterface;
use Monolog\Logger;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const TYPE = 'SYNC';
    const TYPE_STOCK = 'STOCK';

    protected $config;

    protected $store;

    protected $context;

    protected $storeManager;

    protected $scopeConfig;

    /** @var \Intelive\Claro\Logger\Logger */
    protected $logger;

    protected $moduleList;

    /** @var \Intelive\Claro\Model\ClaroReportsSyncFactory */
    protected $syncFactory;

    /** @var \Intelive\Claro\Model\ResourceModel\ClaroReportsSync */
    protected $syncResourceModel;

    /**
     * Data constructor.
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     * @param ModuleListInterface $moduleList
     * @param \Intelive\Claro\Model\ClaroReportsSyncFactory $syncFactory
     * @param \Intelive\Claro\Model\ResourceModel\ClaroReportsSync $syncResourceModel
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        ModuleListInterface $moduleList,
        \Intelive\Claro\Model\ClaroReportsSyncFactory $syncFactory,
        \Intelive\Claro\Model\ResourceModel\ClaroReportsSync $syncResourceModel,
        \Intelive\Claro\Logger\Logger $logger
    )
    {
        parent::__construct($context);
        $this->context = $context;
        $this->storeManager = $storeManager;
        $this->scopeConfig = $context->getScopeConfig();
        $this->moduleList = $moduleList;
        $this->syncFactory = $syncFactory;
        $this->syncResourceModel = $syncResourceModel;
        $this->logger = $logger;
    }

    /**
     * @return string
     */
    public function getBaseDir()
    {
        return $this->context->getBaseDir() . '/app/code/Intelive/';
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        $this->config['enabled'] = $this->scopeConfig->getValue(
            'claroconfig/general/status',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        $this->config['license_key'] = $this->scopeConfig->getValue(
            'claroconfig/general/license_serial_number',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        $this->config['api_key'] = $this->scopeConfig->getValue(
            'claroconfig/general/api_key',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        $this->config['api_secret'] = $this->scopeConfig->getValue(
            'claroconfig/general/api_secret',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        $this->config['debug_status'] = $this->scopeConfig->getValue(
            'claroconfig/advanced/debug_status',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        return $this->config;
    }

    /**
     * @return mixed
     */
    public function getVersion()
    {
        return $this->moduleList->getOne('Intelive_Claro')['setup_version'];
    }

    /**
     * @param $msg
     * @param $type
     */
    public function log($msg, $type = Logger::INFO)
    {
        $this->getConfig();
        // check if debug is active and overrides magentos' debug status
        if ($this->config['debug_status']) {
            switch ($type) {
                case Logger::INFO:
                    $this->logger->info($msg);
                    break;
                case Logger::CRITICAL:
                    $this->logger->crit($msg);
                    break;
            }
        }

        return;
    }

    /**
     * @return \Magento\Store\Api\Data\StoreInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getStore()
    {
        if ($this->store == null) {
            $this->store = $this->storeManager->getStore();
        }

        return $this->store;
    }

    /**
     * @return mixed
     */
    public function getLicenseKey()
    {
        $licenseKey = $this->getConfig()['license_key'];
        return $licenseKey;
    }

    /**
     * @return bool
     */
    public function prepareDefaultResult()
    {
        // If we don't have the token, check the api configuration
        if (!isset($_SERVER['HTTP_X_CLARO_TOKEN'])) {
            $this->getConfig();

            if (
                $this->config['license_key'] != '' &&
                $this->config['api_key'] != '' &&
                $this->config['api_secret'] != ''
            ) {
                return [
                    'status' => \Magento\Framework\App\Response\Http::STATUS_CODE_200,
                    'data' => true
                ];
            }

            return [
                'status' => \Magento\Framework\App\Response\Http::STATUS_CODE_200,
                'data' => false
            ];
        } else {
            return [
                'status' => \Magento\Framework\App\Response\Http::STATUS_CODE_401,
                'data' => ['error' => 'Invalid security token or module disabled']
            ];
        }


    }

    /**
     * @param $payload
     * @param $entity
     * @param $type
     * @return array
     */
    public function prepareResult($payload, $entity, $type = '')
    {
        $this->getConfig();

        $responseIsEncoded = false;
        $responseIsCompressed = false;

        $data = $payload['data'];

        // Encode and compress the data only if we have it
        if (!empty($data)) {
            $encoded = $this->encode($payload);

            if (is_string($encoded)) {
                $responseIsEncoded = true;
                $data = $encoded;
            }

            $compressed = $this->compress($encoded);
            if ($compressed) {
                $responseIsCompressed = true;
                $data = $compressed;
            }
        }
        $callType = $type != '' ? $type : self::TYPE;
        $lastId = isset($payload['last_id']) ? $payload['last_id'] : 0;
        $returnedIds = isset($payload['returned_ids']) ? implode(', ', $payload['returned_ids']) : 0;
        $url = \Magento\Framework\App\ObjectManager::getInstance()
            ->get('Magento\Framework\UrlInterface');
        ;

        $this->log(
            "calledUrl = " . $url->getCurrentUrl() . "; returnedIds = $returnedIds; isEncoded = $responseIsEncoded; isCompressed = $responseIsCompressed; license_key = " . $this->config['license_key'] . "; lastId = $lastId; type = $callType; entity = $entity"
        );

        return [
            'isEncoded' => $responseIsEncoded,
            'isCompressed' => $responseIsCompressed,
            'data' => $data,
            'license_key' => $this->config['license_key'],
            'entity' => $entity,
            'type' => $callType,
            'lastId' => $lastId
        ];
    }

    /**
     * @param $data
     * @return string
     */
    protected function compress($data)
    {
        if (
            extension_loaded('zlib') &&
            function_exists('gzcompress') &&
            function_exists('base64_encode')
        ) {
            return base64_encode(gzcompress(serialize(($data))));
        } else {
            $this->log('Extensions zlib or gzcompress or base64_encode do not exist', Logger::CRITICAL);
        }

        return false;
    }

    /**
     * @param $data
     * @return mixed
     */
    protected function deCompress($data)
    {
        return unserialize(gzuncompress(base64_decode($data)));
    }

    /**
     * @param $payload
     * @return string
     */
    protected function encode($payload)
    {
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
        $encrypted = openssl_encrypt(json_encode($payload), 'aes-256-cbc', $this->config['api_secret'], 0, $iv);

        return base64_encode($encrypted . '::' . $iv);
    }

    /**
     * @param $payload
     * @return string
     */
    protected function decode($payload)
    {
        list($encryptedData, $iv) = explode('::', base64_decode($payload), 2);
        return json_decode(openssl_decrypt($encryptedData, 'aes-256-cbc', $this->config['api_secret'], 0, $iv));
    }
}
