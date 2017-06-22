<?php

declare(strict_types=1);

namespace Blacktrue\Scraping;

use Closure;
use GuzzleHttp\Promise\EachPromise;
use Psr\Http\Message\ResponseInterface;

/**
 * Class DownloadXML.
 */
class DownloadXML
{
    /**
     * @var SATScraper
     */
    protected $satScraper;

    /**
     * @var int
     */
    protected $concurrency;

    /**
     * DownloadXML constructor.
     */
    public function __construct()
    {
        $this->concurrency = 10;
    }

    /**
     * @param Closure $callback
     */
    public function download(Closure $callback)
    {
        $promises = (function () {
            foreach ($this->satScraper->getUrls() as $link) {
                yield $this->satScraper->getClient()->requestAsync('GET', $link, [
                    'future' => true,
                    'verify' => false,
                    'cookies' => $this->satScraper->getCookie(),
                ]);
            }
        })();

        (new EachPromise($promises, [
            'concurrency' => $this->concurrency,
            'fulfilled' => function (ResponseInterface $response) use ($callback) {
                $callback($response->getBody(), $this->getFileName($response));
            },
        ]))->promise()
            ->wait();
    }

    /**
     * @param ResponseInterface $response
     *
     * @return string
     */
    protected function getFileName(ResponseInterface $response) : string
    {
        $contentDisposition = $response->getHeaderLine('content-disposition');
        $partsOfContentDisposition = explode(';', $contentDisposition);
        $fileName = str_replace('filename=', '', isset($partsOfContentDisposition[1]) ? $partsOfContentDisposition[1] : '');

        return !empty($fileName) ? $fileName : uniqid().'.xml';
    }

    /**
     * @param SATScraper $satScraper
     *
     * @return DownloadXML
     */
    public function setSatScraper(SATScraper $satScraper) : DownloadXML
    {
        $this->satScraper = $satScraper;

        return $this;
    }

    /**
     * @param int $concurrency
     *
     * @return DownloadXML
     */
    public function setConcurrency(int $concurrency) : DownloadXML
    {
        $this->concurrency = $concurrency;

        return $this;
    }
}
