<?php
/**
 * PHP Billing Library
 *
 * @link      https://github.com/hiqdev/php-billing
 * @package   php-billing
 * @license   BSD-3-Clause
 * @copyright Copyright (c) 2017-2018, HiQDev (http://hiqdev.com/)
 */

namespace hiqdev\php\billing\bill;

use DateTimeImmutable;
use hiqdev\php\billing\charge\ChargeInterface;
use hiqdev\php\billing\customer\CustomerInterface;
use hiqdev\php\billing\plan\PlanInterface;
use hiqdev\php\billing\target\TargetInterface;
use hiqdev\php\billing\type\TypeInterface;
use hiqdev\php\units\QuantityInterface;
use Money\Money;

/**
 * Bill.
 *
 * @author Andrii Vasyliev <sol@hiqdev.com>
 */
class Bill implements BillInterface
{
    /** @var int|string */
    protected $id;

    /** @var TypeInterface */
    protected $type;

    /** @var DateTimeImmutable */
    protected $time;

    /** @var Money */
    protected $sum;

    /** @var QuantityInterface */
    protected $quantity;

    /** @var CustomerInterface */
    protected $customer;

    /** @var TargetInterface */
    protected $target;

    /** @var PlanInterface */
    protected $plan;

    /** @var ChargeInterface[] */
    protected $charges = [];

    /** @var BillState */
    protected $state;

    /** @var string */
    protected $comment;

    public function __construct(
                            $id,
        TypeInterface       $type,
        DateTimeImmutable   $time,
        Money               $sum,
        QuantityInterface   $quantity,
        CustomerInterface   $customer,
        TargetInterface     $target = null,
        PlanInterface       $plan = null,
        array               $charges = [],
        BillState           $state = null
    ) {
        $this->id           = $id;
        $this->type         = $type;
        $this->time         = $time;
        $this->sum          = $sum;
        $this->quantity     = $quantity;
        $this->customer     = $customer;
        $this->target       = $target;
        $this->plan         = $plan;
        $this->charges      = $charges;
        $this->state        = $state;
    }

    /**
     * Provides unique string.
     * Can be used to compare or aggregate bills.
     */
    public function getUniqueString(): string
    {
        $parts = [
            'currency'  => $this->sum->getCurrency()->getCode(),
            'buyer'     => $this->customer->getUniqueId(),
            'target'    => $this->target ? $this->target->getUniqueId() : null,
            'type'      => $this->type->getUniqueId(),
            'time'      => $this->time->format('c'),
        ];

        return implode('-', $parts);
    }

    public function calculatePrice()
    {
        $quantity = $this->quantity->getQuantity();

        return $quantity ? $this->sum->divide($quantity) : $this->sum;
    }

    /**
     * @return int|string
     */
    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        if ($this->id === $id) {
            return;
        }
        if ($this->id !== null) {
            throw new \Exception('cannot reassign bill id');
        }
        $this->id = $id;
    }

    /**
     * @return TypeInterface
     */
    public function getType(): TypeInterface
    {
        return $this->type;
    }

    /**
     * @return DateTimeImmutable
     */
    public function getTime(): DateTimeImmutable
    {
        return $this->time;
    }

    /**
     * @return TargetInterface
     */
    public function getTarget()
    {
        return $this->target;
    }

    /**
     * @return CustomerInterface
     */
    public function getCustomer()
    {
        return $this->customer;
    }

    /**
     * @return QuantityInterface
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * @return BillInterface
     */
    public function setQuantity(QuantityInterface $quantity): BillInterface
    {
        $this->quantity = $quantity;

        return $this;
    }

    /**
     * @return Money
     */
    public function getSum()
    {
        return $this->sum;
    }

    /**
     * @return PlanInterface
     */
    public function getPlan()
    {
        return $this->plan;
    }

    /**
     * @return bool
     */
    public function hasCharges(): bool
    {
        return $this->charges !== [];
    }

    /**
     * @return ChargeInterface[]
     */
    public function getCharges(): array
    {
        return $this->charges;
    }

    /**
     * @param ChargeInterface[] $prices
     * @return self
     * @throws \Exception
     */
    public function setCharges(array $charges): self
    {
        if ($this->hasCharges()) {
            throw new \Exception('cannot reassign charges for bill');
        }
        $this->charges = $charges;

        return $this;
    }

    public function getState(): ?BillState
    {
        return $this->state;
    }

    public function setFinished(): void
    {
        $this->state = BillState::finished();
    }

    public function isFinished(): ?bool
    {
        return $this->state === null ? null : $this->state->isFinished();
    }

    public function getComment()
    {
        return $this->comment;
    }

    public function setComment(string $comment)
    {
        $this->comment = $comment;
    }

    public function jsonSerialize()
    {
        return get_object_vars($this);
    }
}
