<?php

namespace RailAkhmadullin\ConfigurableProductTierPrice\Pricing\Price;

use Magento\Catalog\Pricing\Price\MinimalPriceCalculatorInterface;
use Magento\Catalog\Pricing\Price\TierPrice;
use Magento\ConfigurableProduct\Pricing\Price\ConfigurableOptionsProviderInterface;
use Magento\Framework\Pricing\SaleableInterface;
use Magento\Framework\Pricing\Adjustment\CalculatorInterface;
use Magento\Framework\Pricing\Amount\AmountInterface;

class MinimalTierPriceCalculator implements MinimalPriceCalculatorInterface
{
    /**
     * @var ConfigurableOptionsProviderInterface
     */
    private $configurableOptionsProvider;

    /**
     * @var CalculatorInterface
     */
    private $calculator;

    /**
     * @param ConfigurableOptionsProviderInterface $configurableOptionsProvider
     * @param CalculatorInterface $calculator
     */
    public function __construct(
        ConfigurableOptionsProviderInterface $configurableOptionsProvider,
        CalculatorInterface $calculator
    ) {
        $this->configurableOptionsProvider = $configurableOptionsProvider;
        $this->calculator = $calculator;
    }

    /**
     * @param SaleableInterface $saleableItem
     * @return float|null
     */
    public function getValue(SaleableInterface $saleableItem)
    {
        /** @var \Magento\Catalog\Model\Product $product */
        $product = $saleableItem;
        $tierPrices = [];
        foreach ($this->configurableOptionsProvider->getProducts($product) as $subProduct) {
            /** @var TierPrice $price */
            $price = $subProduct->getPriceInfo()->getPrice(TierPrice::PRICE_CODE);
            $tierPriceList = $price->getTierPriceList();

            $tierPrices = [];
            foreach ($tierPriceList as $tierPrice) {
                /** @var AmountInterface $price */
                $price = $tierPrice['price'];
                $tierPrices[] = $price->getValue();
            }
        }
        return $tierPrices ? min($tierPrices) : null;
    }

    /**
     * @param SaleableInterface $saleableItem
     * @return AmountInterface|null
     */
    public function getAmount(SaleableInterface $saleableItem)
    {
        $value = $this->getValue($saleableItem);
        return $value === null
            ? null
            : $this->calculator->getAmount($value, $saleableItem, 'tax');
    }
}
