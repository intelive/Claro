<?php
/**
 * Intelive
 * @package Intelive Claro
 * @copyright Copyright (c) 2018 Intelive Metrics Srl
 * @author Adrian Roman
 */

namespace Intelive\Claro\Controller\Module;

use Magento\Catalog\Model\Product;
use Magento\Framework\App\Action\Context;
use Intelive\Claro\Helper\Data;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\Result\JsonFactory;

class ProductImages extends \Intelive\Claro\Controller\Module
{
    const WIDTH = '100';

    /** @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory */
    protected $productFactory;

    /** @var \Magento\Catalog\Helper\Image */
    protected $imageHelper;

    /** @var JsonFactory */
    protected $resultJsonFactory;

    /** @var ResultFactory */
    protected $resultFactory;

    /** @var \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable */
    protected $catalogProductTypeConfigurable;

    public function __construct(
        Context $context,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productFactory,
        \Magento\Catalog\Helper\Image $imageHelper,
        JsonFactory $resultJsonFactory,
        \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable $catalogProductTypeConfigurable,
        Data $helper
    )
    {
        parent::__construct($context);

        $this->helper = $helper;
        $this->productFactory = $productFactory;
        $this->imageHelper = $imageHelper;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->resultFactory = $context->getResultFactory();
        $this->catalogProductTypeConfigurable = $catalogProductTypeConfigurable;
    }

    /**
     * If the image is successfully loaded a raw response will be returned
     * If there is an error, a json response containing the error will be returned
     *
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Json|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $productId = $this->getRequest()->getParam('id');

        if (is_null($productId)) {
            $responseMessage = 'Missing product ID';

            return $this->generateErrorResponse($responseMessage);
        }
        $width = $this->getRequest()->getParam('w');
        if (!$width) {
            $width = self::WIDTH;
        }

        try {
            /** @var Product $product */
            $product = $this->loadProductById($productId);

            if (!$product->getSku()) {
                $responseMessage = 'Cannot load product';

                return $this->generateErrorResponse($responseMessage);
            }

            //if this sku doesn't have an image and is type simple, we can check his parent for image
            if ((!$product->getImage() || $product->getImage() == 'no_selection') && $product->getTypeId() == 'simple') {
                $parentIdByChild = $this->catalogProductTypeConfigurable->getParentIdsByChild($productId);

                if (isset($parentIdByChild[0])) {
                    $product = $this->loadProductById($parentIdByChild[0]);
                    if (!$product->getSku()) {
                        $responseMessage = 'Cannot load parent product';

                        return $this->generateErrorResponse($responseMessage);
                    }
                }
            }
            $path = $this->imageHelper->init($product, 'small_image', ['type' => 'small_image'])->keepAspectRatio(true)->resize($width, $width)->getUrl();
            $type = $this->getImageType($path);

            if ($type != 'unknown') {
                $resultRaw = $this->resultFactory->create(ResultFactory::TYPE_RAW);

                $resultRaw->setHeader('Content-Type:', $type)->setContents(readfile($path));

                return $resultRaw;
            }

            return $this->generateErrorResponse('Unknown image found.');
        } catch (\Exception $ex) {
            $this->helper->log($ex->getMessage(), Logger::CRITICAL);
            $responseMessage = 'An error occured, please try again later.';

            return $this->generateErrorResponse($responseMessage);
        }
    }

    /**
     * @param $productId
     * @return Product
     */
    protected function loadProductById($productId)
    {
        $collection = $this->productFactory->create()
            ->addAttributeToSelect('*')
            ->addAttributeToFilter('entity_id', $productId);

        return $collection->getFirstItem();
    }

    /**
     * @param $path
     * @return string
     */
    protected function getImageType($path)
    {
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        switch ($ext) {
            case 'gif':
                $type = 'image/gif';
                break;
            case 'jpg':
            case 'jpeg':
                $type = 'image/jpeg';
                break;
            case 'png':
                $type = 'image/png';
                break;
            default:
                $type = 'unknown';
                break;
        }

        return $type;
    }

    /**
     * @param $message
     * @return \Magento\Framework\Controller\Result\Json
     */
    protected function generateErrorResponse($message)
    {
        $resultJson = $this->resultJsonFactory->create();

        $resultJson->setHttpResponseCode('400');
        $resultJson->setData(
            [
                'error' => true,
                'message' => $message
            ]
        );

        return $resultJson;
    }
}
