<?php

namespace Blacktrue\Scraping\Contracts;

interface Filters
{
    /**
     * @return mixed
     */
    public function getPost();

    /**
     * @return mixed
     */
    public function getFormPostDates();

    /**
     * @param int $hour
     *
     * @return mixed
     */
    public function converterHoursToSeconds(int $hour);

    /**
     * @param int $pSecStart
     *
     * @return mixed
     */
    public function converterSecondsToHours(int $pSecStart);
}
