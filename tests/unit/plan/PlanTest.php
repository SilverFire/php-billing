<?php
/**
 * PHP Billing Library
 *
 * @link      https://github.com/hiqdev/php-billing
 * @package   php-billing
 * @license   BSD-3-Clause
 * @copyright Copyright (c) 2017-2018, HiQDev (http://hiqdev.com/)
 */

namespace hiqdev\php\billing\tests\unit\plan;

use DateTimeImmutable;
use hiqdev\php\billing\action\Action;
use hiqdev\php\billing\action\ActionInterface;
use hiqdev\php\billing\charge\Charge;
use hiqdev\php\billing\charge\Generalizer;
use hiqdev\php\billing\order\Calculator;
use hiqdev\php\billing\plan\Plan;
use hiqdev\php\billing\plan\PlanInterface;
use hiqdev\php\units\Quantity;
use hiqdev\php\units\Unit;
use Money\Money;

class PlanTest extends \PHPUnit\Framework\TestCase
{
    /** @var Plan|PlanInterface */
    protected $plan;
    /** @var DateTimeImmutable */
    protected $time;
    /** @var Calculator */
    protected $calculator;

    protected function setUp()
    {
        $this->plan = CertificatePlan::get();
        $this->time = new DateTimeImmutable('now');
        $this->calculator = new Calculator(new Generalizer(), null, null);
    }

    public function testCalculateCharges()
    {
        foreach ($this->plan->types as $type) {
            foreach ($this->plan->targets as $target) {
                foreach ([1, 2, 3] as $years) {
                    $usage = Quantity::month($years * 12);
                    $action = new Action(null, $type, $target, $usage, $this->plan->customer, $this->time);
                    $charges = $this->calculator->calculatePlan($this->plan, $action);
                    $this->checkCharges($action, $charges);
                }
            }
        }
    }

    /**
     * @param ActionInterface|Action $action
     * @param array $charges
     */
    public function checkCharges(ActionInterface $action, array $charges)
    {
        $this->assertInternalType('array', $charges);
        $this->assertCount(1, $charges);
        $charge = reset($charges);
        $sum = Money::USD($this->plan->getRawPrice($action));
        $usage = $action->getQuantity()->convert(Unit::year());
        $this->assertInstanceOf(Charge::class, $charge);
        $this->assertSame($action, $charge->getAction());
        $this->assertSame($action->getType(), $charge->getType());
        $this->assertTrue($usage->equals($charge->getUsage()));
        $this->assertTrue($sum->equals($charge->getSum()));
    }
}
