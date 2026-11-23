<?php

namespace App\Services;

/**
 * One admin switch that takes the company's own products (the `products`
 * catalogue VIPs resell under My Store) off sale everywhere, and hides the two
 * explainer boxes at the top of Selling Options by Package. Package selling
 * modes are not touched.
 *
 * Nothing is deleted: products and VIP store choices are kept, so switching it
 * off restores everything as it was.
 */
class CompanyProductsSwitch
{
    public const SETTING_KEY = 'company_products_hidden';

    public function __construct(private readonly SettingsService $settings) {}

    public function hidden(): bool
    {
        return $this->settings->get(self::SETTING_KEY) === '1';
    }

    public function setHidden(bool $hidden): void
    {
        $this->settings->set(self::SETTING_KEY, $hidden ? '1' : '0');
    }
}
