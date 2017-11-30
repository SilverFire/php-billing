<?php
/**
 * PHP Billing Library
 *
 * @link      https://github.com/hiqdev/php-billing
 * @package   php-billing
 * @license   BSD-3-Clause
 * @copyright Copyright (c) 2017, HiQDev (http://hiqdev.com/)
 */

namespace hiqdev\php\billing\tests\unit\order;

use hiqdev\php\billing\action\SimpleAction;
use hiqdev\php\billing\charge\Aggregator;
use hiqdev\php\billing\order\Calculator;
use hiqdev\php\billing\order\Order;
use hiqdev\php\billing\tests\unit\plan\PlanTest;
use hiqdev\php\billing\tests\unit\plan\SimplePlanRepository;
use hiqdev\php\units\Quantity;
use Money\Money;

class AggregatorTest extends PlanTest
{
    protected function setUp()
    {
        parent::setUp();
        $this->repository = new SimplePlanRepository($this->plan);
        $this->calculator = new Calculator($this->repository);
        $this->aggregator = new Aggregator();
        $actions = [];
        foreach ($this->plan->types as $type) {
            foreach ($this->plan->targets as $target) {
                foreach ([1, 2, 3] as $years) {
                    $actions[] = new SimpleAction(null, $type, $target, Quantity::year($years), $this->plan->customer);
                }
            }
        }
        $this->order = new Order(null, $this->plan->customer, $actions);
    }

    public function testCalculateCharges()
    {
        $charges = $this->calculator->calculateCharges($this->order);
        $bills = $this->aggregator->aggregateCharges($charges);
        $this->assertSame(4, count($bills));
        foreach ($bills as $bill) {
            $prices = $this->plan->getRawPrices($bill->getType(), $bill->getTarget());
            $sum = Money::USD(array_sum($prices));
            $this->assertTrue($sum->equals($bill->getSum()));
            $this->assertEquals(6, $bill->getQuantity()->getQuantity());
            $this->assertEquals(3, count($bill->getCharges()));
            foreach ($bill->getCharges() as $charge) {
                $this->assertTrue($bill->getType()->equals($charge->getPrice()->getType()));
                $this->assertTrue($bill->getTarget()->equals($charge->getTarget()));
            }
        }
    }
}
