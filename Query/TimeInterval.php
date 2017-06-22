<?php

namespace Symball\ReportBundle\Service;

use Doctrine\Common\Persistence\ObjectRepository;
use Symball\ReportBundle\Interfaces\QueryInterface;
use Symball\ReportBundle\Service\Query;

class QueryTimeInterval extends Query implements QueryInterface
{
    protected $currentDate;
    protected $interval;
    protected $dateTimeField;

    /**
     * Define the time range to use for each query set
     *
     * @param \DateInterval $interval
     * @return $this
     */
    public function setIntervalDateTime(\DateInterval $interval)
    {
        $this->interval = $interval;

        return $this;
    }

    /**
     * Set the "current" time that the query will work from
     *
     * @param \DateTime $dateTime
     * @return $this
     */
    public function setHeadDateTime(\DateTime $dateTime)
    {
        $this->currentDate = $dateTime;

        return $this;
    }

    /**
     * Define the database field which will be used as the DateTime handler
     *
     * @param string $fieldName
     * @return $this
     */
    public function setDateTimeField($fieldName)
    {
        $this->dateTimeField = $fieldName;

        return $this;
    }

    /**
     * For this report type, add the custom logic to query
     *
     * @return type
     */
    public function prepareQuery()
    {

        $query = parent::prepareQuery();

        $earlyDate = clone $this->currentDate;
        $this->currentDate->add($this->interval);

        $query
        ->field($this->getDateTimeField())->gte($earlyDate)
        ->field($this->getDateTimeField())->lte($this->currentDate);

        return $query;
    }

    /**
     * Stringify the current data set
     *
     * @return string
     */
    public function getTitle()
    {
        $laterDate = clone $this->currentDate;
        $laterDate->add($this->interval);

        return $this->currentDate->format('y-m-d') . ' - ' . $laterDate->format('y-m-d');
    }

    /**
     * Return the field reference that is being used to define time
     *
     * @return string
     */
    public function getDatetimeField()
    {
        return $this->dateTimeField;
    }
}
