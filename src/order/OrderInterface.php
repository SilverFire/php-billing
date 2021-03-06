<?php
/**
 * PHP Billing Library
 *
 * @link      https://github.com/hiqdev/php-billing
 * @package   php-billing
 * @license   BSD-3-Clause
 * @copyright Copyright (c) 2017-2018, HiQDev (http://hiqdev.com/)
 */

namespace hiqdev\php\billing\order;

use hiqdev\php\billing\action\ActionInterface;

/**
 * @author Andrii Vasyliev <sol@hiqdev.com>
 */
interface OrderInterface extends \JsonSerializable
{
    public function getId();

    public function getCustomer();

    /**
     * Returns actions.
     * @return ActionInterface[] array: actionKey => action
     */
    public function getActions();
}
