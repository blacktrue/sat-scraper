<?php

class ScraperTest extends \PHPUnit\Framework\TestCase
{
    public function  testFailedLoginByWrongCredentials()
    {
        $credentials = require 'credentials.php';

        $this->expectException(\Blacktrue\Scraping\Exceptions\SATCredentialsException::class);

        new \Blacktrue\Scraping\SATScraper([
            'rfc' => $credentials['rfc'],
            'ciec' => $credentials['ciec'].'2',
            'tipoDescarga' => 'recibidos',
            'cancelados' => false,
            'loginUrl' => $credentials['loginUrl']
        ]);

    }

    public function  testSuccessLoginByCredentials()
    {
        $credentials = require 'credentials.php';

        $scraper = new \Blacktrue\Scraping\SATScraper([
            'rfc' => $credentials['rfc'],
            'ciec' => $credentials['ciec'],
            'tipoDescarga' => 'recibidos',
            'cancelados' => false,
            'loginUrl' => $credentials['loginUrl']
        ]);

        $this->assertInstanceOf(\Blacktrue\Scraping\SATScraper::class, $scraper);

    }

    public function testDataStructure()
    {
        $credentials = require 'credentials.php';

        $scraper = new \Blacktrue\Scraping\SATScraper([
            'rfc' => $credentials['rfc'],
            'ciec' => $credentials['ciec'],
            'tipoDescarga' => 'recibidos',
            'cancelados' => false,
            'loginUrl' => $credentials['loginUrl']
        ]);

        $scraper->downloadPeriod(
            ...$credentials['dates']['fecha_inicial'],
            ...$credentials['dates']['fecha_final']
        );

        $data = $scraper->getData();

        $this->assertArrayHasKey('uuid', $data[key($data)]);
        $this->assertArrayHasKey('rfcEmisor', $data[key($data)]);
        $this->assertArrayHasKey('nombreEmisor', $data[key($data)]);
        $this->assertArrayHasKey('rfcReceptor', $data[key($data)]);
        $this->assertArrayHasKey('nombreReceptor', $data[key($data)]);
        $this->assertArrayHasKey('fechaEmision', $data[key($data)]);
        $this->assertArrayHasKey('fechaCertificacion', $data[key($data)]);
        $this->assertArrayHasKey('pacCertifico', $data[key($data)]);
        $this->assertArrayHasKey('total', $data[key($data)]);
        $this->assertArrayHasKey('efectoComprobante', $data[key($data)]);
        $this->assertArrayHasKey('estadoComprobante', $data[key($data)]);
        $this->assertArrayHasKey('fechaCancelacion', $data[key($data)]);
        $this->assertArrayHasKey('urlXml', $data[key($data)]);
    }

    public function testDownloadXml()
    {
        $credentials = require 'credentials.php';

        $scraper = new \Blacktrue\Scraping\SATScraper([
            'rfc' => $credentials['rfc'],
            'ciec' => $credentials['ciec'],
            'tipoDescarga' => 'recibidos',
            'cancelados' => false,
            'loginUrl' => $credentials['loginUrl']
        ]);

        $scraper->downloadPeriod(
            ...$credentials['dates']['fecha_inicial'],
            ...$credentials['dates']['fecha_final']
        );

        if(!file_exists(__DIR__.'/downloads')){
            mkdir(__DIR__.'/downloads', 0777);
        }

        $filename = '';

        (new \Blacktrue\Scraping\DownloadXML())
            ->setSatScraper($scraper)
            ->setConcurrency(50)
            ->download(function ($contentXml, $name) use (&$filename){
                $filename = __DIR__.'/downloads'.DIRECTORY_SEPARATOR.$name;

                $f = new SplFileObject($filename,'w');
                $f->fwrite($contentXml);
                $f = null;
            });

        $this->assertFileExists($filename);
    }
}